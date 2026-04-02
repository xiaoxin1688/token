<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->default('')->comment('订单 号');
            $table->string('package_id')->default('')->comment('套餐ID');
            $table->string('package_name')->nullable()->comment('套餐名称');
            $table->string('package_code')->nullable()->comment('套餐标识');
            $table->decimal('amount')->comment('原价');
            $table->decimal('pay_amount')->comment('实际支付金额');
            $table->string('pay_type')->default('')->comment('支付方式（wechat/alipay/stripe）');
            $table->tinyInteger('pay_status')->comment('支付状态 0未支付 1已支付 2已取消 3已退款');
            $table->string('transaction_id')->nullable()->comment('第三方支付流水号');
            $table->dateTime('start_time')->nullable()->comment('套餐开始时间');
            $table->dateTime('end_time')->nullable()->comment('套餐结束时间');
            $table->integer('duration')->nullable()->comment('购买时长');
            $table->string('remark')->nullable()->comment('备注');
            $table->dateTime('paid_at')->nullable()->comment('支付时间');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_orders');
    }
}
