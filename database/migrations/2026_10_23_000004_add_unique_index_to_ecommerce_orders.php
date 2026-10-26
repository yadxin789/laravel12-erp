<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 电商订单幂等约束
     *
     * SyncEcommerceOrdersJob 用 updateOrCreate 保证幂等，但并发派发时仍可能产生重复单据，
     * 因此在数据库层补唯一索引（应用层幂等 + 数据库兜底）。
     */
    public function up(): void
    {
        // 历史重复数据先清理，避免迁移失败（保留 id 最小的一条）
        $duplicates = DB::table('ecommerce_orders')
            ->select('platform', 'platform_order_no')
            ->groupBy('platform', 'platform_order_no')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keep = DB::table('ecommerce_orders')
                ->where('platform', $duplicate->platform)
                ->where('platform_order_no', $duplicate->platform_order_no)
                ->orderBy('id')
                ->value('id');

            DB::table('ecommerce_orders')
                ->where('platform', $duplicate->platform)
                ->where('platform_order_no', $duplicate->platform_order_no)
                ->where('id', '!=', $keep)
                ->delete();
        }

        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->unique(['platform', 'platform_order_no'], 'ecommerce_orders_platform_order_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropUnique('ecommerce_orders_platform_order_unique');
        });
    }
};
