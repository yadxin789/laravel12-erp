<?php

namespace App\Services;

use App\Models\System\DatabaseBackup;
use App\Support\RuntimeInspector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 系统工作状态（运行时体检）
 *
 * 与 erp:health 的分工：
 *   - erp:health 面向「告警」，回答的是「哪些指标越界了」；
 *   - 本服务面向「运维看板上那一屏」，回答的是「这台机器/这个实例现在跑得怎么样」，
 *     包括环境、版本、连接资源、磁盘、内存、负载、队列与调度、备份、错误日志。
 *
 * 所有外部资源（磁盘、数据库、命令执行）都做了兜底：拿不到就是 null，
 * 页面上显示「无法获取」，而不是让整个运维看板 500。
 */
class SystemStatusService
{
    public function __construct(
        protected QueueMonitorService $queue,
        protected ScheduleMonitorService $schedule,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return [
            'generated_at' => now(),
            'environment' => $this->environment(),
            'runtime' => $this->runtime(),
            'database' => $this->database(),
            'cache' => $this->cache(),
            'queue' => $this->queueSection(),
            'schedule' => $this->scheduleSection(),
            'backup' => $this->backup(),
            'storage' => $this->storage(),
            'logs' => $this->logs(),
            'health' => $this->health(),
            'alerts' => $this->alerts(),
            'counts' => $this->counts(),
        ];
    }

    // ------------------------------------------------------------------
    // 环境
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function environment(): array
    {
        return [
            'app_name' => config('app.name'),
            'environment' => app()->environment(),
            'debug' => (bool) config('app.debug'),
            'url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'php_binary' => PHP_BINARY,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'opcache_enabled' => function_exists('opcache_get_status')
                ? (bool) (opcache_get_status(false)['opcache_enabled'] ?? false)
                : null,
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? null,
            'maintenance' => app()->isDownForMaintenance(),
        ];
    }

    // ------------------------------------------------------------------
    // 机器运行时
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function runtime(): array
    {
        $memory = RuntimeInspector::memoryUsage();
        $load = RuntimeInspector::loadAverage();
        $uptime = RuntimeInspector::uptimeSeconds();

        return [
            'uptime_seconds' => $uptime,
            'uptime_human' => $uptime !== null ? $this->humanDuration($uptime) : null,
            'load' => $load,
            'memory' => $memory,
            'cpu_cores' => $this->cpuCores(),
            'disk_usage_percent' => $this->diskUsagePercent(),
        ];
    }

