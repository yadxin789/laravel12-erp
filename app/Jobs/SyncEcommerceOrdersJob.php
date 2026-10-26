<?php

namespace App\Jobs;

use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 电商订单同步
 *
 * - 幂等：按 (platform, platform_order_no) updateOrCreate，重复同步不会产生重复单据
 * - 互斥：同一平台 1 小时内只允许一个同步任务在队列中，避免上一轮未跑完又堆积
 * - 无真实接口时不写任何数据（此前会写入写死的演示订单）
 */
class SyncEcommerceOrdersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    /** @var array<int, int> 重试退避秒数 */
    public array $backoff = [30, 120, 300];

    /** 同一平台任务的互斥窗口（秒） */
    public int $uniqueFor = 3600;

    public function __construct(public string $platform)
    {
    }

    public function uniqueId(): string
    {
        return $this->platform;
    }

    public function handle(): int
    {
        Log::info("开始同步 {$this->platform} 电商订单...");

        $orders = $this->fetchOrdersFromPlatform();

        if ($orders === []) {
            Log::warning("平台 {$this->platform} 未配置开放接口或本次无新订单，跳过同步");

            return 0;
        }

        $synced = 0;
        foreach ($orders as $order) {
            $orderNo = (string) ($order['order_no'] ?? $order['platform_order_no'] ?? '');
            if ($orderNo === '') {
                continue;
            }

            EcommerceOrder::updateOrCreate(
                [
                    'platform' => $this->platform,
                    'platform_order_no' => $orderNo,
                ],
                [
                    'buyer_name' => $order['buyer_name'] ?? null,
                    'buyer_phone' => $order['buyer_phone'] ?? null,
                    'shipping_address' => $order['shipping_address'] ?? null,
                    'total_amount' => (float) ($order['total_amount'] ?? 0),
                    'status' => EcommerceOrder::STATUS[$order['status'] ?? ''] ?? 'synced',
                    'items' => $order['items'] ?? null,
                    'synced_at' => now(),
                ]
            );
            $synced++;
        }

        Log::info("{$this->platform} 电商订单同步完成，处理 {$synced} 条");

        return $synced;
    }

    public function failed(?Throwable $exception = null): void
    {
        Log::error("电商订单同步失败：{$this->platform} - " . ($exception?->getMessage() ?: '未知错误'));
    }

    /**
     * 拉取平台订单
     *
     * 未配置 endpoint 时返回空数组（不写任何模拟数据）。
     * 各平台适配只需在 config/services.php 的 ecommerce.platforms 中补齐地址与密钥。
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchOrdersFromPlatform(): array
    {
        $config = config("services.ecommerce.platforms.{$this->platform}", []);

        if (empty($config['endpoint'])) {
            return [];
        }

        $response = Http::timeout(30)
            ->acceptJson()
            ->get($config['endpoint'], array_filter([
                'app_key' => $config['app_key'] ?? null,
                'page_size' => 100,
            ]));

        if (! $response->successful()) {
            throw new \RuntimeException(
                "平台接口返回 {$response->status()}：" . mb_substr($response->body(), 0, 500)
            );
        }

        $payload = $response->json();

        return $payload['data']['orders'] ?? $payload['orders'] ?? $payload['data'] ?? [];
    }
}
