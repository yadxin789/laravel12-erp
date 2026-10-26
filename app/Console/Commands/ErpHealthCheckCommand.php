<?php

namespace App\Console\Commands;

use App\Enums\ProductionOrderStatus;
use App\Models\Finance\CustomerInvoice;
use App\Models\Inventory\InventoryBalance;
use App\Models\Production\ProductionOrder;
use App\Models\System\ApprovalTask;
use App\Models\System\DatabaseBackup;
use App\Services\AlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * ERP 业务与系统健康检查
 *
 * 对齐主流 ERP 运维惯例：既看「系统面」（DB / 队列 / 磁盘 / 备份 / 调度心跳），
 * 也看「业务面」（负库存 / 审批积压 / 逾期应收 / 工单延期），
 * 任一指标越界即通过 AlertService 告警，恢复后自动发恢复通知。
 *
 * 用法：
 *   php artisan erp:health              人读表格输出
 *   php artisan erp:health --json       机读，供 Zabbix / Prometheus textfile / 外部拨测采集
 *   php artisan erp:health --no-alert   只检查不告警（维护窗口）
 */
class ErpHealthCheckCommand extends Command
{
    protected $signature = 'erp:health
                            {--json : 以 JSON 输出，便于外部监控系统采集}
                            {--no-alert : 只输出检查结果，不发送告警}';

    protected $description = '检查系统与业务健康状态，异常时按冷却策略发送告警';

    /** @var array<int, array{name:string,group:string,status:string,value:mixed,threshold:string,message:string}> */
    protected array $checks = [];

    /** 本轮仍然 firing 的告警指纹，用于自动恢复 */
    protected array $fingerprints = [];

    public function handle(AlertService $alerts): int
    {
        $started = microtime(true);

        $this->checkScheduler();
        $this->checkDatabase();
        $this->checkQueue();
        $this->checkDisk();
        $this->checkBackup();
        $this->checkBusiness();

        if (! $this->option('no-alert')) {
            $alerts->resolveMissing('health', $this->fingerprints);
        }

        $payload = [
            'checked_at' => now()->toIso8601String(),
            'duration_ms' => (int) round((microtime(true) - $started) * 1000),
            'healthy' => ! collect($this->checks)->contains('status', 'fail'),
            'checks' => $this->checks,
        ];

        // 供 /admin/system-health 页面直接读取，无需每次重跑检查
        Cache::put('erp:health:last_result', $payload, now()->addDay());

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $payload['healthy'] ? self::SUCCESS : self::FAILURE;
        }

        $this->renderTable();

