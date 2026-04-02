<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TOrder extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 't_orders';

    protected $fillable = [
        'order_no',
        'package_id',
        'package_name',
        'package_code',
        'billing_cycle',
        'amount',
        'pay_amount',
        'pay_type',
        'pay_status',
        'transaction_id',
        'start_time',
        'end_time',
        'duration',
        'remark',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'pay_amount' => 'decimal:2',
            'pay_status' => 'integer',
            'duration' => 'integer',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
