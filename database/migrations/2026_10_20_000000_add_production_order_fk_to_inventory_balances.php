<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * inventory_balances 迁移早于 production_orders，MySQL 下无法直接建立外键，
     * 因此在 production_orders 创建完成后再补充该约束。
     */
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->foreign('production_order_id')
                ->references('id')
                ->on('production_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropForeign(['production_order_id']);
        });
    }
};
