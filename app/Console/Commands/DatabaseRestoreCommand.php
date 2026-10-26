<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 从备份文件恢复数据库
 *
 * 备份的价值取决于「能不能恢复」，因此恢复能力必须与备份成套落地，并定期演练。
 *
 * 用法：
 *   php artisan db:restore storage/app/backups/xxx.sql.gz
 *   php artisan db:restore storage/app/backups/xxx.sql --force    跳过确认
 *
 * 注意：恢复会覆盖现有数据，务必先在维护模式下执行（php artisan down）。
 */
class DatabaseRestoreCommand extends Command
{
    protected $signature = 'db:restore
                            {file : 备份文件路径（支持 .sql / .sql.gz）}
                            {--force : 跳过交互确认}';

    protected $description = '从备份文件恢复数据库';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! is_file($file)) {
            $this->error("备份文件不存在：{$file}");

            return self::FAILURE;
        }

        $sql = $this->readFile($file);

        if (trim($sql) === '') {
            $this->error('备份文件内容为空');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('恢复会覆盖当前数据库，确认继续？', false)) {
            $this->line('已取消');

            return self::SUCCESS;
        }

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        $this->warn("即将恢复到连接 [{$connection}] 的库 [{$database}]");

        $started = microtime(true);
        $statements = 0;

        try {
            DB::connection()->beginTransaction();

            foreach ($this->splitStatements($sql) as $statement) {
                DB::connection()->unprepared($statement);
                $statements++;
            }

            DB::connection()->commit();
        } catch (\Throwable $e) {
            DB::connection()->rollBack();

            Log::error('数据库恢复失败', ['file' => $file, 'error' => $e->getMessage()]);
            $this->error('恢复失败：' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            '恢复完成：执行 %d 条语句，耗时 %.1f 秒',
            $statements,
            microtime(true) - $started
        ));

        return self::SUCCESS;
    }

    protected function readFile(string $file): string
    {
        if (str_ends_with($file, '.gz')) {
            $handle = gzopen($file, 'rb');
            if (! $handle) {
                throw new \RuntimeException('无法打开 gzip 文件，请确认已安装 zlib 扩展');
            }

            $content = '';
            while (! gzeof($handle)) {
                $content .= gzread($handle, 1048576);
            }
            gzclose($handle);

            return $content;
        }

        return (string) file_get_contents($file);
    }

    /**
     * 按分号切分 SQL，跳过注释与空语句
     *
     * @return \Generator<int, string>
     */
    protected function splitStatements(string $sql): \Generator
    {
        foreach (explode(";\n", $sql) as $chunk) {
            $statement = trim($chunk);

            if ($statement === '' || str_starts_with($statement, '--') || str_starts_with($statement, '/*')) {
                continue;
            }

            yield $statement;
        }
    }
}
