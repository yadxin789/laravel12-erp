<?php

namespace App\Console\Commands;

use App\Jobs\SyncEcommerceOrdersJob;
use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Console\Command;

/**
 * 电商订单同步
 *
 * 同步本身耗时且依赖外部平台接口，因此放到队列执行（SyncEcommerceOrdersJob），
 * 本命令只负责任务派发，可安全地从 cron / supervisor 每分钟调用。
 *
 * 用法：
 *   php artisan erp:ecommerce-sync            同步全部已启用平台
 *   php artisan erp:ecommerce-sync --platform=taobao
 */
class EcommerceSyncCommand extends Command
{
    protected $signature = 'erp:ecommerce-sync {--platform= : 只同步指定平台}';

    protected $description = '派发电商订单同步任务到队列';

    public function handle(): int
    {
        $platforms = $this->option('platform')
            ? [(string) $this->option('platform')]
            : array_keys(EcommerceOrder::PLATFORMS);

        $invalid = array_diff($platforms, array_keys(EcommerceOrder::PLATFORMS));
        if ($invalid !== []) {
            $this->error('未知平台：' . implode(', ', $invalid));

            return self::FAILURE;
        }

        foreach ($platforms as $platform) {
            // 同一平台的同步任务互斥，避免上一轮未跑完又堆积新任务
            SyncEcommerceOrdersJob::dispatch($platform)->onQueue('default');
            $this->line('已派发同步任务：' . (EcommerceOrder::PLATFORMS[$platform] ?? $platform));
        }

        return self::SUCCESS;
    }
}
