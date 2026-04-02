<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->default('')->comment('套餐名称（基础版/专业版/企业版）');
            $table->string('code')->default('')->comment('套餐标识（basic/pro/enterprise）');
            $table->decimal('price')->comment('月价格');
            $table->decimal('year_price')->comment('年价格');
            $table->json('features')->comment('功能列表');
            $table->integer('sort')->comment('排序');
            $table->tinyInteger('status')->comment('状态（1启用 0禁用）');
            $table->integer('trial_days')->comment('试用天数');
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
        Schema::dropIfExists('t_packages');
    }
}
