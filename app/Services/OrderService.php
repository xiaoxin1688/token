<?php

namespace App\Services;

use App\Models\TOrder;
use App\Models\TPackage;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OrderService
{
    public function createPendingOrder(int $packageId, string $billingCycle): TOrder
    {
        $package = TPackage::query()
            ->whereKey($packageId)
            ->where('status', 1)
            ->first();

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => '套餐不存在或已禁用',
            ]);
        }

        [$price, $duration] = $this->resolvePriceAndDuration($package, $billingCycle);

        return TOrder::query()->create([
            'order_no' => $this->generateOrderNo(),
            'package_id' => (string) $package->getKey(),
            'package_name' => (string) $package->name,
            'package_code' => (string) $package->code,
            'billing_cycle' => $billingCycle,
            'amount' => $price,
            'pay_amount' => $price,
            'pay_type' => 'wechat',
            'pay_status' => 0,
            'duration' => $duration,
        ]);
    }

    public function markAsPaid(array $transaction): TOrder
    {
        $orderNo = (string) ($transaction['out_trade_no'] ?? '');

        $order = TOrder::query()->where('order_no', $orderNo)->firstOrFail();

        if ((int) $order->pay_status === 1) {
            return $order;
        }

        $paidAmount = $this->fromFen((int) data_get($transaction, 'amount.total', 0));
        $paidAt = data_get($transaction, 'success_time');

        $order->forceFill([
            'pay_status' => 1,
            'pay_type' => 'wechat',
            'transaction_id' => (string) ($transaction['transaction_id'] ?? ''),
            'pay_amount' => $paidAmount,
            'paid_at' => $paidAt ? CarbonImmutable::parse($paidAt) : now(),
        ])->save();

        return $order->refresh();
    }

    public function resolvePriceAndDuration(TPackage $package, string $billingCycle): array
    {
        if ($billingCycle === 'year') {
            return [(float) $package->year_price, 12];
        }

        return [(float) $package->price, 1];
    }

    protected function generateOrderNo(): string
    {
        return now()->format('YmdHis').Str::upper(Str::random(10));
    }

    protected function fromFen(int $fen): string
    {
        return number_format($fen / 100, 2, '.', '');
    }
}
