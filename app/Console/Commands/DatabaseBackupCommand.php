<?php

namespace App\Console\Commands;

use App\Models\System\DatabaseBackup;
use App\Services\AlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * 数据库备份
 *
 * 主流 ERP 运维惯例：每日全量 + 保留 N 天 + 备份结果本身纳入监控（见 erp:health 的 backup.freshness）。
 * 支持 mysql / pgsql / sqlite；mysqldump 路径可用 ERP_MYSQLDUMP_PATH 指定。
 *
 * 用法：
 *   php artisan db:backup                 备份默认连接
 *   php artisan db:backup --connection=mysql
 *   php artisan db:backup --list          查看备份台账
 */
class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup
                            {--connection= : 备份指定数据库连接，默认使用 default}
                            {--list : 只列出已有备份记录，不执行备份}';

    protected $description = '备份数据库到 storage 磁盘并记录备份台账';

    public function handle(AlertService $alerts): int
    {
        if ($this->option('list')) {
            $this->renderList();

            return self::SUCCESS;
        }

        $connection = $this->option('connection') ?: config('database.default');
        $startedAt = now();
        $timer = microtime(true);

        $record = DatabaseBackup::create([
            'connection' => $connection,
            'database' => config("database.connections.{$connection}.database"),
            'disk' => config('monitoring.backup.disk', 'local'),
            'filename' => '',
            'path' => '',
            'status' => DatabaseBackup::STATUS_RUNNING,
            'started_at' => $startedAt,
        ]);

        try {
            [$filename, $path, $compressed] = $this->dump($connection, $startedAt);

            $size = Storage::disk(config('monitoring.backup.disk', 'local'))->size($path);

            $record->update([
                'filename' => $filename,
                'path' => $path,
                'size_bytes' => $size,
                'compressed' => $compressed,
                'status' => DatabaseBackup::STATUS_SUCCESS,
                'duration_ms' => (int) round((microtime(true) - $timer) * 1000),
                'finished_at' => now(),
            ]);

            $this->info("备份完成：{$filename}（" . $record->sizeForHumans() . '）');

            $this->pruneOldBackups();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $record->update([
                'status' => DatabaseBackup::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $timer) * 1000),
                'finished_at' => now(),
            ]);

            $this->error('备份失败：' . $e->getMessage());
            Log::error('数据库备份失败', ['connection' => $connection, 'error' => $e->getMessage()]);

            $alerts->fire(
                'backup',
                'backup.failed',
                'critical',
                '数据库备份失败',
                "连接 {$connection} 的备份任务执行失败：" . $e->getMessage(),
                ['connection' => $connection]
            );

            return self::FAILURE;
        }
    }

    /**
     * 执行导出，返回 [文件名, 磁盘内相对路径, 是否压缩]
     */
    protected function dump(string $connection, $startedAt): array
    {
        $config = config("database.connections.{$connection}");

        if (! $config) {
            throw new \RuntimeException("数据库连接 [{$connection}] 未配置");
        }

        $driver = $config['driver'];
        $database = (string) ($config['database'] ?? '');
        $stamp = $startedAt->format('Ymd-His');
        $directory = trim(config('monitoring.backup.directory', 'backups'), '/');
        $compress = (bool) config('monitoring.backup.compress', true);

        $rawFilename = sprintf('%s-%s-%s.sql', $database ?: $connection, $connection, $stamp);
        $filename = $compress ? $rawFilename . '.gz' : $rawFilename;
        $path = $directory . '/' . $filename;

        $temporary = storage_path('app/' . $directory . '/' . $rawFilename);
        if (! is_dir(dirname($temporary))) {
            mkdir(dirname($temporary), 0755, true);
        }

        try {
            $sql = match ($driver) {
                'mysql', 'mariadb' => $this->runMysqlDump($config, $database),
                'pgsql' => $this->runPgDump($config, $database),
                'sqlite' => $this->copySqlite($config, $database),
                default => throw new \RuntimeException("暂不支持的数据库驱动：{$driver}"),
            };

            if ($compress && function_exists('gzencode')) {
                $payload = gzencode($sql, 9);
                $sql = null;
            } else {
                $payload = $sql;
                $compress = false;
            }

            Storage::disk(config('monitoring.backup.disk', 'local'))->put($path, $payload);
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }

        return [$filename, $path, $compress];
    }

    protected function runMysqlDump(array $config, string $database): string
    {
        $binary = $this->resolveBinary('mysqldump', 'mysqldump_path', [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
        ]);

        $command = [
            $binary,
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . ($config['username'] ?? 'root'),
            '--single-transaction',
            '--quick',
            '--no-tablespaces',
            '--default-character-set=' . ($config['charset'] ?? 'utf8mb4'),
            $database,
        ];

        $process = new Process(
            $command,
            null,
            // 密码走环境变量，避免出现在进程列表与日志中
            ['MYSQL_PWD' => (string) ($config['password'] ?? '')]
        );
        $process->setTimeout((int) config('monitoring.backup.timeout', 1800));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();

        if (trim($output) === '') {
            throw new \RuntimeException('mysqldump 输出为空，请检查账号权限与库名');
        }

        return $output;
    }

    protected function runPgDump(array $config, string $database): string
    {
        $binary = $this->resolveBinary('pg_dump', 'pg_dump_path', [
            'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
            '/usr/bin/pg_dump',
            '/usr/local/bin/pg_dump',
        ]);

        $process = new Process([
            $binary,
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? 5432),
            '--username=' . ($config['username'] ?? 'postgres'),
            '--no-password',
            $database,
        ], null, ['PGPASSWORD' => (string) ($config['password'] ?? '')]);
        $process->setTimeout((int) config('monitoring.backup.timeout', 1800));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    protected function copySqlite(array $config, string $database): string
    {
        $file = $database !== ':memory:' ? $database : ($config['database'] ?? '');

        if (! $file || ! is_file($file)) {
            throw new \RuntimeException("SQLite 数据库文件不存在：{$file}");
        }

        return (string) file_get_contents($file);
    }

    /**
     * 探测可执行文件：优先 env 指定 → 系统 PATH → 常见安装路径
     */
    protected function resolveBinary(string $name, string $configKey, array $candidates): string
    {
        if ($configured = config('monitoring.backup.' . $configKey)) {
            if (is_file($configured)) {
                return $configured;
            }
            throw new \RuntimeException("已配置 {$name} 路径但文件不存在：{$configured}");
        }

        $lookup = str_starts_with(PHP_OS, 'WIN') ? 'where' : 'which';
        $probe = new Process([$lookup, $name]);
        $probe->run();

        if ($probe->isSuccessful()) {
            $found = trim(explode("\n", trim($probe->getOutput()))[0]);
            if ($found !== '') {
                return $found;
            }
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new \RuntimeException(
            "未找到 {$name} 可执行文件，请在 .env 中配置 ERP_" . strtoupper($name) . "_PATH，或安装数据库客户端工具"
        );
    }

    /**
     * 按保留天数清理过期备份
     */
    protected function pruneOldBackups(): void
    {
        $days = (int) config('monitoring.backup.retention_days', 14);
        if ($days <= 0) {
            return;
        }

        $disk = Storage::disk(config('monitoring.backup.disk', 'local'));
        $cutoff = now()->subDays($days);

        DatabaseBackup::where('created_at', '<', $cutoff)->get()->each(function (DatabaseBackup $backup) use ($disk) {
            if ($backup->path && $disk->exists($backup->path)) {
                $disk->delete($backup->path);
            }
            $backup->delete();
            $this->line("已清理过期备份：{$backup->filename}");
        });
    }

    protected function renderList(): void
    {
        $rows = DatabaseBackup::latest()->limit(20)->get()->map(fn (DatabaseBackup $b) => [
            $b->filename,
            $b->status,
            $b->sizeForHumans(),
            optional($b->finished_at)->format('Y-m-d H:i:s') ?: '-',
            round($b->duration_ms / 1000, 1) . 's',
        ])->all();

        $this->table(['文件名', '状态', '大小', '完成时间', '耗时'], $rows);
    }
}
