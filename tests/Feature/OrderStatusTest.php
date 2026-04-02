<?php

namespace Tests\Feature;

use App\Models\TOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_order_payment_status(): void
    {
        $order = TOrder::query()->create([
            'order_no' => '20260404123456ABCDEFGH',
            'package_id' => '1',
            'package_name' => '专业版',
            'package_code' => 'pro',
            'billing_cycle' => 'month',
            'amount' => 1999,
            'pay_amount' => 1999,
            'pay_type' => 'wechat',
            'pay_status' => 1,
            'duration' => 1,
        ]);

        $response = $this->getJson("/orders/{$order->order_no}/status");

        $response->assertOk()
            ->assertJsonPath('data.order_no', $order->order_no)
            ->assertJsonPath('data.pay_status', 1);
    }
}
