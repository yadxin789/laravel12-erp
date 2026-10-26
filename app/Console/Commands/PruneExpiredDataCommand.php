<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 清理过期运行数据
 *
 * 主流 ERP 运维惯例：会话 / 通知 / 失败任务 / 审计日志这类只增不减的表必须定期轮转，
 * 否则半年后单表膨胀会拖慢整库。清理天数在 config/monitoring.php 的 prune 段集中配置。
 *
 * 用法：php artisan erp:prune [--dry-run]
 */
class PruneExpiredDataCommand extends Command
{
    protected $signature = 'erp:prune {--dry-run : 只统计将要删除的行数，不实际删除}';

    protected $description = '清理过期会话、通知、失败任务、批次与审计日志';

    /** @var array<int, array{目标,删除行数,保留}> */
    protected array $result = [];

    public function handle(): int
    {
        $days = config('monitoring.prune');

        $this->prune('sessions', function () use ($days) {
            $cutoff = now()->subDays($days['sessions'])->getTimestamp();

            return DB::table('sessions')->where('last_activity', '<', $cutoff);
        }, $days['sessions'] . ' 天未活动');

        $this->prune('password_reset_tokens', function () use ($days) {
            return DB::table('password_reset_tokens')
                ->where('created_at', '<', now()->subDays($days['password_resets']));
        }, $days['password_resets'] . ' 天前');

        $this->prune('notifications', function () use ($days) {
            return DB::table('notifications')
                ->where('created_at', '<', now()->subDays($days['notifications']));
        }, $days['notifications'] . ' 天前');

        $this->prune('failed_jobs', function () use ($days) {
            return DB::table(config('queue.failed.table', 'failed_jobs'))
                ->where('failed_at', '<', now()->subDays($days['failed_jobs']));
        }, $days['failed_jobs'] . ' 天前');

        $this->prune('job_batches', function () use ($days) {
            return DB::table(config('queue.batching.table', 'job_batches'))
                ->where('created_at', '<', now()->subDays($days['job_batches'])->getTimestamp());
        }, $days['job_batches'] . ' 天前');

        $this->prune('audit_logs', function () use ($days) {
            return DB::table('audit_logs')
                ->where('created_at', '<', now()->subDays($days['audit_logs']));
        }, $days['audit_logs'] . ' 天前');

        // database 缓存驱动的过期键不会自动回收，一并清理
        $this->prune('cache', function () {
            return DB::table(config('cache.stores.database.table', 'cache'))
                ->where('expiration', '<', now()->getTimestamp());
        }, '已过期');

        if ($this->option('dry-run')) {
            $this->warn('dry-run 模式，未执行任何删除');
        }

        $this->table(['目标', '删除行数', '保留策略'], $this->result);

        return self::SUCCESS;
    }

    protected function prune(string $label, callable $query, string $retention): void
    {
        try {
            $count = $query()->count();

            if (! $this->option('dry-run') && $count > 0) {
                // 分批删除，避免长事务锁表
                $query()->limit(10000)->delete();
            }

            $this->result[] = [$label, $count, $retention];
        } catch (\Throwable $e) {
            $this->result[] = [$label, '跳过（' . $e->getMessage() . '）', $retention];
        }
    }
}