        return $payload['healthy'] ? self::SUCCESS : self::FAILURE;
    }

    // ------------------------------------------------------------------
    // 检查项
    // ------------------------------------------------------------------

    protected function checkDatabase(): void
    {
        try {
            $pdo = DB::connection();
            $pdo->getPdo();
            $this->pass('database.connection', '系统', '可连通');
        } catch (\Throwable $e) {
            $this->markFailed('database.connection', '系统', '不可连通', $e->getMessage());
            $this->raiseAlert('database', 'database.unreachable', 'critical', '数据库连接失败', $e->getMessage());

            return;
        }

        // MySQL 连接数占比
        if (DB::connection()->getDriverName() === 'mysql') {
            try {
                $used = (int) DB::selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value;
                $max = (int) DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value;
                $ratio = $max > 0 ? (int) round($used / $max * 100) : 0;
                $limit = (int) config('monitoring.thresholds.db_connections_warning');

                if ($ratio >= $limit) {
                    $this->markFailed('database.connections', '系统', "{$used}/{$max} ({$ratio}%)", "阈值 {$limit}%");
                    $this->raiseAlert('database', 'database.connections', 'warning', '数据库连接数过高',
                        "当前连接数 {$used}，上限 {$max}，占用 {$ratio}%。", ['used' => $used, 'max' => $max]);
                } else {
                    $this->pass('database.connections', '系统', "{$used}/{$max} ({$ratio}%)");
                }
            } catch (\Throwable $e) {
                $this->warning('database.connections', '系统', '无法获取', $e->getMessage());
            }
        }
    }

    protected function checkQueue(): void
    {
        if (config('queue.default') !== 'database') {
            $this->warning('queue.pending', '系统', '非 database 驱动', '积压检查仅支持 database 队列');

            return;
        }

        $table = config('queue.connections.database.table', 'jobs');
        $pending = DB::table($table)->count();
        $warning = (int) config('monitoring.thresholds.queue_pending_warning');
        $critical = (int) config('monitoring.thresholds.queue_pending_critical');

        if ($pending >= $critical) {
            $this->markFailed('queue.pending', '系统', $pending, "严重阈值 {$critical}");
            $this->raiseAlert('queue', 'queue.backlog', 'critical', '队列严重积压',
                "当前积压 {$pending} 个任务，已超过严重阈值 {$critical}，请确认 queue worker 是否存活。",
                ['pending' => $pending]);
        } elseif ($pending >= $warning) {
            $this->warning('queue.pending', '系统', $pending, "警告阈值 {$warning}");
            $this->raiseAlert('queue', 'queue.backlog', 'warning', '队列积压',
                "当前积压 {$pending} 个任务，超过警告阈值 {$warning}。", ['pending' => $pending]);
        } else {
            $this->pass('queue.pending', '系统', $pending);
        }

        // 最旧任务等待时长：超过阈值基本等同于 worker 已停
        try {
            $oldest = DB::table($table)->min('available_at');
            $lagMinutes = $oldest ? (int) floor((time() - $oldest) / 60) : 0;
            $limit = (int) config('monitoring.thresholds.queue_lag_minutes');

            if ($lagMinutes >= $limit) {
                $this->markFailed('queue.lag', '系统', $lagMinutes . ' 分钟', "阈值 {$limit} 分钟");
                $this->raiseAlert('queue', 'queue.stalled', 'critical', '队列疑似停止消费',
                    "最旧任务已等待 {$lagMinutes} 分钟仍未被消费，queue worker 可能已退出。",
                    ['lag_minutes' => $lagMinutes]);
            } else {
                $this->pass('queue.lag', '系统', $lagMinutes . ' 分钟');
            }
        } catch (\Throwable $e) {
            $this->warning('queue.lag', '系统', '无法获取', $e->getMessage());
        }

        // 失败任务
        $failedTable = config('queue.failed.table', 'failed_jobs');
        $failed = DB::table($failedTable)->where('failed_at', '>=', now()->subHour())->count();
        $failedWarning = (int) config('monitoring.thresholds.failed_jobs_warning');
        $failedCritical = (int) config('monitoring.thresholds.failed_jobs_critical');

        if ($failed >= $failedCritical) {
            $this->markFailed('queue.failed', '系统', $failed, "严重阈值 {$failedCritical}");
            $this->raiseAlert('queue', 'queue.failed', 'critical', '队列任务大量失败',
                "近 1 小时失败任务 {$failed} 个。", ['failed' => $failed]);
        } elseif ($failed >= $failedWarning) {
            $this->warning('queue.failed', '系统', $failed, "警告阈值 {$failedWarning}");
            $this->raiseAlert('queue', 'queue.failed', 'warning', '存在失败的队列任务',
                "近 1 小时失败任务 {$failed} 个，请执行 php artisan queue:failed 查看。", ['failed' => $failed]);
        } else {
            $this->pass('queue.failed', '系统', $failed);
        }
    }

    protected function checkDisk(): void
    {
        $path = storage_path();
        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || ! $total) {
            $this->warning('disk.free', '系统', '无法获取', 'disk_free_space 不可用');

            return;
        }

        $percent = (int) round($free / $total * 100);
        $warning = (int) config('monitoring.thresholds.disk_free_warning');
        $critical = (int) config('monitoring.thresholds.disk_free_critical');

        if ($percent <= $critical) {
            $this->markFailed('disk.free', '系统', $percent . '%', "严重阈值 ≤{$critical}%");
            $this->raiseAlert('disk', 'disk.low', 'critical', '磁盘空间严重不足',
                "存储目录仅剩 {$percent}% 可用空间。", ['path' => $path, 'free_percent' => $percent]);
        } elseif ($percent <= $warning) {
            $this->warning('disk.free', '系统', $percent . '%', "警告阈值 ≤{$warning}%");
            $this->raiseAlert('disk', 'disk.low', 'warning', '磁盘可用空间偏低',
                "存储目录仅剩 {$percent}% 可用空间。", ['path' => $path, 'free_percent' => $percent]);
        } else {
            $this->pass('disk.free', '系统', $percent . '%');
        }
    }

    protected function checkBackup(): void
    {
        if (! config('monitoring.backup.enabled', true)) {
            $this->warning('backup.freshness', '系统', '已禁用', 'ERP_BACKUP_ENABLED=false');

            return;
        }

        $last = DatabaseBackup::where('status', DatabaseBackup::STATUS_SUCCESS)->latest('finished_at')->first();

        if (! $last) {
            $this->markFailed('backup.freshness', '系统', '无成功备份', '从未备份成功');
            $this->raiseAlert('backup', 'backup.missing', 'critical', '从未产生成功的数据库备份',
                '未检测到任何成功的数据库备份记录，请立即确认 db:backup 是否已加入调度。');

            return;
        }

        $ageHours = (int) $last->finished_at->diffInHours(now());
        $maxAge = (int) config('monitoring.thresholds.backup_max_age_hours');

        if ($ageHours > $maxAge) {
            $this->markFailed('backup.freshness', '系统', $last->filename . '（' . $ageHours . ' 小时前）', "阈值 {$maxAge} 小时");
            $this->raiseAlert('backup', 'backup.stale', 'critical', '数据库备份已过期',
                "最近一次成功备份为 {$last->filename}，距今 {$ageHours} 小时，超过阈值 {$maxAge} 小时。",
                ['filename' => $last->filename, 'age_hours' => $ageHours]);
        } else {
            $this->pass('backup.freshness', '系统', $last->filename . '（' . $ageHours . ' 小时前）');
        }
    }

    /**
     * 调度心跳自检：上一次心跳距今过久 → 说明 cron / schedule:work 已停
     */
    protected function checkScheduler(): void
    {
        // 先读旧心跳做判断，再写入本次心跳（供下一轮与外部监控比对）
        $heartbeat = Cache::get('erp:scheduler:heartbeat');

        Cache::put('erp:scheduler:heartbeat', now()->toIso8601String(), now()->addDays(2));

        if (! $heartbeat) {
            $this->warning('scheduler.heartbeat', '系统', '首次运行', '尚无历史心跳');

            return;
        }

        $minutes = (int) \Carbon\Carbon::parse($heartbeat)->diffInMinutes(now());

        // 调度每分钟跑一次，心跳超过 10 分钟未更新即视为异常
        if ($minutes > 10) {
            $this->markFailed('scheduler.heartbeat', '系统', $heartbeat, '超过 10 分钟未更新');
            $this->raiseAlert('health', 'scheduler.stopped', 'critical', '定时任务调度已停止',
                "上次心跳为 {$heartbeat}，距今 {$minutes} 分钟。请检查 cron 或 supervisor 中的 schedule 进程。",
                ['last_heartbeat' => $heartbeat]);
        } else {
            $this->pass('scheduler.heartbeat', '系统', $minutes . ' 分钟前');
        }
    }

    protected function checkBusiness(): void
    {
        // 负库存（数据异常，任何情况下都不应出现）
        $negative = (int) InventoryBalance::where('quantity', '<', 0)->count();
        if ($negative > 0) {
            $this->markFailed('stock.negative', '业务', $negative, '应为 0');
            $this->raiseAlert('stock', 'stock.negative', 'critical', '存在负库存记录',
                "检测到 {$negative} 条负库存余额记录，通常由超发或单据回冲异常导致。",
                ['count' => $negative]);
        } else {
            $this->pass('stock.negative', '业务', 0);
        }

        // 待审批积压
        $pendingApprovals = (int) ApprovalTask::where('status', 'pending')->count();
        if ($pendingApprovals >= 50) {
            $this->warning('approval.backlog', '业务', $pendingApprovals, '阈值 50');
            $this->raiseAlert('approval', 'approval.backlog', 'warning', '待审批任务积压',
                "当前有 {$pendingApprovals} 条待审批任务长时间未处理。", ['count' => $pendingApprovals]);
        } else {
            $this->pass('approval.backlog', '业务', $pendingApprovals);
        }

        // 逾期应收
        $overdue = CustomerInvoice::whereIn('status', ['unpaid', 'partial', 'overdue'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->whereColumn('paid_amount', '<', 'total_amount')
            ->count();
        if ($overdue > 0) {
            $this->warning('finance.overdue', '业务', $overdue, '应为 0');
            $this->raiseAlert('finance', 'finance.overdue_receivable', 'warning', '存在逾期未收回应收账款',
                "当前有 {$overdue} 张客户发票已过账期且未收齐款项。", ['count' => $overdue]);
        } else {
            $this->pass('finance.overdue', '业务', 0);
        }

        // 工单延期（计划完工日已过但未完成）
        $overdueOrders = ProductionOrder::whereNotNull('plan_end_date')
            ->whereDate('plan_end_date', '<', now()->toDateString())
            ->whereNotIn('status', [
                ProductionOrderStatus::Completed->value,
                ProductionOrderStatus::Closed->value,
                ProductionOrderStatus::Cancelled->value,
            ])->count();
        if ($overdueOrders > 0) {
            $this->warning('production.overdue', '业务', $overdueOrders, '应为 0');
            $this->raiseAlert('production', 'production.overdue_orders', 'warning', '存在延期生产工单',
                "当前有 {$overdueOrders} 张生产工单已过计划完工日仍未完工。", ['count' => $overdueOrders]);
        } else {
            $this->pass('production.overdue', '业务', 0);
        }
    }

    // ------------------------------------------------------------------
    // 辅助
    // ------------------------------------------------------------------

    protected function pass(string $name, string $group, mixed $value): void
    {
        $this->record($name, $group, 'ok', $value, '', '');
    }

    protected function warning(string $name, string $group, mixed $value, string $threshold, string $message = ''): void
    {
        $this->record($name, $group, 'warn', $value, $threshold, $message);
    }

    protected function markFailed(string $name, string $group, mixed $value, string $threshold): void
    {
        $this->record($name, $group, 'fail', $value, $threshold, $threshold);
    }

    protected function record(string $name, string $group, string $status, mixed $value, string $threshold, string $message): void
    {
        $this->checks[] = compact('name', 'group', 'status', 'value', 'threshold', 'message');
    }

    protected function raiseAlert(string $source, string $fingerprint, string $level, string $title, string $message, array $context = []): void
    {
        // 本轮仍 firing 的指纹登记，未登记的会在收尾时自动恢复
        if ($level !== 'info') {
            $this->fingerprints[] = $fingerprint;
        }

        if ($this->option('no-alert')) {
            return;
        }

        app(AlertService::class)->fire($source, $fingerprint, $level, $title, $message, $context);
    }

    protected function renderTable(): void
    {
        $icons = ['ok' => '✓', 'warn' => '!', 'fail' => '✗'];

        $this->newLine();
        $this->table(
            ['状态', '分组', '检查项', '实测值', '阈值/说明'],
            collect($this->checks)->map(fn ($c) => [
                $icons[$c['status']],
                $c['group'],
                $c['name'],
                is_scalar($c['value']) ? (string) $c['value'] : json_encode($c['value'], JSON_UNESCAPED_UNICODE),
                $c['threshold'] ?: '-',
            ])->all()
        );

        $failed = collect($this->checks)->where('status', 'fail');
        $warned = collect($this->checks)->where('status', 'warn');

        if ($failed->isNotEmpty()) {
            $this->error("健康检查未通过：{$failed->count()} 项异常、{$warned->count()} 项警告");
        } elseif ($warned->isNotEmpty()) {
            $this->warn("健康检查通过（含 {$warned->count()} 项警告）");
        } else {
            $this->info('健康检查全部通过');
        }
    }
}
