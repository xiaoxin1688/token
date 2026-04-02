<?php

namespace Tests\Unit;

use App\Models\TOrder;
use App\Services\WechatPayService;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithWechatPayKeys;
use Tests\TestCase;

class WechatPayServiceTest extends TestCase
{
    use InteractsWithWechatPayKeys;

    protected array $merchantKeys;

    protected array $platformKeys;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchantKeys = $this->createWechatKeyPair('merchant');
        $this->platformKeys = $this->createWechatKeyPair('platform');

        config()->set('services.wechat_pay', [
            'app_id' => 'wx-test-appid',
            'mch_id' => '1234567890',
            'serial_no' => 'test-serial-no',
            'private_key_path' => $this->merchantKeys['private_key_path'],
            'api_v3_key' => '12345678901234567890123456789012',
            'notify_url' => 'https://example.com/wechat/pay/notify',
            'public_key_id' => 'PUB_KEY_ID_1234567890',
            'public_key_path' => $this->platformKeys['public_key_path'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanupWechatKeyFiles();

        parent::tearDown();
    }

    public function test_it_sends_a_native_order_request_with_expected_payload(): void
    {
        Http::fake([
            'https://api.mch.weixin.qq.com/v3/pay/transactions/native' => Http::response([
                'code_url' => 'weixin://wxpay/bizpayurl?pr=service-test',
            ], 200),
        ]);

        $service = app(WechatPayService::class);
        $order = new TOrder([
            'order_no' => '20260403130000ABCDEFGH',
            'package_name' => '专业版',
            'billing_cycle' => 'year',
            'pay_amount' => '999.00',
        ]);

        $result = $service->createNativeOrder($order);

        $this->assertSame('weixin://wxpay/bizpayurl?pr=service-test', $result['code_url']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('POST', $request->method());
            $this->assertSame('https://api.mch.weixin.qq.com/v3/pay/transactions/native', (string) $request->url());
            $this->assertSame('wx-test-appid', $payload['appid']);
            $this->assertSame('1234567890', $payload['mchid']);
            $this->assertSame('20260403130000ABCDEFGH', $payload['out_trade_no']);
            $this->assertSame(99900, $payload['amount']['total']);
            $this->assertSame('https://example.com/wechat/pay/notify', $payload['notify_url']);
            $this->assertStringStartsWith('WECHATPAY2-SHA256-RSA2048', $request->header('Authorization')[0]);

            return true;
        });
    }

    public function test_it_verifies_and_decrypts_notification_payload(): void
    {
        $service = app(WechatPayService::class);

        $plainText = json_encode([
            'out_trade_no' => '20260403130000ABCDEFGH',
            'transaction_id' => '4200000000003',
            'trade_state' => 'SUCCESS',
            'success_time' => '2026-04-03T13:00:00+08:00',
            'amount' => [
                'total' => 99900,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $resourceNonce = 'service-notify';
        $associatedData = 'transaction';
        $encrypted = openssl_encrypt(
            $plainText,
            'aes-256-gcm',
            (string) config('services.wechat_pay.api_v3_key'),
            OPENSSL_RAW_DATA,
            $resourceNonce,
            $tag,
            $associatedData
        );

        if ($encrypted === false) {
            self::fail('无法生成微信支付回调服务测试密文');
        }

        $body = json_encode([
            'resource' => [
                'algorithm' => 'AEAD_AES_256_GCM',
                'ciphertext' => base64_encode($encrypted.$tag),
                'associated_data' => $associatedData,
                'nonce' => $resourceNonce,
                'original_type' => 'transaction',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $nonce = 'service-nonce';
        $message = sprintf("%s\n%s\n%s\n", $timestamp, $nonce, $body);
        $privateKey = openssl_pkey_get_private($this->platformKeys['private_key']);

        if ($privateKey === false) {
            self::fail('无法加载微信支付平台测试私钥');
        }

        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $transaction = $service->parseNotification($body, [
            'serial' => 'PUB_KEY_ID_1234567890',
            'signature' => base64_encode($signature),
            'timestamp' => $timestamp,
            'nonce' => $nonce,
        ]);

        $this->assertSame('20260403130000ABCDEFGH', $transaction['out_trade_no']);
        $this->assertSame('4200000000003', $transaction['transaction_id']);
        $this->assertSame('SUCCESS', $transaction['trade_state']);
        $this->assertSame(99900, $transaction['amount']['total']);
    }
}
