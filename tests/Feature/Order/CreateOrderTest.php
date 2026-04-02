<?php

namespace Tests\Feature\Order;

use App\Models\TPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Concerns\InteractsWithWechatPayKeys;

class CreateOrderTest extends TestCase
{
    use InteractsWithWechatPayKeys;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $merchantKeys = $this->createWechatKeyPair('merchant');
        $platformKeys = $this->createWechatKeyPair('platform');

        config()->set('services.wechat_pay', [
            'app_id' => 'wx-test-appid',
            'mch_id' => '1234567890',
            'serial_no' => 'test-serial-no',
            'private_key_path' => $merchantKeys['private_key_path'],
            'api_v3_key' => '12345678901234567890123456789012',
            'notify_url' => 'https://example.com/wechat/pay/notify',
            'public_key_id' => 'PUB_KEY_ID_1234567890',
            'public_key_path' => $platformKeys['public_key_path'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->cleanupWechatKeyFiles();

        parent::tearDown();
    }

    public function test_it_creates_a_monthly_order_and_returns_wechat_native_code_url(): void
    {
        $package = TPackage::query()->forceCreate([
            'name' => '专业版',
            'code' => 'pro',
            'price' => 99.90,
            'year_price' => 999.00,
            'features' => json_encode(['feature-a']),
            'sort' => 1,
            'status' => 1,
            'trial_days' => 7,
        ]);

        Http::fake([
            'https://api.mch.weixin.qq.com/v3/pay/transactions/native' => Http::response([
                'code_url' => 'weixin://wxpay/bizpayurl?pr=test-native-code',
            ], 200),
        ]);

        $response = $this->postJson('/order/create', [
            'package_id' => $package->id,
            'billing_cycle' => 'month',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.package_id', $package->id)
            ->assertJsonPath('data.package_name', '专业版')
            ->assertJsonPath('data.billing_cycle', 'month')
            ->assertJsonPath('data.pay_type', 'wechat')
            ->assertJsonPath('data.pay_status', 0)
            ->assertJsonPath('data.code_url', 'weixin://wxpay/bizpayurl?pr=test-native-code');

        $this->assertDatabaseHas('t_orders', [
            'package_id' => (string) $package->id,
            'package_name' => '专业版',
            'package_code' => 'pro',
            'pay_type' => 'wechat',
            'pay_status' => 0,
            'billing_cycle' => 'month',
            'duration' => 1,
        ]);
    }

    public function test_it_rejects_invalid_billing_cycle(): void
    {
        $package = TPackage::query()->forceCreate([
            'name' => '基础版',
            'code' => 'basic',
            'price' => 19.90,
            'year_price' => 199.00,
            'features' => json_encode(['feature-a']),
            'sort' => 1,
            'status' => 1,
            'trial_days' => 3,
        ]);

        $response = $this->postJson('/order/create', [
            'package_id' => $package->id,
            'billing_cycle' => 'week',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['billing_cycle']);
    }
}
