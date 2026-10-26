<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| 任务调度
|--------------------------------------------------------------------------
|
| 运行前提（二者其一，详见 DEPLOYMENT.md「自动化运维」章节）：
|   Linux  : crontab 每分钟执行 `php artisan schedule:run`
|   Windows: 任务计划程序每分钟执行 `php.exe artisan schedule:run`
|            或用 `php artisan schedule:work` 常驻（需 NSSM 托管）
|
| withoutOverlapping() 依赖缓存锁，请确保 CACHE_STORE 不是 array。
|
| 亚分钟任务（everyFiveSeconds 等）：schedule:run 仍由 cron / 计划任务每分钟拉起，
| Laravel 会在该分钟的剩余时间内持续轮询到期的任务，因此无需把 cron 改成每 5 秒。
|
*/

// 健康检查：每分钟自检一次，同时作为「调度是否存活」的心跳来源
Schedule::command('erp:health')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/health.log'));

/*
|--------------------------------------------------------------------------
| 状态自动转换：每 5 秒扫描一次
|--------------------------------------------------------------------------
|
| 数量达标 / 到期 / 物料重新齐套驱动的状态跃迁在事件路径被绕过时会卡在中间状态，
| 这里按各模块既有的流转规则补算（详见 StateTransitionService 的注释）：
|   报价单     过了有效期          → expired
|   生产工单   缺料齐套            → ready
|   销售订单   发货数量达标        → partially_delivered / fully_delivered
|   采购订单   收货数量达标        → partially_received / fully_received
|   库存预留   过了到期日          → expired（并释放库存占用）
|
| 5 秒一次的输出不落盘（避免日志文件暴涨），转换明细写入 storage/logs/laravel.log，
| 逐条审计留在 audit_logs 表（action = auto_transition）。
|
*/

Schedule::command('erp:state-transition')
    ->everyFiveSeconds()
    // 20 秒内未完成即视为上次仍在跑，下次跳过，杜绝 5 秒任务堆积
    ->withoutOverlapping(20)
    ->runInBackground();

// 数据库全量备份：业务低峰期
Schedule::command('db:backup')
    ->dailyAt('02:00')
    ->withoutOverlapping(120)
    ->onFailure(function () {
        app(\App\Services\AlertService::class)->fire(
            'backup',
            'backup.schedule_failed',
            'critical',
            '定时备份任务执行失败',
            'db:backup 在调度中执行失败，请查看 storage/logs/scheduler.log 与备份台账。'
        );
    })
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 过期数据轮转
Schedule::command('erp:prune')
    ->dailyAt('03:10')
    ->withoutOverlapping(60)
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 低库存预警：每日上班前推送
Schedule::command('erp:low-stock-alert')
    ->dailyAt('08:00')
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 物流轨迹轮询：在途运单状态变化自动通知
Schedule::command('erp:logistics-track --limit=50 --interval=6')
    ->hourly()
    ->withoutOverlapping(55)
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 电商订单同步：Job 自带同平台 1 小时互斥锁，重复派发不会产生重复单据
Schedule::command('erp:ecommerce-sync')
    ->everyThirtyMinutes()
    ->withoutOverlapping(25)
    ->appendOutputTo(storage_path('logs/scheduler.log'));

// 框架内置的收尾清理（与 erp:prune 互补：前者按小时/按天保留策略清理运行表）
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('queue:prune-failed --hours=720')->daily();
Schedule::command('auth:clear-resets')->daily();
