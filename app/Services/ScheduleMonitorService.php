<?php

namespace App\Services;

use App\Support\RuntimeInspector;
use App\Support\ScheduleRunRecorder;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * 定时任务（Schedule）运行状况
 *
 * 数据来源分三层，互为补充：
 *   1) 静态清单：直接读 Schedule 容器里注册的事件（routes/console.php），拿到命令、
 *      cron 表达式、下次运行时间、是否后台执行、输出落盘位置；
 *   2) 执行台账：ScheduleRunRecorder 通过 ScheduledTaskFinished/Failed 写入的
 *      上次运行时间、耗时、退出码 —— 这才是「有没有真跑起来」的依据；
 *   3) 调度心跳：erp:health 每分钟写一次 Cache，用于判断 schedule:run / schedule:work
 *      这个「驱动器」本身是否还活着。
 *
 * 三者缺一不可：清单存在 ≠ 到点会跑（cron 没配），任务跑过 ≠ 现在还在跑（心跳停了）。
 */
class ScheduleMonitorService
{
    /** 心跳超过该分钟数判定为「调度已停」 */
    public const HEARTBEAT_TIMEOUT_MINUTES = 10;

    public function __construct(protected ScheduleRunRecorder $recorder)
    {
    }

    // ------------------------------------------------------------------
    // 任务清单
    // ------------------------------------------------------------------

    /**
     * 已注册的定时任务（含执行台账）
     *
     * @return array<int, array<string, mixed>>
     */
    public function tasks(): array
    {
        $tasks = [];

        foreach (app(Schedule::class)->events() as $event) {
            $command = $this->normalizeCommand($event);
            $expression = $event->getExpression();
            $repeatSeconds = $this->repeatSecondsOf($event);

            $tasks[] = [
                'key' => ScheduleRunRecorder::keyFor($event),
                'command' => $command,
                'description' => $this->descriptionOf($event),
                'expression' => $expression,
                'schedule_hint' => $this->describeExpression($expression, $repeatSeconds),
                'next_run_at' => $this->safeNextRun($event),
                'background' => $this->runsInBackground($event),
                'output_file' => $this->outputFile($event),
                'repeat_seconds' => $repeatSeconds,
                'last' => $this->recorder->last($event),
                'recent' => $this->recorder->recent($event, 5),
                'artisan' => str_starts_with($command, 'schedule:') || ! str_contains($command, ' '),
            ];
        }

        // 到点时间升序，管理员最关心下一个要跑的任务
        usort($tasks, function (array $a, array $b) {
            return ($a['next_run_at']?->timestamp ?? PHP_INT_MAX) <=> ($b['next_run_at']?->timestamp ?? PHP_INT_MAX);
        });

        return $tasks;
    }

    /**
     * 总览：总数 / 已运行过 / 从未运行 / 最近执行
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $tasks = $this->tasks();
        $now = now();

        $dueSoon = array_values(array_filter($tasks, function (array $task) use ($now) {
            $next = $task['next_run_at'];

            return $next instanceof Carbon
                && $next->greaterThanOrEqualTo($now)
                && $next->diffInMinutes($now) <= 30;
        }));

        $lastRuns = array_values(array_filter(array_column($tasks, 'last')));
        usort($lastRuns, fn ($a, $b) => strcmp((string) ($b['ran_at'] ?? ''), (string) ($a['ran_at'] ?? '')));

        return [
            'total' => count($tasks),
            'never_ran' => count(array_filter($tasks, fn (array $t) => $t['last'] === null)),
            'last_run_at' => $lastRuns[0]['ran_at'] ?? null,
            'due_soon' => $dueSoon,
        ];
    }

    // ------------------------------------------------------------------
    // 调度心跳
    // ------------------------------------------------------------------

    /**
     * @return array{beat:?string, minutes_ago:?int, healthy:?bool}
     */
    public function heartbeat(): array
    {
        $beat = Cache::get('erp:scheduler:heartbeat');

        if (! $beat) {
            return ['beat' => null, 'minutes_ago' => null, 'healthy' => null];
        }

        $minutes = (int) Carbon::parse($beat)->diffInMinutes(now());

        return [
            'beat' => $beat,
            'minutes_ago' => $minutes,
            'healthy' => $minutes <= self::HEARTBEAT_TIMEOUT_MINUTES,
        ];
    }

