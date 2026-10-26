<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\DatabaseBackup;
use App\Models\System\SystemAlert;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * 系统监控面板
 *
 * 对齐主流 ERP 运维看板：一屏看到「系统是否健康 + 队列是否消费 + 备份是否成功 + 告警是否未处理」。
 */
class SystemHealthController extends Controller
{
    public function index(): View
    {
        return view('admin.system-health.index', [
            'health' => $this->lastHealth(),
            'heartbeat' => Cache::get('erp:scheduler:heartbeat'),
            'alerts' => app(AlertService::class)->firing(),
            'resolvedAlerts' => SystemAlert::where('status', SystemAlert::STATUS_RESOLVED)
                ->latest('resolved_at')->limit(10)->get(),
            'queue' => $this->queueStats(),
            'backups' => DatabaseBackup::latest()->limit(10)->get(),
            'schedule' => $this->scheduleList(),
        ]);
    }

    /**
     * 手动触发一次健康检查
     */
    public function run(): RedirectResponse
    {
        Artisan::call('erp:health');

        return redirect()
            ->route('admin.system-health')
            ->with('success', '健康检查已执行，结果见下方。');
    }

    /**
     * 手动确认/关闭一条告警
     */
    public function resolveAlert(Request $request, SystemAlert $alert): RedirectResponse
    {
        app(AlertService::class)->resolve($alert->fingerprint, '由 ' . $request->user()?->name . ' 在系统监控面板手动确认恢复。');

        return redirect()->route('admin.system-health')->with('success', '告警已标记为已恢复。');
    }

    protected function lastHealth(): ?array
    {
        $cached = Cache::get('erp:health:last_result');

        if ($cached) {
            return $cached;
        }

        // 面板首次打开时兜底跑一次（不发送告警，避免刷新页面触发通知）
        Artisan::call('erp:health', ['--no-alert' => true]);

        return Cache::get('erp:health:last_result');
    }

    protected function queueStats(): array
    {
        if (config('queue.default') !== 'database') {
            return ['driver' => config('queue.default'), 'pending' => null, 'failed' => null];
        }

        return [
            'driver' => 'database',
            'pending' => DB::table(config('queue.connections.database.table', 'jobs'))->count(),
            'failed' => DB::table(config('queue.failed.table', 'failed_jobs'))->count(),
            'latest_failures' => DB::table(config('queue.failed.table', 'failed_jobs'))
                ->latest('failed_at')->limit(5)->get(),
        ];
    }

    protected function scheduleList(): string
    {
        Artisan::call('schedule:list');

        return trim(Artisan::output());
    }
}
