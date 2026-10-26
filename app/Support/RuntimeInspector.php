<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

/**
 * 服务器运行时探测
 *
 * 定时任务 / 队列 / 自启动程序三个页面都需要「SSH 上去敲命令」才能拿到的信息：
 * supervisor 是否在跑、systemd unit 是否 active、Windows 计划任务是否注册、
 * crontab 里有没有 schedule:run、机器负载与内存。
 *
 * 这里统一封装，并处理三类现实约束：
 *   1) exec / shell_exec 可能被 disable_functions 禁用；
 *   2) Web 进程（www-data / IIS 用户）通常没有 supervisorctl / systemctl 权限；
 *   3) 命令可能不存在或超时 —— 一律降级为「无法检测」，绝不让监控页面 500。
 *
 * 所有执行结果短时缓存，避免 F5 刷新变成一次进程扫描风暴。
 */
class RuntimeInspector
{
    /** 探测结果缓存秒数 */
    public const CACHE_TTL = 20;

    /** 单条命令最长执行时间（秒） */
    public const TIMEOUT = 6;

    // ------------------------------------------------------------------
    // 平台
    // ------------------------------------------------------------------

    public static function os(): string
    {
        $family = strtolower(PHP_OS_FAMILY);

        return match ($family) {
            'windows' => 'windows',
            'darwin' => 'darwin',
            'linux', 'bsd', 'solaris' => 'linux',
            default => 'unknown',
        };
    }

    public static function isWindows(): bool
    {
        return self::os() === 'windows';
    }

    /** 命令执行能力是否可用（没有被 disable_functions 掐掉） */
    public static function canRunCommands(): bool
    {
        return function_exists('proc_open')
            && ! in_array('proc_open', array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions')))), true);
    }

    // ------------------------------------------------------------------
    // 命令执行
    // ------------------------------------------------------------------

    /**
     * @return array{ok:bool, output:string, exit:int, error:string}
     */
    public static function run(string $command, int $timeout = self::TIMEOUT): array
    {
        $empty = ['ok' => false, 'output' => '', 'exit' => -1, 'error' => '命令执行不可用'];

        if (! self::canRunCommands()) {
            return $empty;
        }

        $cacheKey = 'erp:runtime:cmd:' . md5($command) . ':' . $timeout;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($command, $timeout) {
            try {
                $result = Process::timeout($timeout)->run($command);

                return [
                    'ok' => $result->successful(),
                    'output' => trim((string) $result->output()),
                    'error' => trim((string) $result->errorOutput()),
                    'exit' => $result->exitCode(),
                ];
            } catch (\Throwable $e) {
                return [
                    'ok' => false,
                    'output' => '',
                    'error' => $e->getMessage(),
                    'exit' => -1,
                ];
            }
        });
    }

    /** 只取标准输出，失败返回 null */
    public static function outputOf(string $command, int $timeout = self::TIMEOUT): ?string
    {
        $result = self::run($command, $timeout);

        return $result['ok'] && $result['output'] !== '' ? $result['output'] : null;
    }

    // ------------------------------------------------------------------
    // 自启动载体探测
    // ------------------------------------------------------------------

    /**
     * Supervisor 程序状态
     *
     * @return array{available:bool, state:?string, raw:?string}
     */
    public static function supervisor(string $program): array
    {
        $notAvailable = ['available' => false, 'state' => null, 'raw' => null];

        if (self::isWindows()) {
            return $notAvailable;
        }

        $result = self::run('supervisorctl status ' . escapeshellarg($program));

        if (! $result['ok'] && str_contains(strtolower($result['error'] . $result['output']), 'command not found')) {
            return $notAvailable;
        }

        $text = $result['output'] ?: $result['error'];

        if ($text === '' || str_contains(strtolower($text), 'no such process') || str_contains(strtolower($text), 'error:')) {
            return $notAvailable;
        }

        // 典型输出：erp-worker:erp-worker_00   RUNNING   pid 1234, uptime 1:20:00
        $parts = preg_split('/\s+/', $text);

        return [
            'available' => true,
            'state' => $parts[1] ?? null,
            'raw' => $text,
        ];
    }

