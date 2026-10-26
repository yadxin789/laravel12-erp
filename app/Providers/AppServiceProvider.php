<?php

namespace App\Providers;

use App\Listeners\BusinessEventListener;
use App\Services\AlertService;
use App\Support\ScheduleRunRecorder;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Pagination\Paginator;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 前端已切换到 Bootstrap 5，分页组件同步使用 Bootstrap 5 样式
        Paginator::useBootstrapFive();

        // 统一分页视图：Bootstrap 5 样式 + "共 N 条"摘要，且只有一页时也显示页码条
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('pagination.custom');

        // Register event subscriber
        Event::subscribe(BusinessEventListener::class);

        $this->registerFailureAlerts();
        $this->registerScheduleLedger();
    }

    /**
     * 定时任务执行台账
     *
     * 管理员后台「定时任务」页要展示每个任务的「上次执行时间/耗时/成功与否」，
     * 而 Laravel 的调度原生不落任何痕迹，这里在调度事件里补上。
     * 写缓存失败绝不能影响任务本身。
     */
    protected function registerScheduleLedger(): void
    {
        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            try {
                app(ScheduleRunRecorder::class)->record($event->task, [
                    'status' => $event->task->exitCode === 0 ? 'success' : 'failed',
                    'duration_ms' => (int) round($event->runtime * 1000),
                    'exit_code' => $event->task->exitCode,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            try {
                app(ScheduleRunRecorder::class)->record($event->task, [
                    'status' => 'failed',
                    'exception' => mb_substr($event->exception->getMessage(), 0, 500),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });

        // 被重叠保护跳过时也留痕，否则管理员会误以为「任务压根没被调度」
        Event::listen(ScheduledTaskSkipped::class, function (ScheduledTaskSkipped $event) {
            try {
                app(ScheduleRunRecorder::class)->record($event->task, [
                    'status' => 'skipped',
                    'exception' => '上一次执行尚未结束，本次被 withoutOverlapping 跳过',
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /**
     * 队列任务失败即时告警
     *
     * erp:health 每分钟轮询只能发现「积压与失败计数」，这里在任务失败的瞬间就告警，
     * 保证故障响应不依赖轮询周期。
     */
    protected function registerFailureAlerts(): void
    {
        Event::listen(JobFailed::class, function (JobFailed $event) {
            try {
                $message = $event->exception->getMessage();
                $fingerprint = 'queue.job_failed:' . md5(
                    $event->connectionName . '|' . $event->job->getQueue() . '|'
                    . get_class($event->exception) . '|' . mb_substr($message, 0, 100)
                );

                app(AlertService::class)->fire(
                    'queue',
                    $fingerprint,
                    'critical',
                    '队列任务执行失败',
                    sprintf(
                        "队列 [%s/%s] 中的任务 %s 执行失败：%s",
                        $event->connectionName,
                        $event->job->getQueue(),
                        $event->job->resolveName(),
                        mb_substr($message, 0, 500)
                    ),
                    ['job' => $event->job->resolveName(), 'exception' => get_class($event->exception)]
                );
            } catch (\Throwable $e) {
                // 告警自身失败绝不能影响业务
                report($e);
            }
        });
    }
}
