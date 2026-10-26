<?php

namespace App\Console\Commands;

use App\Models\Logistics\LogisticsShipment;
use App\Services\LogisticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 物流运单轨迹轮询
 *
 * 签收/异常/退回状态变化由 LogisticsService 自动发出站内通知，
 * 因此这里只需把「在途且长时间未刷新」的运单捞出来重新查询即可。
 *
 * 用法：php artisan erp:logistics-track [--limit=50] [--interval=6]
 */
class LogisticsTrackCommand extends Command
{
    protected $signature = 'erp:logistics-track
                            {--limit=50 : 单次最多处理的运单数}
                            {--interval=6 : 距上次查询多少小时以上才重新查询}';

    protected $description = '轮询在途物流运单轨迹，状态变化时自动通知';

    public function handle(LogisticsService $logistics): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $interval = max(1, (int) $this->option('interval'));

        $shipments = LogisticsShipment::query()
            ->where('notify_enabled', true)
            ->whereNotIn('status', [
                LogisticsShipment::STATUS_SIGNED,
                LogisticsShipment::STATUS_CLOSED,
            ])
            ->where(function ($query) use ($interval) {
                $query->whereNull('last_traced_at')
                    ->orWhere('last_traced_at', '<', now()->subHours($interval));
            })
            ->orderBy('last_traced_at')
            ->limit($limit)
            ->get();

        if ($shipments->isEmpty()) {
            $this->info('没有需要刷新轨迹的运单');

            return self::SUCCESS;
        }

        $updated = 0;
        $failed = 0;

        foreach ($shipments as $shipment) {
            try {
                $result = $logistics->query(
                    (string) $shipment->carrier_key,
                    (string) $shipment->tracking_no
                );

                if (! empty($result['success'])) {
                    $updated++;
                    $this->line(sprintf(
                        '%s → %s（来源：%s%s）',
                        $shipment->tracking_no,
                        $result['status'],
                        $result['source'],
                        ! empty($result['is_demo']) ? '，演示数据' : ''
                    ));
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('物流轨迹刷新失败', [
                    'tracking_no' => $shipment->tracking_no,
                    'error' => $e->getMessage(),
                ]);
                $this->warn("{$shipment->tracking_no} 查询失败：{$e->getMessage()}");
            }
        }

        $this->info("轨迹刷新完成：成功 {$updated} 单，失败 {$failed} 单");

        return self::SUCCESS;
    }
}
