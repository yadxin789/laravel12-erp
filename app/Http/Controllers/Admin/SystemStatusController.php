<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * 系统工作状态（运维看板）
 *
 * 一屏回答：这个实例在哪台机器、跑到什么版本、连接资源够不够、
 * 磁盘够不够、队列与调度是否正常、最近有没有 ERROR。
 */
class SystemStatusController extends Controller
{
    public function index(SystemStatusService $status): View
    {
        return view('admin.monitor.system-status', [
            'status' => $status->snapshot(),
        ]);
    }

    /**
     * 立即跑一次健康检查
     *
     * 区别于后台每分钟的自动巡检：这里会连同告警一起触发，
     * 便于运维人员验证告警通道是否真的能通知出来。
     */
    public function runHealth(): RedirectResponse
    {
        Artisan::call('erp:health');

        return redirect()
            ->route('admin.monitor.system-status')
            ->with('success', '健康检查已执行（含告警通知），结果见下方检查项明细。');
    }

    /**
     * 清空健康检查缓存结果，强制下一次打开页面时重新体检
     */
    public function clearCache(): RedirectResponse
    {
        cache()->forget('erp:health:last_result');

        return redirect()
            ->route('admin.monitor.system-status')
            ->with('success', '已清空缓存的体检结果，页面会自动重新体检。');
    }
}
