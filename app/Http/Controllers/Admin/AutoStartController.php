<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AutoStartMonitorService;
use App\Support\RuntimeInspector;
use Illuminate\View\View;

/**
 * 自启动程序监控
 *
 * 清单来自 deploy/ 目录下的部署件（supervisor / systemd / cron / Windows），
 * 状态来自对当前主机的实时探测，两边放一起才能看出"配了但没起"或"起了但改过配置"。
 */
class AutoStartController extends Controller
{
    public function index(AutoStartMonitorService $autoStart): View
    {
        $programs = $autoStart->programs();

        return view('admin.monitor.auto-start', [
            'programs' => $programs,
            'summary' => $autoStart->summary($programs),
            'platform' => RuntimeInspector::os(),
            'commandsAvailable' => RuntimeInspector::canRunCommands(),
        ]);
    }
}