    // ------------------------------------------------------------------
    // 调度驱动器：cron / 常驻进程
    // ------------------------------------------------------------------

    /**
     * 检测「谁在拉起 schedule:run」
     *
     * @return array<int, array<string, mixed>>
     */
    public function drivers(): array
    {
        $drivers = [];

        if (RuntimeInspector::isWindows()) {
            $task = RuntimeInspector::windowsScheduledTask('ERP-Scheduler');
            $drivers[] = [
                'name' => 'Windows 计划任务 ERP-Scheduler',
                'type' => '计划任务',
                'command' => 'artisan schedule:run（每分钟）',
                'detected' => $task['available'],
                'running' => $task['state'] !== null && $task['state'] !== 'not_installed',
                'state' => $task['state'],
            ];

            $workers = RuntimeInspector::processCount('schedule:work');
            $drivers[] = [
                'name' => '常驻进程 schedule:work',
                'type' => '常驻进程',
                'command' => 'php artisan schedule:work',
                'detected' => $workers !== null,
                'running' => ($workers ?? 0) > 0,
                'state' => $workers === null ? '无法检测' : "{$workers} 个进程",
            ];

            return $drivers;
        }

        $cron = RuntimeInspector::cronContains('schedule:run');
        $drivers[] = [
            'name' => '系统 crontab',
            'type' => 'Cron',
            'command' => '* * * * * php artisan schedule:run',
            'detected' => $cron['available'],
            'running' => $cron['matched'] === true,
            'state' => $cron['available'] ? ($cron['matched'] ? '已配置' : '未配置') : '无法检测',
        ];

        $workers = RuntimeInspector::processCount('schedule:work');
        $drivers[] = [
            'name' => '常驻进程 schedule:work',
            'type' => '常驻进程',
            'command' => 'php artisan schedule:work',
            'detected' => $workers !== null,
            'running' => ($workers ?? 0) > 0,
            'state' => $workers === null ? '无法检测' : "{$workers} 个进程",
        ];

        return $drivers;
    }

    // ------------------------------------------------------------------
    // 日志
    // ------------------------------------------------------------------

    /**
     * 调度日志文件尾部
     *
     * @return array{exists:bool, path:string, size:?string, modified_at:?Carbon, lines:array<int,string>}
     */
    public function tailSchedulerLog(int $lines = 40): array
    {
        $path = storage_path('logs/scheduler.log');

        return [
            'exists' => file_exists($path),
            'path' => $path,
            'size' => file_exists($path) ? $this->humanBytes((int) filesize($path)) : null,
            'modified_at' => file_exists($path) ? Carbon::createFromTimestamp(filemtime($path)) : null,
            'lines' => $this->tail($path, $lines),
        ];
    }

    // ------------------------------------------------------------------
    // 辅助
    // ------------------------------------------------------------------

    /**
     * 从 Event 中提取可执行的命令串
     *
     * Schedule::command('foo') 存入的 command 形如
     *   `/usr/bin/php artisan erp:health --limit=50`
     * 这里剥掉 php 与 artisan 两个前缀，得到 `erp:health --limit=50`。
     */
    protected function normalizeCommand(Event $event): string
    {
        if ($event instanceof CallbackEvent) {
            return 'Closure / Job（回调任务）';
        }

        $raw = trim((string) $event->command);

        if (preg_match('/artisan["\']?\s+(.*)$/i', $raw, $matches)) {
            return trim($matches[1]);
        }

        return $raw !== '' ? $raw : '(未知命令)';
    }

