<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

/**
 * 在队列 worker 里执行一条已在 routes/console.php 中注册的调度命令
 *
 * 之所以走队列而不是在 Web 请求里 Artisan::call()：
 * 调度命令往往是分钟级甚至更久的重活，同步执行会直接把 HTTP 请求拖到 504，
 * 而且 HTTP 侧还有 PHP max_execution_time 的限制。
 *
 * 安全性由调用方保证：命令必须来自 ScheduleMonitorService 读取到的清单，
 * 不接受任何用户输入拼接。
 */
class RunScheduledCommandJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** 单条任务最多执行 10 分钟 */
    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(public string $command)
    {
        $this->onQueue(config('queue.connections.database.queue', 'default'));
    }

    public function handle(): void
    {
        Artisan::call($this->command);
    }
}
