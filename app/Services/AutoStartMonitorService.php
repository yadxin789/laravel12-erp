<?php

namespace App\Services;

use App\Support\RuntimeInspector;

/**
 * 自启动程序（守护进程）清单与状态
 *
 * ERP 要真正「无人值守地跑起来」，至少要有两个常驻角色：
 *   1) 调度驱动器：`schedule:run`（每分钟被 cron / 计划任务拉起）或 `schedule:work`（常驻）；
 *   2) 队列 Worker：`queue:work`，负责异步 Job 与邮件通知。
 *
 * 只要这两个角色没开机自启，定时任务与队列在重启之后就是停摆的，而页面上看不出来
 * （因为没报错）。所以本页的清单不是硬编码的，而是从 deploy/ 目录里现读出来的 ——
 * 部署脚本改了，页面自动跟着变，不会出现「文档写了 A、实际配的是 B」。
 *
 * 状态判定：先在 OS 上真实探测（supervisorctl / systemctl / schtasks / sc / pgrep），
 * 权限不足或命令不存在时降级为 unknown，页面显示「无法检测」而不是谎报「运行中」。
 */
class AutoStartMonitorService
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_STOPPED = 'stopped';

    public const STATUS_NOT_INSTALLED = 'not_installed';

    public const STATUS_UNKNOWN = 'unknown';

    /**
     * 完整清单（按当前操作系统给出可执行的处理建议）
     *
     * @return array<int, array<string, mixed>>
     */
    public function programs(): array
    {
        $programs = array_merge(
            $this->fromSupervisor(),
            $this->fromSystemd(),
            $this->fromCron(),
            $this->fromWindowsScripts(),
            $this->fromProcessProbe()
        );

        foreach ($programs as &$program) {
            $program['status'] = $this->probe($program);
            $program['hint'] = $this->hint($program);
        }
        unset($program);

        return $programs;
    }

    /**
     * 总览数字
     *
     * @return array<string, int>
     */
    public function summary(array $programs): array
    {
        $counts = array_count_values(array_column($programs, 'status'));

        return [
            'total' => count($programs),
            'running' => $counts[self::STATUS_RUNNING] ?? 0,
            'stopped' => $counts[self::STATUS_STOPPED] ?? 0,
            'not_installed' => $counts[self::STATUS_NOT_INSTALLED] ?? 0,
            'unknown' => $counts[self::STATUS_UNKNOWN] ?? 0,
        ];
    }

    // ------------------------------------------------------------------
    // 清单来源：deploy/supervisor/*.conf
    // ------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fromSupervisor(): array
    {
        $programs = [];

        foreach ($this->filesIn('supervisor', '*.conf') as $file) {
            $content = file_get_contents($file);

            preg_match_all('/^\[program:(.+?)\]\s*$(.*?)(?=^\[|\z)/ms', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $name = trim($match[1]);
                $body = $match[2];

                $programs[] = [
                    'name' => $name,
                    'type' => 'Supervisor',
                    'command' => $this->directive($body, 'command'),
                    'processes' => (int) ($this->directive($body, 'numprocs') ?: 1),
                    'autostart' => strtolower((string) $this->directive($body, 'autostart')) !== 'false',
                    'autorestart' => strtolower((string) $this->directive($body, 'autorestart')) !== 'false',
                    'config' => $this->relative($file),
                    'config_exists' => true,
                    'probe' => ['kind' => 'supervisor', 'target' => $name],
                    'install' => sprintf(
                        'sudo cp %s /etc/supervisor/conf.d/ && sudo supervisorctl reread && sudo supervisorctl update',
                        $this->relative($file)
                    ),
                ];
            }
        }

        return $programs;
    }

    // ------------------------------------------------------------------
    // 清单来源：deploy/systemd/*.service
    // ------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fromSystemd(): array
    {
        $programs = [];

        foreach ($this->filesIn('systemd', '*.service') as $file) {
            $content = (string) file_get_contents($file);
            $name = basename($file);

            $programs[] = [
                'name' => $name,
                'type' => 'systemd',
                'command' => $this->directive($content, 'ExecStart'),
                'description' => $this->directive($content, 'Description'),
                'processes' => 1,
                'autostart' => true,
                'autorestart' => str_contains(strtolower($content), 'restart=always'),
                'config' => $this->relative($file),
                'config_exists' => true,
                'probe' => ['kind' => 'systemd', 'target' => $name],
                'install' => sprintf(
                    'sudo cp %s /etc/systemd/system/ && sudo systemctl daemon-reload && sudo systemctl enable --now %s',
                    $this->relative($file),
                    $name
                ),
            ];
        }

        return $programs;
    }

    // ------------------------------------------------------------------
    // 清单来源：deploy/cron/*
    // ------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fromCron(): array
    {
        $programs = [];

        foreach ($this->filesIn('cron', '*') as $file) {
            $content = (string) file_get_contents($file);

            foreach (explode("\n", $content) as $line) {
                $line = trim($line);

                // 只认真正 crontab 行：五个时间字段 + 命令；已注释的行视为示例不算已安装
                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (preg_match('/^(\S+\s+\S+\s+\S+\s+\S+\s+\S+)\s+(.+)$/', $line, $m)) {
                    if (! str_contains($m[2], 'artisan')) {
                        continue;
                    }

                    $programs[] = [
                        'name' => basename($file),
                        'type' => 'Cron',
                        'command' => $m[2],
                        'expression' => $m[1],
                        'processes' => 0,
                        'autostart' => true,
                        'autorestart' => false,
                        'config' => $this->relative($file),
                        'config_exists' => true,
                        'probe' => ['kind' => 'cron', 'target' => 'schedule:run'],
                        'install' => sprintf('sudo cp %s /etc/cron.d/%s && sudo chmod 644 /etc/cron.d/%s', $this->relative($file), basename($file), basename($file)),
                    ];
                }
            }
        }

        return $programs;
    }

    // ------------------------------------------------------------------
    // 清单来源：deploy/windows/*.ps1
    // ------------------------------------------------------------------

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fromWindowsScripts(): array
    {
        $programs = [];

        foreach ($this->filesIn('windows', '*.ps1') as $file) {
            $content = (string) file_get_contents($file);

            // Register-ScheduledTask -TaskName "ERP-Scheduler" ...
            if (preg_match_all('/Register-ScheduledTask\s+-TaskName\s+["\']([\w\-]+)["\']/i', $content, $matches)) {
                foreach (array_unique($matches[1]) as $name) {
                    $programs[] = [
                        'name' => $name,
                        'type' => 'Windows 计划任务',
                        'command' => $this->windowsCommandOf($content, $name),
                        'trigger' => $this->windowsTriggerOf($content, $name),
                        'processes' => 0,
                        'autostart' => true,
                        'autorestart' => true,
                        'config' => $this->relative($file),
                        'config_exists' => true,
                        'probe' => ['kind' => 'win_task', 'target' => $name],
                        'install' => sprintf('PowerShell（管理员）执行 .\\%s', $this->relative($file)),
                    ];
                }
            }

            // nssm install "ERP-Queue" ...
            if (preg_match_all('/nssm(\.Source)?\s+install\s+["\']([\w\-]+)["\']/i', $content, $matches)) {
                foreach (array_unique($matches[2]) as $name) {
                    $programs[] = [
                        'name' => $name,
                        'type' => 'Windows 服务（NSSM）',
                        'command' => 'artisan queue:work database',
                        'processes' => 1,
                        'autostart' => true,
                        'autorestart' => true,
                        'config' => $this->relative($file),
                        'config_exists' => true,
                        'probe' => ['kind' => 'win_service', 'target' => $name],
                        'install' => sprintf('PowerShell（管理员）执行 .\\%s（需先安装 NSSM）', $this->relative($file)),
                    ];
                }
            }
        }

        return $programs;
    }

    /**
     * 进程级兜底：不依赖任何守护框架，直接看这两个关键进程在不在
     *
     * 这是最有说服力的一栏 —— 页面上其它项说的是「配了没」，
     * 这一栏说的是「真的在动没」。
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fromProcessProbe(): array
    {
        return [
            [
                'name' => 'queue:work 进程',
                'type' => '进程探测',
                'command' => 'php artisan queue:work（消费队列 Job）',
                'processes' => 0,
                'autostart' => true,
                'autorestart' => false,
                'config' => null,
                'config_exists' => true,
                'probe' => ['kind' => 'process', 'target' => 'queue:work'],
                'install' => 'php artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=256 &',
            ],
            [
                'name' => 'Schedule 驱动器',
                'type' => '进程探测',
                'command' => 'schedule:run（由 cron / 计划任务拉起）或 schedule:work（常驻）',
                'processes' => 0,
                'autostart' => true,
                'autorestart' => false,
                'config' => null,
                'config_exists' => true,
                'probe' => ['kind' => 'process_any', 'target' => ['schedule:run', 'schedule:work']],
                'install' => 'Linux: crontab 加 `* * * * * php /path/artisan schedule:run`；Windows: 执行 deploy/windows/install-tasks.ps1',
            ],
        ];
    }

    // ------------------------------------------------------------------
    // 状态探测
    // ------------------------------------------------------------------

    protected function probe(array $program): string
    {
        $probe = $program['probe'];
        $target = $probe['target'];

        switch ($probe['kind']) {
            case 'supervisor':
                $result = RuntimeInspector::supervisor($target);

                if (! $result['available']) {
                    return self::STATUS_UNKNOWN;
                }

                return strtoupper((string) $result['state']) === 'RUNNING'
                    ? self::STATUS_RUNNING
                    : self::STATUS_STOPPED;

            case 'systemd':
                $result = RuntimeInspector::systemd($target);

                if (! $result['available']) {
                    return self::STATUS_UNKNOWN;
                }

                return $result['state'] === 'active'
                    ? self::STATUS_RUNNING
                    : ($result['state'] === 'inactive' || $result['state'] === 'failed' ? self::STATUS_STOPPED : self::STATUS_UNKNOWN);

            case 'cron':
                $result = RuntimeInspector::cronContains($target);

                if (! $result['available']) {
                    return self::STATUS_UNKNOWN;
                }

                return $result['matched'] ? self::STATUS_RUNNING : self::STATUS_STOPPED;

            case 'win_task':
                $result = RuntimeInspector::windowsScheduledTask($target);

                if (! $result['available']) {
                    return self::STATUS_UNKNOWN;
                }

                if ($result['state'] === 'not_installed') {
                    return self::STATUS_NOT_INSTALLED;
                }

                return in_array($result['state'], ['Ready', 'Running'], true)
                    ? self::STATUS_RUNNING
                    : self::STATUS_STOPPED;

            case 'win_service':
                $result = RuntimeInspector::windowsService($target);

                if (! $result['available']) {
                    return self::STATUS_UNKNOWN;
                }

                if ($result['state'] === 'not_installed') {
                    return self::STATUS_NOT_INSTALLED;
                }

                return strtoupper((string) $result['state']) === 'RUNNING'
                    ? self::STATUS_RUNNING
                    : self::STATUS_STOPPED;

            case 'process':
                $count = RuntimeInspector::processCount($target);

                if ($count === null) {
                    return self::STATUS_UNKNOWN;
                }

                return $count > 0 ? self::STATUS_RUNNING : self::STATUS_STOPPED;

            case 'process_any':
                foreach ((array) $target as $pattern) {
                    if ((RuntimeInspector::processCount($pattern) ?? 0) > 0) {
                        return self::STATUS_RUNNING;
                    }
                }

                return self::STATUS_STOPPED;

            default:
                return self::STATUS_UNKNOWN;
        }
    }

    /**
     * 针对当前系统给出的一句话操作提示
     */
    protected function hint(array $program): string
    {
        $windows = RuntimeInspector::isWindows();
        $supervisorish = in_array($program['type'], ['Supervisor', 'systemd'], true);
        $cronish = $program['type'] === 'Cron';
        $winish = str_starts_with($program['type'], 'Windows');

        if (! RuntimeInspector::canRunCommands()) {
            return 'Web 进程未启用命令执行（proc_open 被禁用），无法在线检测运行状态。';
        }

        if ($windows && ($supervisorish || $cronish)) {
            return '当前为 Windows 部署，该项属于 Linux 部署方案，请改用 deploy/windows/install-tasks.ps1。';
        }

        if (! $windows && $winish) {
            return '当前为 Linux 部署，该项属于 Windows 部署方案，请改用 supervisor 或 systemd。';
        }

        if ($program['status'] === self::STATUS_NOT_INSTALLED) {
            return $program['install'];
        }

        if ($program['status'] === self::STATUS_STOPPED) {
            return '已注册但未运行，请检查是否启用开机自启。';
        }

        return '';
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_RUNNING => '运行中',
            self::STATUS_STOPPED => '未运行',
            self::STATUS_NOT_INSTALLED => '未安装',
            default => '无法检测',
        };
    }

    public function statusBadge(string $status): string
    {
        return match ($status) {
            self::STATUS_RUNNING => 'success',
            self::STATUS_STOPPED, self::STATUS_NOT_INSTALLED => 'danger',
            default => 'secondary',
        };
    }

    // ------------------------------------------------------------------
    // 辅助
    // ------------------------------------------------------------------

    /**
     * @return array<int, string>
     */
    protected function filesIn(string $directory, string $pattern): array
    {
        $path = base_path('deploy/' . $directory);

        if (! is_dir($path)) {
            return [];
        }

        $files = glob(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $pattern) ?: [];

        return array_values(array_filter($files, 'is_file'));
    }

    protected function directive(string $body, string $key): ?string
    {
        if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=\s*(.+)$/m', $body, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    protected function windowsCommandOf(string $content, string $taskName): string
    {
        // 计划任务的命令由 New-ScheduledTaskAction -Argument 决定
        if (preg_match('/\$(\w+)Action\s*=\s*New-ScheduledTaskAction[^;]*-Argument\s+["\']([^"\']+)["\']/s', $content, $m)) {
            return trim($m[2]);
        }

        return $taskName === 'ERP-Scheduler' ? 'artisan schedule:run（每分钟）' : 'artisan queue:work database';
    }

    protected function windowsTriggerOf(string $content, string $taskName): string
    {
        return $taskName === 'ERP-Scheduler' ? '每分钟重复一次' : '系统启动时（AtStartup）';
    }

    protected function relative(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), '/\\');
    }
}