    protected function descriptionOf(Event $event): string
    {
        // getSummaryForDisplay() 在没有显式 ->description() 时会把整条 shell 命令
        // （含 start /b、重定向等 Windows 包装）当成摘要返回，直接展示毫无意义。
        // 此时改为从 Artisan 命令注册表中取该命令自身的说明。
        try {
            $summary = $event->getSummaryForDisplay();
        } catch (\Throwable) {
            $summary = null;
        }

        if (is_string($summary) && $summary !== '' && ! str_contains($summary, 'artisan')) {
            return $summary;
        }

        $name = strtok($this->normalizeCommand($event), ' ');

        if ($name === false) {
            return '';
        }

        try {
            // Artisan facade 指向 Kernel，没有 find()，直接从命令注册表里取
            return (string) (Artisan::all()[$name] ?? null)?->getDescription();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * 输出落盘路径（排除各平台的空设备）
     */
    protected function outputFile(Event $event): ?string
    {
        $output = $event->output;

        if (! $output || in_array(strtoupper($output), ['/DEV/NULL', 'NUL'], true)) {
            return null;
        }

        return $output;
    }

    protected function safeNextRun(Event $event): ?Carbon
    {
        try {
            return $event->nextRunDate(now());
        } catch (\Throwable) {
            return null;
        }
    }

    protected function runsInBackground(Event $event): bool
    {
        try {
            return str_contains($event->buildCommand(), '&');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * 亚分钟任务的重复间隔（秒）
     *
     * everyFiveSeconds() 在底层仍然是 `* * * * *`，重复间隔存在私有属性里，
     * 不读出来页面上就只能看到「每分钟」，与实际执行频次不符。
     */
    protected function repeatSecondsOf(Event $event): ?int
    {
        try {
            $property = new \ReflectionProperty(Event::class, 'repeatSeconds');
            $property->setAccessible(true);
            $value = $property->getValue($event);

            return is_int($value) && $value > 0 ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * cron 表达式中文描述
     *
     * 只覆盖本项目用到的常见写法，解析不了就原样给出表达式，
     * 管理员真正关心的始终是「下一次什么时候跑」。
     */
    public function describeExpression(string $expression, ?int $repeatSeconds = null): string
    {
        if ($repeatSeconds !== null) {
            return "每 {$repeatSeconds} 秒";
        }

        return match (trim($expression)) {
            '* * * * *' => '每分钟',
            '0 * * * *' => '每小时整点',
            '30 * * * *' => '每小时第 30 分',
            '*/5 * * * *' => '每 5 分钟',
            '*/10 * * * *' => '每 10 分钟',
            '*/30 * * * *' => '每 30 分钟',
            '0 */2 * * *' => '每 2 小时',
            '0 2 * * *' => '每天 02:00',
            '0 3 * * *' => '每天 03:00',
            '10 3 * * *' => '每天 03:10',
            '0 8 * * *' => '每天 08:00',
            default => $this->describeGeneric($expression),
        };
    }

    protected function describeGeneric(string $expression): string
    {
        $parts = preg_split('/\s+/', trim($expression)) ?: [];

        if (count($parts) === 5) {
            [$minute, $hour, $day, $month, $weekday] = $parts;

            if ($minute === '*' && $hour === '*') {
                return '每分钟';
            }

            if (str_starts_with($minute, '*/') && $hour === '*') {
                return '每 ' . substr($minute, 2) . ' 分钟';
            }

            if (is_numeric($minute) && $hour === '*') {
                return "每小时第 {$minute} 分";
            }

            if (is_numeric($minute) && is_numeric($hour)) {
                return sprintf('每天 %02d:%02d', (int) $hour, (int) $minute);
            }
        }

        return $expression;
    }

    /**
     * @return array<int, string>
     */
    public function tail(string $path, int $lines = 40): array
    {
        if (! file_exists($path) || ! is_readable($path)) {
            return [];
        }

        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        // 从文件尾部反向按块读取，避免把几十 MB 日志整份载入内存
        $buffer = '';
        $chunkSize = 8192;
        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);
        $foundLines = 0;

        while ($position > 0 && $foundLines <= $lines) {
            $readSize = min($chunkSize, $position);
            $position -= $readSize;
            fseek($handle, $position);
            $buffer = fread($handle, $readSize) . $buffer;
            $foundLines = substr_count($buffer, "\n");
        }

        fclose($handle);

        $all = preg_split('/\R/u', trim($buffer)) ?: [];

        return array_slice($all, max(0, count($all) - $lines));
    }

    public function humanBytes(float|int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