    /**
     * systemd unit 状态
     *
     * @return array{available:bool, state:?string, raw:?string}
     */
    public static function systemd(string $unit): array
    {
        $notAvailable = ['available' => false, 'state' => null, 'raw' => null];

        if (self::isWindows()) {
            return $notAvailable;
        }

        $result = self::run('systemctl is-active ' . escapeshellarg($unit));
        $state = trim($result['output']);

        if (! $result['ok'] && ! in_array($state, ['active', 'inactive', 'failed', 'activating', 'deactivating'], true)) {
            // systemctl 不存在，或 unit 未安装，或非 systemd 发行版
            return $notAvailable;
        }

        return [
            'available' => true,
            'state' => $state !== '' ? $state : 'unknown',
            'raw' => $state,
        ];
    }

    /**
     * crontab 中是否包含指定内容
     *
     * @return array{available:bool, matched:?bool}
     */
    public static function cronContains(string $needle): array
    {
        $notAvailable = ['available' => false, 'matched' => null];

        if (self::isWindows()) {
            return $notAvailable;
        }

        // -u 指定当前用户可规避 root 场景下 crontab -l 报 "no crontab for root" 的差异
        $result = self::run('crontab -l 2>/dev/null');

        if (! $result['ok']) {
            return $notAvailable;
        }

        $lines = array_filter(array_map('trim', explode("\n", $result['output'])));
        $effective = array_filter($lines, fn ($line) => ! str_starts_with($line, '#'));

        if ($effective === []) {
            return ['available' => true, 'matched' => false];
        }

        return [
            'available' => true,
            'matched' => (bool) preg_match('/' . preg_quote($needle, '/') . '/i', implode("\n", $effective)),
        ];
    }

    /**
     * Windows 计划任务
     *
     * @return array{available:bool, state:?string, raw:?string}
     */
    public static function windowsScheduledTask(string $name): array
    {
        $notAvailable = ['available' => false, 'state' => null, 'raw' => null];

        if (! self::isWindows()) {
            return $notAvailable;
        }

        $name = str_replace(['"', "'", '`', '|', '&', ';'], '', $name);

        // /fo csv 便于解析；Query 失败时会同时写到 stderr
        $result = self::run(sprintf('schtasks /Query /TN "%s" /FO CSV /NH', $name), 10);
        $text = trim($result['output'] ?: $result['error']);

        if (! $result['ok'] || $text === '') {
            return ['available' => true, 'state' => 'not_installed', 'raw' => null];
        }

        $columns = str_getcsv($text);

        return [
            'available' => true,
            'state' => trim($columns[3] ?? 'Unknown'),
            'raw' => $text,
        ];
    }

    /**
     * Windows 服务（NSSM 注册的 ERP-Queue 走这里）
     *
     * @return array{available:bool, state:?string, raw:?string}
     */
    public static function windowsService(string $name): array
    {
        $notAvailable = ['available' => false, 'state' => null, 'raw' => null];

        if (! self::isWindows()) {
            return $notAvailable;
        }

        $name = str_replace(['"', "'", '`', '|', '&', ';'], '', $name);

        $result = self::run(sprintf('sc query "%s"', $name), 10);
        $text = trim($result['output'] ?: $result['error']);

        if (! $result['ok'] || $text === '') {
            return ['available' => true, 'state' => 'not_installed', 'raw' => null];
        }

        preg_match('/STATE\s*:\s*\d+\s+(\w+)/i', $text, $matches);

        return [
            'available' => true,
            'state' => $matches[1] ?? 'UNKNOWN',
            'raw' => $text,
        ];
    }

