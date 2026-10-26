@extends('layouts.admin')

@section('title', '自启动程序')

@section('content')
    @include('admin.monitor._tabs', ['active' => 'admin.monitor.auto-start'])

    @php
        $platformLabel = match ($platform) {
            'windows' => 'Windows',
            'linux' => 'Linux',
            'darwin' => 'macOS',
            default => '未知平台',
        };
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-rocket"></i>自启动程序</h2>
        <div class="text-muted small">
            清单自动解析自 <code>deploy/</code> 部署件 · 当前平台 <strong>{{ $platformLabel }}</strong>
        </div>
    </div>

    {{-- 概览 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">已定义自启动项</div>
                    <div class="fs-3 fw-bold">{{ $summary['total'] }}</div>
                    <div class="text-muted small mt-1">含 supervisor / systemd / cron / Windows</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">运行中</div>
                    <div class="fs-3 fw-bold text-success">{{ $summary['running'] }}</div>
                    <div class="text-muted small mt-1">已在本机实际探测到</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">未运行 / 未安装</div>
                    <div class="fs-3 fw-bold text-danger">{{ $summary['stopped'] + $summary['not_installed'] }}</div>
                    <div class="text-muted small mt-1">需要处理，见下方清单</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">无法检测</div>
                    <div class="fs-3 fw-bold text-secondary">{{ $summary['unknown'] }}</div>
                    <div class="text-muted small mt-1">权限不足或命令不存在</div>
                </div>
            </div>
        </div>
    </div>

    @if(! $commandsAvailable)
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            当前 Web 进程禁用了命令执行（<code>proc_open</code> 被 <code>disable_functions</code> 屏蔽），
            所有自启动项都无法在线检测，请以「定时任务」页的调度心跳与「队列」页的任务等待时长为准。
        </div>
    @endif

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list-check me-2"></i>自启动项清单</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>名称</th>
                        <th style="width:11rem">类型</th>
                        <th>启动命令</th>
                        <th style="width:7rem">开机自启</th>
                        <th style="width:7rem">崩溃自愈</th>
                        <th style="width:8rem">运行状态</th>
                        <th style="width:22rem">处理建议</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $service = app(\App\Services\AutoStartMonitorService::class); @endphp
                    @forelse($programs as $program)
                        @php $badge = $service->statusBadge($program['status']); @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $program['name'] }}</div>
                                @if(! empty($program['config']))
                                    <div class="text-muted small">
                                        <i class="fa-regular fa-file-code me-1"></i><code>{{ $program['config'] }}</code>
                                    </div>
                                @endif
                                @if(! empty($program['config_exists']) && $program['config_exists'] === false)
                                    <span class="badge bg-danger small">配置文件缺失</span>
                                @endif
                                @if(! empty($program['trigger']))
                                    <div class="text-muted small">触发器：{{ $program['trigger'] }}</div>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $program['type'] }}</span></td>
                            <td>
                                <code class="small">{{ Str::limit($program['command'] ?? '-', 90) }}</code>
                                @if(! empty($program['processes']) && $program['processes'] > 1)
                                    <div class="text-muted small">{{ $program['processes'] }} 个进程</div>
                                @endif
                                @if(! empty($program['description']))
                                    <div class="text-muted small">{{ $program['description'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if($program['autostart'])
                                    <i class="fa-solid fa-check text-success"></i>
                                @else
                                    <i class="fa-solid fa-xmark text-muted"></i>
                                @endif
                            </td>
                            <td>
                                @if($program['autorestart'])
                                    <i class="fa-solid fa-check text-success"></i>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $badge }}">{{ $service->statusLabel($program['status']) }}</span>
                            </td>
                            <td class="small text-muted">{{ $program['hint'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">deploy/ 目录下没有解析到任何部署件</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            最后两行「进程探测」不依赖任何守护框架，是判断「任务到底有没有真的跑」的最终依据：
            Linux 用 <code>pgrep -f</code>，Windows 用 PowerShell 读取 <code>Win32_Process</code>，
            结果缓存 20 秒，避免频繁进程枚举。
        </div>
    </div>
@endsection
