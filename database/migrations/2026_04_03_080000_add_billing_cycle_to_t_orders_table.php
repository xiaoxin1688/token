<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_orders', function (Blueprint $table) {
            $table->string('billing_cycle', 20)->default('month')->after('package_code')->comment('购买周期 month/year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_orders', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
    }
};
