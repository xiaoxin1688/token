<?php

namespace Tests\Feature;

use App\Models\TOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithWechatPayKeys;
use Tests\TestCase;

class WechatPayNotifyTest extends TestCase
{
    use InteractsWithWechatPayKeys;
    use RefreshDatabase;

    protected array $platformKeys;

    protected function setUp(): void
    {
        parent::setUp();

        $merchantKeys = $this->createWechatKeyPair('merchant');
        $this->platformKeys = $this->createWechatKeyPair('platform');

        config()->set('services.wechat_pay', [
            'app_id' => 'wx-test-appid',
            'mch_id' => '1234567890',
            'serial_no' => 'test-serial-no',
            'private_key_path' => $merchantKeys['private_key_path'],
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

    public function test_it_marks_the_order_as_paid_when_the_wechat_notification_is_valid(): void
    {
        $order = TOrder::query()->create([
            'order_no' => '20260403120000ABCDEFGH',
            'package_id' => '1',
            'package_name' => '专业版',
            'package_code' => 'pro',
            'billing_cycle' => 'month',
            'amount' => 99.90,
            'pay_amount' => 99.90,
            'pay_type' => 'wechat',
            'pay_status' => 0,
            'duration' => 1,
        ]);

        [$body, $headers] = $this->buildWechatNotification($order->order_no, '4200000000001', 9990);

        $response = $this->call('POST', '/wechat/pay/notify', [], [], [], $headers, $body);

        $response->assertOk()
            ->assertJson([
                'code' => 'SUCCESS',
                'message' => '成功',
            ]);

        $this->assertDatabaseHas('t_orders', [
            'id' => $order->id,
            'pay_status' => 1,
            'transaction_id' => '4200000000001',
        ]);
    }

    public function test_it_rejects_notification_with_invalid_signature(): void
    {
        $order = TOrder::query()->create([
            'order_no' => '20260403120000ZZZZZZZZ',
            'package_id' => '1',
            'package_name' => '专业版',
            'package_code' => 'pro',
            'billing_cycle' => 'month',
            'amount' => 99.90,
            'pay_amount' => 99.90,
            'pay_type' => 'wechat',
            'pay_status' => 0,
            'duration' => 1,
        ]);

        [$body, $headers] = $this->buildWechatNotification($order->order_no, '4200000000002', 9990);
        $headers['HTTP_WECHATPAY_SIGNATURE'] = 'invalid-signature';

        $response = $this->call('POST', '/wechat/pay/notify', [], [], [], $headers, $body);

        $response->assertStatus(500)
            ->assertJson([
                'code' => 'FAIL',
            ]);

        $this->assertDatabaseHas('t_orders', [
            'id' => $order->id,
            'pay_status' => 0,
        ]);
    }

    public function test_it_handles_duplicate_success_notifications_idempotently(): void
    {
        $order = TOrder::query()->create([
            'order_no' => '20260403120000YYYYYYYY',
            'package_id' => '1',
            'package_name' => '专业版',
            'package_code' => 'pro',
            'billing_cycle' => 'month',
            'amount' => 99.90,
            'pay_amount' => 99.90,
            'pay_type' => 'wechat',
            'pay_status' => 0,
            'duration' => 1,
        ]);

        [$firstBody, $firstHeaders] = $this->buildWechatNotification($order->order_no, '4200000000010', 9990);
        $firstResponse = $this->call('POST', '/wechat/pay/notify', [], [], [], $firstHeaders, $firstBody);
        $firstResponse->assertOk();

        [$secondBody, $secondHeaders] = $this->buildWechatNotification($order->order_no, '4200000000011', 9990);
        $secondResponse = $this->call('POST', '/wechat/pay/notify', [], [], [], $secondHeaders, $secondBody);
        $secondResponse->assertOk();

        $this->assertDatabaseHas('t_orders', [
            'id' => $order->id,
            'pay_status' => 1,
            'transaction_id' => '4200000000010',
        ]);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    protected function buildWechatNotification(string $orderNo, string $transactionId, int $totalFen): array
    {
        $resourceNonce = 'resource-nonce';
        $associatedData = 'transaction';
        $plainText = json_encode([
            'out_trade_no' => $orderNo,
            'transaction_id' => $transactionId,
            'trade_state' => 'SUCCESS',
            'success_time' => '2026-04-03T12:34:56+08:00',
            'amount' => [
                'total' => $totalFen,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

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
            self::fail('无法生成微信支付回调测试密文');
        }

        $body = json_encode([
            'id' => 'notify-id',
            'create_time' => '2026-04-03T12:34:56+08:00',
            'resource_type' => 'encrypt-resource',
            'event_type' => 'TRANSACTION.SUCCESS',
            'summary' => '支付成功',
            'resource' => [
                'algorithm' => 'AEAD_AES_256_GCM',
                'ciphertext' => base64_encode($encrypted.$tag),
                'associated_data' => $associatedData,
                'nonce' => $resourceNonce,
                'original_type' => 'transaction',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $headerNonce = 'notify-nonce';
        $timestamp = (string) time();
        $message = sprintf("%s\n%s\n%s\n", $timestamp, $headerNonce, $body);
        $privateKey = openssl_pkey_get_private($this->platformKeys['private_key']);

        if ($privateKey === false) {
            self::fail('无法加载微信支付平台测试私钥');
        }

        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return [
            $body,
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_WECHATPAY_SERIAL' => 'PUB_KEY_ID_1234567890',
                'HTTP_WECHATPAY_SIGNATURE' => base64_encode($signature),
                'HTTP_WECHATPAY_TIMESTAMP' => $timestamp,
                'HTTP_WECHATPAY_NONCE' => $headerNonce,
            ],
        ];
    }
}
