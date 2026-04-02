<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\WechatPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class WechatPayController extends Controller
{
    public function notify(
        Request $request,
        WechatPayService $wechatPayService,
        OrderService $orderService
    ): JsonResponse {
        try {
            $transaction = $wechatPayService->parseNotification($request->getContent(), [
                'serial' => $request->header('Wechatpay-Serial'),
                'signature' => $request->header('Wechatpay-Signature'),
                'timestamp' => $request->header('Wechatpay-Timestamp'),
                'nonce' => $request->header('Wechatpay-Nonce'),
            ]);

            if (($transaction['trade_state'] ?? null) === 'SUCCESS') {
                $orderService->markAsPaid($transaction);
            }

            return response()->json([
                'code' => 'SUCCESS',
                'message' => '成功',
            ]);
        } catch (Throwable $exception) {
            Log::error('wechat pay notify failed', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'code' => 'FAIL',
                'message' => '失败',
            ], 500);
        }
    }
}
