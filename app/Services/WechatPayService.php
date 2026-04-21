<?php

namespace App\Services;

use App\Models\TOrder;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;

class WechatPayService
{
    public function __construct(
        protected HttpFactory $http
    ) {
    }

    public function createNativeOrder(TOrder $order): array
    {
        $path = '/v3/pay/transactions/native';
        $payload = [
            'appid' => $this->config('app_id')??'',
            'mchid' => $this->config('mch_id'),
            'description' => $this->buildDescription($order),
            'out_trade_no' => $order->order_no,
            'notify_url' => $this->config('notify_url'),
            'amount' => [
                'total' => $this->toFen((string) $order->pay_amount),
                'currency' => 'CNY',
            ],
//            'scene_info' => [ // <= 这里是 H5 特有字段
//                'payer_client_ip' => $_SERVER['REMOTE_ADDR'],
//                'h5_info' => [
//                    'type' => 'Wap',
//                    'wap_url' => 'https://aitoken-ai.com.cn/', // 你的 PC 网站域名
//                    'wap_name' => '网站名称',
//                ],
//            ],
        ];

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $response = $this->request('POST', $path, $body);

        if (! $response->successful()) {
            throw new RuntimeException('微信支付下单失败：'.$response->body());
        }

        $data = $response->json();

        if (! is_array($data) || empty($data['code_url'])) {
            throw new RuntimeException('微信支付返回缺少 code_url');
        }

        return $data;
    }

    public function parseNotification(string $body, array $headers): array
    {
        if (! $this->verifySignature($body, $headers)) {
            throw new RuntimeException('微信支付回调验签失败');
        }

        $payload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        $resource = Arr::get($payload, 'resource');

        if (! is_array($resource)) {
            throw new RuntimeException('微信支付回调缺少 resource');
        }

        return $this->decryptResource($resource);
    }

    public function verifySignature(string $body, array $headers): bool
    {
        $timestamp = (string) ($headers['timestamp'] ?? '');
        $nonce = (string) ($headers['nonce'] ?? '');
        $signature = (string) ($headers['signature'] ?? '');
        $serial = (string) ($headers['serial'] ?? '');

        if ($timestamp === '' || $nonce === '' || $signature === '' || $serial === '') {
            return false;
        }

        $configuredSerial = (string) $this->config('public_key_id', '');

        if ($configuredSerial !== '' && $configuredSerial !== $serial) {
            return false;
        }

        $message = sprintf("%s\n%s\n%s\n", $timestamp, $nonce, $body);
        $publicKey = openssl_pkey_get_public($this->readKey($this->config('public_key_path')));

        if ($publicKey === false) {
            throw new RuntimeException('微信支付平台公钥读取失败');
        }

        $verified = openssl_verify($message, base64_decode($signature, true), $publicKey, OPENSSL_ALGO_SHA256);

        if (is_resource($publicKey) || $publicKey instanceof \OpenSSLAsymmetricKey) {
            openssl_free_key($publicKey);
        }

        return $verified === 1;
    }

    public function decryptResource(array $resource): array
    {
        $ciphertext = base64_decode((string) Arr::get($resource, 'ciphertext', ''), true);

        if ($ciphertext === false || strlen($ciphertext) < 17) {
            throw new RuntimeException('微信支付回调密文格式错误');
        }

        $nonce = (string) Arr::get($resource, 'nonce', '');
        $associatedData = (string) Arr::get($resource, 'associated_data', '');
        $tag = substr($ciphertext, -16);
        $encrypted = substr($ciphertext, 0, -16);

        $plainText = openssl_decrypt(
            $encrypted,
            'aes-256-gcm',
            $this->config('api_v3_key'),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $associatedData
        );

        if ($plainText === false) {
            throw new RuntimeException('微信支付回调解密失败');
        }

        return json_decode($plainText, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function request(string $method, string $path, string $body): Response
    {
        return $this->http
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->buildAuthorization($method, $path, $body),
                'User-Agent' => 'TokenApp/1.0',
            ])
            ->retry(
                (int) $this->config('retry_times', 2),
                (int) $this->config('retry_sleep_ms', 250)
            )
            ->connectTimeout((int) $this->config('connect_timeout', 3))
            ->timeout((int) $this->config('timeout', 15))
            ->withOptions([
                'curl' => [
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                ],
            ])
            ->send($method, 'https://api.mch.weixin.qq.com'.$path, [
                'body' => $body,
            ]);
    }

    protected function buildAuthorization(string $method, string $path, string $body): string
    {
        $timestamp = (string) time();
        $nonce = Str::random(32);
        $message = sprintf("%s\n%s\n%s\n%s\n%s\n", strtoupper($method), $path, $timestamp, $nonce, $body);
        $privateKey = openssl_pkey_get_private($this->readKey($this->config('private_key_path')));

        if ($privateKey === false) {
            throw new RuntimeException('商户私钥读取失败');
        }

        $signed = openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (is_resource($privateKey) || $privateKey instanceof \OpenSSLAsymmetricKey) {
            openssl_free_key($privateKey);
        }

        if (! $signed) {
            throw new RuntimeException('微信支付签名生成失败');
        }

        return sprintf(
            'WECHATPAY2-SHA256-RSA2048 mchid="%s",nonce_str="%s",timestamp="%s",serial_no="%s",signature="%s"',
            $this->config('mch_id'),
            $nonce,
            $timestamp,
            $this->config('serial_no'),
            base64_encode($signature)
        );
    }

    protected function buildDescription(TOrder $order): string
    {
        $cycleLabel = $order->billing_cycle === 'year' ? '年付' : '月付';

        return sprintf('%s-%s', $order->package_name, $cycleLabel);
    }

    protected function toFen(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        $value = config("services.wechat_pay.{$key}", $default);

        if ($value === null || $value === '') {
            throw new RuntimeException("微信支付配置缺失：{$key}");
        }

        return $value;
    }

    protected function readKey(string $path): string
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("密钥文件不可读：{$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("密钥文件读取失败：{$path}");
        }

        return $content;
    }
}
