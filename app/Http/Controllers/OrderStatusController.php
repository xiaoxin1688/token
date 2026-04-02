<?php

namespace App\Http\Controllers;

use App\Models\TOrder;
use Illuminate\Http\JsonResponse;

class OrderStatusController extends Controller
{
    public function show(string $orderNo): JsonResponse
    {
        $order = TOrder::query()
            ->where('order_no', $orderNo)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'order_no' => $order->order_no,
                'pay_status' => $order->pay_status,
                'paid_at' => optional($order->paid_at)?->toDateTimeString(),
                'transaction_id' => $order->transaction_id,
            ],
        ]);
    }
}
