<?php

namespace App\Jobs;

use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEcommerceOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public string $platform)
    {
    }

    public function handle(): void
    {
        Log::info("开始同步 {$this->platform} 电商订单...");

        // 模拟同步（实际对接各平台API）
        // $orders = $this->fetchOrdersFromPlatform();
        // foreach ($orders as $orderData) { ... }

        EcommerceOrder::create([
            'platform' => $this->platform,
            'platform_order_no' => "SYNC-" . now()->timestamp,
            'buyer_name' => '同步测试',
            'total_amount' => 0,
            'status' => 'synced',
            'synced_at' => now(),
        ]);

        Log::info("{$this->platform} 电商订单同步完成");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("电商订单同步失败: {$exception->getMessage()}");
    }
}
