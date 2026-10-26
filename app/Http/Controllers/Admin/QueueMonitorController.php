<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QueueMonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * 队列监控与管理
 *
 * 页面本身是只读视图 + 几条运维常用动作（重试失败任务、清理失败记录、
 * 通知 worker 优雅重启）。动作全部走 Artisan 命令，不直接操作数据库，
 * 保证与命令行行为一致 —— 避免"页面点了没生效，命令行里却能重跑"的排查陷阱。
 */
class QueueMonitorController extends Controller
{
    public function index(QueueMonitorService $queue): View
    {
        return view('admin.monitor.queue', [
            'summary' => $queue->summary(),
            'byQueue' => $queue->byQueue(),
            'trend' => $queue->enqueueTrend(12),
            'failures' => $queue->latestFailures(20),
            'failureStats' => $queue->failureStats(),
            'batches' => $queue->batches(),
            'workers' => $queue->workers(),
            'thresholds' => [
                'pending_warning' => config('monitoring.thresholds.queue_pending_warning'),
                'pending_critical' => config('monitoring.thresholds.queue_pending_critical'),
                'lag_minutes' => config('monitoring.thresholds.queue_lag_minutes'),
            ],
        ]);
    }

    /**
     * 重试单个失败任务
     */
    public function retry(string $id): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => $id]);

        return redirect()
            ->route('admin.monitor.queue')
            ->with('success', "已重试失败任务 {$id}，请稍后刷新查看是否再次失败。");
    }

    /**
     * 重试全部失败任务
     */
    public function retryAll(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => 'all']);

        return redirect()
            ->route('admin.monitor.queue')
            ->with('success', '已重试全部失败任务。');
    }

    /**
     * 删除单条失败记录
     */
    public function forget(string $id): RedirectResponse
    {
        Artisan::call('queue:forget', ['id' => $id]);

        return redirect()
            ->route('admin.monitor.queue')
            ->with('success', "已删除失败记录 {$id}。");
    }

    /**
     * 清空失败任务表
     */
    public function flush(): RedirectResponse
    {
        Artisan::call('queue:flush');

        return redirect()
            ->route('admin.monitor.queue')
            ->with('success', '已清空全部失败任务记录。');
    }

    /**
     * 通知 worker 在处理完当前任务后退出（配合 supervisor/systemd 自动拉起）
     *
     * 改了代码之后不用手工重启：worker 会在 cache 里看到这个信号并自行终止，
     * 由守护程序立刻补一个新的进程起来，载入的是新代码。
     */
    public function restart(): RedirectResponse
    {
        Artisan::call('queue:restart');

        return redirect()
            ->route('admin.monitor.queue')
            ->with('success', '已下发优雅重启信号，worker 执行完当前任务后会退出并由守护进程重新拉起。');
    }
}
