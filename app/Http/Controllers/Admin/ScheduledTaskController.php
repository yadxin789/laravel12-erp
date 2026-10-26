<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunScheduledCommandJob;
use App\Services\ScheduleMonitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * 定时任务监控
 *
 * 回答三个管理员一定会问的问题：
 *   1) 到底注册了哪些任务、多久跑一次、下一次什么时候跑（清单）；
 *   2) 它们最近真的跑了吗、花了多久、成功还是失败（执行台账）；
 *   3) 拉起这些任务的那个 cron / 常驻进程还在吗（调度驱动器 + 心跳）。
 */
class ScheduledTaskController extends Controller
{
    public function index(ScheduleMonitorService $schedule): View
    {
        return view('admin.monitor.scheduled-tasks', [
            'tasks' => $schedule->tasks(),
            'summary' => $schedule->summary(),
            'heartbeat' => $schedule->heartbeat(),
            'drivers' => $schedule->drivers(),
            'log' => $schedule->tailSchedulerLog(40),
            'commandsAvailable' => \App\Support\RuntimeInspector::canRunCommands(),
        ]);
    }

    /**
     * 手动触发一次任务
     *
     * 只允许执行「已经在调度清单里注册过」的命令，避免把 HTTP 入口变成任意命令执行的口子。
     */
    public function run(Request $request, ScheduleMonitorService $schedule): RedirectResponse
    {
        $command = (string) $request->input('command', '');

        $allowed = array_column($schedule->tasks(), 'command');

        if (! in_array($command, $allowed, true)) {
            return redirect()
                ->route('admin.monitor.scheduled-tasks')
                ->with('error', '只允许手动执行已在调度清单中注册的任务。');
        }

        // worker 不在时投递到队列只会石沉大海（而这恰恰是管理员打开本页的常见场景），
        // 因此探测不到 worker 就退化为同步执行。
        $workers = \App\Support\RuntimeInspector::processCount('queue:work');

        if (($workers ?? 0) > 0) {
            RunScheduledCommandJob::dispatch($command);

            return redirect()
                ->route('admin.monitor.scheduled-tasks')
                ->with('success', "任务 {$command} 已投递到队列，稍后由队列 worker 执行；结果见下方调度日志。");
        }

        $exitCode = \Illuminate\Support\Facades\Artisan::call($command);

        return $exitCode === 0
            ? redirect()->route('admin.monitor.scheduled-tasks')->with('success', "任务 {$command} 已同步执行完成。")
            : redirect()->route('admin.monitor.scheduled-tasks')->with('error', "任务 {$command} 执行异常，退出码 {$exitCode}，请查看日志。");
    }
}