    /**
     * 匹配命令行（而非进程名）的进程数量
     *
     * Linux 用 pgrep -f，Windows 用 PowerShell 的 Win32_Process。
     * 拿不到权限 / 命令缺失时返回 null，由上层显示「无法检测」。
     */
    public static function processCount(string $pattern): ?int
    {
        if (self::isWindows()) {
            $safe = str_replace(["'", '"', '`', '$'], '', $pattern);
            $ps = sprintf(
                'powershell -NoProfile -NonInteractive -Command "Get-CimInstance Win32_Process -Filter \\"Name=\'php.exe\'\\" | Where-Object { $_.CommandLine -like \'*%s*\' } | Measure-Object | Select-Object -ExpandProperty Count"',
                $safe
            );

            $result = self::run($ps, 15);

            if (! $result['ok']) {
                return null;
            }

            return is_numeric(trim($result['output'])) ? (int) trim($result['output']) : null;
        }

        $result = self::run('pgrep -fc ' . escapeshellarg($pattern));

        // pgrep 无匹配返回 1，且有输出数字；命令不存在时输出为空
        $output = trim($result['output']);

        if ($output === '' || ! is_numeric($output)) {
            return null;
        }

        return (int) $output;
    }

    // ------------------------------------------------------------------
    // 机器负载
    // ------------------------------------------------------------------

    /** 系统开机时长（秒） */
    public static function uptimeSeconds(): ?int
    {
        if (self::isWindows()) {
            // wmic 已废弃但仍普遍可用；失败时回落到 PowerShell
            $value = self::outputOf('powershell -NoProfile -NonInteractive -Command "(Get-Date) - (gcim Win32_OperatingSystem).LastBootUpTime | ForEach-Object { [int]$_.TotalSeconds }"', 15);

            return is_numeric($value) ? (int) trim($value) : null;
        }

        $value = self::outputOf('cat /proc/uptime');

        if ($value !== null && preg_match('/^([\d.]+)/', $value, $m)) {
            return (int) floor((float) $m[1]);
        }

        return null;
    }

    /**
     * CPU 负载（1 / 5 / 15 分钟）
     *
     * @return array{1min:?float, 5min:?float, 15min:?float}
     */
    public static function loadAverage(): array
    {
        if (self::isWindows()) {
            // Windows 没有 load average，改用 CPU 利用率近似
            $value = self::outputOf('powershell -NoProfile -NonInteractive -Command "(gcim Win32_Processor).LoadPercentage"', 15);

            return [
                '1min' => is_numeric($value) ? (float) trim($value) : null,
                '5min' => null,
                '15min' => null,
            ];
        }

        $load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

        if (! $load) {
            return ['1min' => null, '5min' => null, '15min' => null];
        }

        return [
            '1min' => round((float) $load[0], 2),
            '5min' => round((float) $load[1], 2),
            '15min' => round((float) $load[2], 2),
        ];
    }

    /**
     * 物理内存占用
     *
     * @return array{total_mb:?float, used_mb:?float, free_mb:?float, percent:?float}
     */
    public static function memoryUsage(): array
    {
        $empty = ['total_mb' => null, 'used_mb' => null, 'free_mb' => null, 'percent' => null];

        if (self::isWindows()) {
            $value = self::outputOf('powershell -NoProfile -NonInteractive -Command "$o=gcim Win32_OperatingSystem; [math]::Round($o.TotalVisibleMemorySize/1KB,0); [math]::Round($o.FreePhysicalMemory/1KB,0)"', 15);

            if ($value === null) {
                return $empty;
            }

            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $value))));

            if (count($lines) < 2) {
                return $empty;
            }

            $total = (float) $lines[0];
            $free = (float) $lines[1];

            return [
                'total_mb' => $total,
                'free_mb' => $free,
                'used_mb' => $total - $free,
                'percent' => $total > 0 ? round(($total - $free) / $total * 100, 1) : null,
            ];
        }

        $raw = self::outputOf('cat /proc/meminfo');

        if ($raw === null) {
            return $empty;
        }

        $metrics = [];
        foreach (explode("\n", $raw) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $metrics[$m[1]] = (float) $m[2];
            }
        }

        $total = ($metrics['MemTotal'] ?? 0) / 1024;
        $available = ($metrics['MemAvailable'] ?? 0) / 1024;

        if ($total <= 0) {
            return $empty;
        }

        return [
            'total_mb' => round($total),
            'free_mb' => round($available),
            'used_mb' => round($total - $available),
            'percent' => round(($total - $available) / $total * 100, 1),
        ];
    }
}
