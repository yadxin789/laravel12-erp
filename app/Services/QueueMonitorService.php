<?php

namespace App\Services;

use App\Support\RuntimeInspector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 队列（Queue）运行状况
 *
 * 队列监控的关键不在「有多少任务」，而在「它们是不是在被消费」：
 *   - pending（待处理）持续上涨 → worker 跟不上；
 *   - reserved（处理中）长时间不变 → 任务卡死 / 执行超时；
 *   - 最旧一条任务的等待时长（lag）→ worker 是否已死的决定性指标；
 *   - failed_jobs → 业务侧真的出问题了。
 *
 * 冗余项：只有 database 队列驱动才能透视 jobs 表，其它驱动（redis / sync）时
 * 相关指标降级为 null，页面显示「驱动不支持统计」而不是假装 0。
 */
class QueueMonitorService
{
    /** 表名 */
    protected function jobsTable(): string
    {
        return config('queue.connections.database.table', 'jobs');
    }

    protected function failedTable(): string
    {
        return config('queue.failed.table', 'failed_jobs');
    }

    protected function batchesTable(): string
    {
        return config('queue.batching.table', 'job_batches');
    }

    public function driver(): string
    {
        return (string) config('queue.default', 'sync');
    }

    /** 是否支持基于数据库的透视（-sync / redis 驱动不支持） */
    public function inspectable(): bool
    {
        return $this->driver() === 'database';
    }

    // ------------------------------------------------------------------
    // 指标
    // ------------------------------------------------------------------

    /**
     * 全队概览
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $failedCount = $this->safeCount($this->failedTable());

        if (! $this->inspectable()) {
            return [
                'driver' => $this->driver(),
                'inspectable' => false,
                'pending' => null,
                'processing' => null,
                'delayed' => null,
                'failed' => $failedCount,
                'lag_minutes' => null,
                'oldest_at' => null,
                'throughput_1h' => null,
            ];
        }

        $table = $this->jobsTable();

        $oldest = $this->safe(function () use ($table) {
            return DB::table($table)->min('available_at');
        });

        return [
            'driver' => $this->driver(),
            'inspectable' => true,
            'pending' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->count();
            }),
            'processing' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->whereNotNull('reserved_at')->count();
            }),
            'delayed' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->where('available_at', '>', time())->count();
            }),
            'failed' => $failedCount,
            'lag_minutes' => $oldest ? (int) floor(max(0, time() - $oldest) / 60) : 0,
            'oldest_at' => $oldest ? now()->subSeconds(max(0, time() - $oldest)) : null,
            // 近 1 小时入队量：判断是「偶发积压」还是「持续洪峰」
            'throughput_1h' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->where('created_at', '>=', now()->subHour()->timestamp)->count();
            }),
        ];
    }

    /**
     * 按队列维度拆分
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function byQueue(): Collection
    {
        if (! $this->inspectable()) {
            return collect();
        }

        $table = $this->jobsTable();

        return $this->safe(function () use ($table) {
            return DB::table($table)
                ->selectRaw('queue, COUNT(*) AS total, SUM(CASE WHEN reserved_at IS NOT NULL THEN 1 ELSE 0 END) AS processing, SUM(CASE WHEN available_at > ? THEN 1 ELSE 0 END) AS delayed, MIN(available_at) AS oldest_at, MAX(attempts) AS max_attempts', [time()])
                ->groupBy('queue')
                ->orderByDesc('total')
                ->get();
        }, collect());
    }

    /**
     * 近 12 小时入队趋势（每小时一档）
     *
     * @return array<int, array{hour:string, count:int}>
     */
    public function enqueueTrend(int $hours = 12): array
    {
        if (! $this->inspectable()) {
            return [];
        }

        $rows = $this->safe(function () use ($hours) {
            return DB::table($this->jobsTable())
                ->where('created_at', '>=', now()->subHours($hours)->timestamp)
                ->selectRaw('FLOOR((created_at - ?) / 3600) AS bucket, COUNT(*) AS total', [now()->subHours($hours)->timestamp])
                ->groupBy('bucket')
                ->pluck('total', 'bucket')
                ->all();
        }, []);

        $trend = [];
        for ($i = $hours - 1; $i >= 0; $i--) {
            $at = now()->subHours($i);
            $trend[] = [
                'hour' => $at->format('H:00'),
                'count' => (int) ($rows[$hours - 1 - $i] ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * 失败任务统计
     *
     * @return array<string, mixed>
     */
    public function failureStats(): array
    {
        $table = $this->failedTable();

        return [
            'total' => $this->safeCount($table),
            'last_1h' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->where('failed_at', '>=', now()->subHour())->count();
            }),
            'last_24h' => $this->safe(function () use ($table) {
                return (int) DB::table($table)->where('failed_at', '>=', now()->subDay())->count();
            }),
            'by_queue' => $this->safe(function () use ($table) {
                return DB::table($table)
                    ->selectRaw('queue, COUNT(*) AS total, MAX(failed_at) AS last_failed_at')
                    ->groupBy('queue')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();
            }, collect()),
        ];
    }

    /**
     * 失败任务明细
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function latestFailures(int $limit = 20): Collection
    {
        return $this->safe(function () use ($limit) {
            return DB::table($this->failedTable())
                ->orderByDesc('failed_at')
                ->limit($limit)
                ->get();
        }, collect());
    }

    public function failureCount(?string $queue = null): int
    {
        return $this->safe(function () use ($queue) {
            return (int) DB::table($this->failedTable())
                ->when($queue, fn ($q) => $q->where('queue', $queue))
                ->count();
        }, 0);
    }

    /**
     * 批次（Job Batches）状态
     *
     * @return array<string, mixed>
     */
    public function batches(): array
    {
        $table = $this->batchesTable();

        if (! $this->tableExists($table)) {
            return ['available' => false, 'total' => null, 'finished' => null, 'pending' => null, 'failed' => null];
        }

        $total = $this->safeCount($table);

        return [
            'available' => true,
            'total' => $total,
            'finished' => $this->safe(fn () => (int) DB::table($table)->whereNotNull('finished_at')->count()),
            'pending' => $this->safe(fn () => (int) DB::table($table)->whereNull('finished_at')->whereNull('cancelled_at')->count()),
            // job_batches 没有 failed_at 列，失败批次看 failed_jobs 计数
            'failed' => $this->safe(fn () => (int) DB::table($table)->where('failed_jobs', '>', 0)->count()),
        ];
    }

    /**
     * Worker 存活探测
     *
     * 以进程命令行匹配 `queue:work` / `queue:listen`，拿不到权限时返回 null，
     * 此时页面上应提示「以队列积压与 lag 判断」，避免误报「worker 未运行」。
     *
     * @return array{detected:bool, count:?int, samples:array<int,string>}
     */
    public function workers(): array
    {
        $count = RuntimeInspector::processCount('queue:work');

        if ($count === null) {
            $count = RuntimeInspector::processCount('queue:listen');
        }

        return [
            'detected' => $count !== null,
            'count' => $count,
            'samples' => [],
        ];
    }

    // ------------------------------------------------------------------
    // 辅助
    // ------------------------------------------------------------------

    public function tableExists(string $table): bool
    {
        try {
            $schema = Schema::connection(config('queue.connections.database.connection'));

            return $schema->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function safeCount(string $table): ?int
    {
        return $this->safe(fn () => (int) DB::table($table)->count());
    }

    /**
     * 队列表缺失 / 数据库抖动时不能让监控页 500，统一降级为 null 或默认值
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  T|null  $default
     * @return T|null
     */
    protected function safe(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (\Throwable) {
            return $default;
        }
    }
}
