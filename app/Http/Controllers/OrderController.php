<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use App\Services\WechatPayService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function store(
        CreateOrderRequest $request,
        OrderService $orderService,
        WechatPayService $wechatPayService
    ): JsonResponse {
        $order = $orderService->createPendingOrder(
            (int) $request->integer('package_id'),
            (string) $request->string('billing_cycle')
        );

        $wechatOrder = $wechatPayService->createNativeOrder($order);

        return response()->json([
            'data' => [
                'order_no' => $order->order_no,
                'package_id' => (int) $order->package_id,
                'package_name' => $order->package_name,
                'package_code' => $order->package_code,
                'billing_cycle' => $order->billing_cycle,
                'amount' => $order->amount,
                'pay_amount' => $order->pay_amount,
                'pay_type' => $order->pay_type,
                'pay_status' => $order->pay_status,
                'code_url' => $wechatOrder['code_url'],
            ],
        ], 201);
    }
}