    // ------------------------------------------------------------------
    // 数据库
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function database(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        $started = microtime(true);
        try {
            $connection->getPdo();
            $latency = (int) round((microtime(true) - $started) * 1000);
            $reachable = true;
            $error = null;
        } catch (\Throwable $e) {
            $latency = null;
            $reachable = false;
            $error = $e->getMessage();
        }

        $result = [
            'driver' => $driver,
            'database' => config('database.default') ? $connection->getDatabaseName() : null,
            'host' => config('database.connections.' . config('database.default') . '.host'),
            'reachable' => $reachable,
            'latency_ms' => $latency,
            'error' => $error,
            'version' => null,
            'size_mb' => null,
            'tables' => null,
            'connections_used' => null,
            'connections_max' => null,
        ];

        if (! $reachable) {
            return $result;
        }

        try {
            $result['version'] = match ($driver) {
                'mysql', 'mariadb' => trim((string) $connection->selectOne('SELECT VERSION() AS v')->v),
                'pgsql' => trim((string) $connection->selectOne('SELECT version() AS v')->v),
                'sqlite' => 'SQLite ' . trim((string) $connection->selectOne('SELECT sqlite_version() AS v')->v),
                default => null,
            };
        } catch (\Throwable) {
            // 版本信息拿不到不影响其它指标
        }

        try {
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $stats = $connection->selectOne(
                    'SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb, COUNT(*) AS tables
                     FROM information_schema.TABLES WHERE table_schema = DATABASE()'
                );
                $result['size_mb'] = (float) $stats->size_mb;
                $result['tables'] = (int) $stats->tables;

                $used = $connection->selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value;
                $max = $connection->selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value;
                $result['connections_used'] = (int) $used;
                $result['connections_max'] = (int) $max;
            } elseif ($driver === 'sqlite') {
                $result['tables'] = (int) $connection
                    ->selectOne("SELECT COUNT(*) AS total FROM sqlite_master WHERE type = 'table'")
                    ->total;
                $result['size_mb'] = round((float) ($this->fileSize((string) $connection->getDatabaseName()) / 1024 / 1024), 2);
            }
        } catch (\Throwable) {
            // 统计失败保留 null
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // 缓存 / 队列 / 调度
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function cache(): array
    {
        $store = config('cache.default');

        $started = microtime(true);
        $key = 'erp:status:probe:' . (string) time();

        try {
            Cache::store($store)->put($key, 1, 10);
            $ok = Cache::store($store)->get($key) === 1;
            Cache::store($store)->forget($key);
            $latency = (int) round((microtime(true) - $started) * 1000);
        } catch (\Throwable $e) {
            $ok = false;
            $latency = null;
            $error = $e->getMessage();
        }

        return [
            'store' => $store,
            'writable' => $ok,
            'latency_ms' => $latency ?? null,
            'error' => $error ?? null,
            // 锁驱动的判断很关键：withoutOverlapping 依赖非 array 缓存，
            // 用 array 会导致 excepts 互斥失效，重复叠加执行。
            'lock_driver' => config('cache.default') === 'array' ? 'array（互斥失效，请改用 database/redis）' : $store,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function queueSection(): array
    {
        return array_merge($this->queue->summary(), [
            'workers' => $this->queue->workers(),
            'by_queue' => $this->queue->byQueue(),
            'failure_stats' => $this->queue->failureStats(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function scheduleSection(): array
    {
        return array_merge($this->schedule->summary(), [
            'heartbeat' => $this->schedule->heartbeat(),
            'drivers' => $this->schedule->drivers(),
        ]);
    }

    // ------------------------------------------------------------------
    // 备份 / 存储 / 日志
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function backup(): array
    {
        try {
            $last = DatabaseBackup::latest()->first();
            $success = DatabaseBackup::where('status', DatabaseBackup::STATUS_SUCCESS)->latest('finished_at')->first();
        } catch (\Throwable) {
            return ['available' => false];
        }

        return [
            'available' => true,
            'enabled' => (bool) config('monitoring.backup.enabled', true),
            'last' => $last,
            'last_success' => $success,
            'total_records' => DatabaseBackup::count(),
            'age_hours' => $success?->finished_at?->diffInHours(now()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function storage(): array
    {
        $paths = [
            '项目目录' => base_path(),
            'storage' => storage_path(),
            'public' => public_path(),
        ];

        $result = [];
        foreach ($paths as $label => $path) {
            $free = @disk_free_space($path);
            $total = @disk_total_space($path);

            $result[] = [
                'label' => $label,
                'path' => $path,
                'free' => $free !== false ? $free : null,
                'total' => $total !== false && $total > 0 ? $total : null,
                'percent_free' => ($free !== false && $total > 0) ? (int) round($free / $total * 100) : null,
            ];
        }

        // 日志总体积：堆积的日志往往是磁盘告警的元凶
        $result[] = [
            'label' => '日志目录',
            'path' => storage_path('logs'),
            'free' => null,
            'total' => $this->directorySize(storage_path('logs')),
            'percent_free' => null,
        ];

        return [
            'paths' => $result,
            'writable' => is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function logs(): array
    {
        $files = glob(storage_path('logs/*.log')) ?: [];

        $items = [];
        foreach ($files as $file) {
            $items[] = [
                'name' => basename($file),
                'size' => (int) filesize($file),
                'size_human' => $this->humanBytes((int) filesize($file)),
                'modified_at' => Carbon::createFromTimestamp(filemtime($file)),
            ];
        }

        usort($items, fn ($a, $b) => $b['size'] <=> $a['size']);

        // 应用日志可能有多个（laravel.log / laravel-YYYY-MM-DD.log），取最近动过的那份
        $logFiles = array_values(array_filter($files, fn ($f) => str_starts_with(basename($f), 'laravel')));

        if ($logFiles !== []) {
            usort($logFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        }

        $recentErrors = [];

        if ($logFiles !== []) {
            foreach (array_reverse($this->tailFile($logFiles[0], 300)) as $line) {
                if (preg_match('/\[([\d\-\s:]+)\]\s+(\w+)\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $line, $m)) {
                    $recentErrors[] = [
                        'at' => $m[1],
                        'level' => $m[3],
                        'channel' => $m[2],
                        'line' => mb_substr($line, 0, 300),
                    ];

                    if (count($recentErrors) >= 10) {
                        break;
                    }
                }
            }
        }

        return [
            'files' => $items,
            'total_size' => array_sum(array_column($items, 'size')),
            'source_file' => $logFiles[0] ?? null,
            'recent_errors' => $recentErrors,
            'error_window' => '最近 300 行日志',
        ];
    }

    /**
     * 读取文件最后 N 行（从尾部按块反向读，避免把几十 MB 日志整份载入）
     *
     * @return array<int, string>
     */
    protected function tailFile(string $path, int $lines = 100): array
    {
        if (! file_exists($path) || ! is_readable($path)) {
            return [];
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $buffer = '';
        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);
        $found = 0;

        while ($position > 0 && $found <= $lines) {
            $readSize = min(8192, $position);
            $position -= $readSize;
            fseek($handle, $position);
            $buffer = fread($handle, $readSize) . $buffer;
            $found = substr_count($buffer, "\n");
        }

        fclose($handle);

        $all = preg_split('/\R/u', trim($buffer)) ?: [];

        return array_slice($all, max(0, count($all) - $lines));
    }

    // ------------------------------------------------------------------
    // 健康检查结果与告警
    // ------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        $cached = Cache::get('erp:health:last_result');

        // 面板打开时若从未检查过，就地跑一次（不发送告警，避免刷新页面触发通知）
        if (! $cached) {
            try {
                Artisan::call('erp:health', ['--no-alert' => true]);
                $cached = Cache::get('erp:health:last_result');
            } catch (\Throwable) {
                // 忽略：健康检查失败时页面仍要显示其它模块
            }
        }

        if (! $cached) {
            return ['available' => false, 'healthy' => null, 'checks' => []];
        }

        $groups = collect($cached['checks'] ?? [])
            ->groupBy('group')
            ->map(fn ($group) => [
                'total' => $group->count(),
                'failed' => $group->where('status', 'fail')->count(),
                'warned' => $group->where('status', 'warn')->count(),
            ])
            ->all();

        return [
            'available' => true,
            'healthy' => $cached['healthy'] ?? null,
            'checked_at' => isset($cached['checked_at']) ? Carbon::parse($cached['checked_at']) : null,
            'duration_ms' => $cached['duration_ms'] ?? null,
            'checks' => $cached['checks'] ?? [],
            'groups' => $groups,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function alerts(): array
    {
        try {
            $firing = app(AlertService::class)->firing();

            return [
                'firing' => $firing->take(10),
                'firing_count' => $firing->count(),
                'monitoring_enabled' => (bool) config('monitoring.enabled', true),
            ];
        } catch (\Throwable) {
            return ['firing' => collect(), 'firing_count' => 0, 'monitoring_enabled' => false];
        }
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $safe = function (string $class): int {
            try {
                return $class::count();
            } catch (\Throwable) {
                return 0;
            }
        };

        return [
            'users' => $safe(\App\Models\User::class),
            'roles' => $safe(\Spatie\Permission\Models\Role::class),
            'audit_logs_24h' => (function () {
                try {
                    return \App\Models\System\AuditLog::where('created_at', '>=', now()->subDay())->count();
                } catch (\Throwable) {
                    return 0;
                }
            })(),
            'pending_approvals' => (function () {
                try {
                    return \App\Models\System\ApprovalTask::where('status', 'pending')->count();
                } catch (\Throwable) {
                    return 0;
                }
            })(),
        ];
    }

    // ------------------------------------------------------------------
    // 辅助
    // ------------------------------------------------------------------

    protected function cpuCores(): ?int
    {
        if (RuntimeInspector::isWindows()) {
            $value = (int) getenv('NUMBER_OF_PROCESSORS');

            return $value > 0 ? $value : null;
        }

        $cpuinfo = @file_get_contents('/proc/cpuinfo');

        if ($cpuinfo !== false) {
            $count = preg_match_all('/^processor\s*:/m', $cpuinfo);

            if ($count > 0) {
                return $count;
            }
        }

        return null;
    }

    protected function diskUsagePercent(): ?float
    {
        $free = @disk_free_space(storage_path());
        $total = @disk_total_space(storage_path());

        if ($free === false || ! $total) {
            return null;
        }

        return round(($total - $free) / $total * 100, 1);
    }

    protected function fileSize(string $path): int
    {
        return file_exists($path) ? (int) filesize($path) : 0;
    }

    protected function directorySize(string $path): ?int
    {
        if (! is_dir($path)) {
            return null;
        }

        $size = 0;

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
        } catch (\Throwable) {
            return null;
        }

        return $size;
    }

    public function humanDuration(int $seconds): string
    {
        $units = [
            '天' => (int) floor($seconds / 86400),
            '小时' => (int) floor(($seconds % 86400) / 3600),
            '分钟' => (int) floor(($seconds % 3600) / 60),
        ];

        $parts = array_filter($units, fn ($v) => $v > 0);

        if ($parts === []) {
            return '不足 1 分钟';
        }

        $text = '';
        foreach ($parts as $unit => $value) {
            $text .= $value . ' ' . $unit;
        }

        return $text;
    }

    public function humanBytes(float|int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, $precision) . ' ' . $units[$index];
    }
}
