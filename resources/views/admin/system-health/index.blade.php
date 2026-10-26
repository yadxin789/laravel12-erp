@extends('layouts.admin')

@section('title', '系统监控')

@section('content')
    @php
        $healthy = $health['healthy'] ?? null;
        $statusBadge = $healthy === true ? 'success' : ($healthy === false ? 'danger' : 'secondary');
        $statusText = $healthy === true ? '健康' : ($healthy === false ? '异常' : '未检查');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-heart-pulse"></i>系统监控</h2>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.system-health.run') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-rotate me-1"></i>立即检查
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 概览 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">整体状态</div>
                    <div class="fs-4 fw-bold">
                        <span class="badge bg-{{ $statusBadge }}">{{ $statusText }}</span>
                    </div>
                    <div class="text-muted small mt-2">
                        检查时间：{{ $health['checked_at'] ? \Carbon\Carbon::parse($health['checked_at'])->format('Y-m-d H:i:s') : '-' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">调度心跳</div>
                    <div class="fs-5 fw-bold">
                        @if($heartbeat)
                            {{ \Carbon\Carbon::parse($heartbeat)->diffForHumans() }}
                        @else
                            <span class="text-danger">无心跳</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-2">超过 10 分钟未更新将自动告警</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">队列积压 / 失败</div>
                    <div class="fs-5 fw-bold">
                        {{ $queue['pending'] ?? '-' }} / <span class="{{ ($queue['failed'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ $queue['failed'] ?? '-' }}</span>
                    </div>
                    <div class="text-muted small mt-2">驱动：{{ $queue['driver'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">最近备份</div>
                    <div class="fs-6 fw-bold">
                        @php $last = $backups->firstWhere('status', 'success'); @endphp
                        @if($last)
                            {{ $last->finished_at?->diffForHumans() }}
                        @else
                            <span class="text-danger">无成功备份</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-2">{{ $last?->sizeForHumans() ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- 检查项 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-list-check me-2"></i>检查项明细</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:4rem">状态</th>
                        <th style="width:6rem">分组</th>
                        <th>检查项</th>
                        <th>实测值</th>
                        <th>阈值/说明</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($health['checks'] ?? [] as $check)
                        <tr>
                            <td>
                                @if($check['status'] === 'ok')
                                    <span class="badge bg-success">正常</span>
                                @elseif($check['status'] === 'warn')
                                    <span class="badge bg-warning text-dark">警告</span>
                                @else
                                    <span class="badge bg-danger">异常</span>
                                @endif
                            </td>
                            <td>{{ $check['group'] }}</td>
                            <td><code>{{ $check['name'] }}</code></td>
                            <td>{{ is_scalar($check['value']) ? $check['value'] : json_encode($check['value'], JSON_UNESCAPED_UNICODE) }}</td>
                            <td class="text-muted">{{ $check['threshold'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">暂无检查结果，请点击「立即检查」</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 当前告警 --}}
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa-solid fa-bell me-2"></i>当前告警
            <span class="badge bg-danger ms-1">{{ $alerts->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:6rem">级别</th>
                        <th>标题</th>
                        <th style="width:8rem">来源</th>
                        <th style="width:6rem">次数</th>
                        <th style="width:12rem">最近触发</th>
                        <th style="width:8rem">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($alerts as $alert)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $alert->level === 'critical' ? 'danger' : 'warning' }}">{{ $alert->levelLabel() }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $alert->title }}</div>
                                <div class="text-muted small">{{ Str::limit($alert->message, 120) }}</div>
                            </td>
                            <td><code>{{ $alert->source }}</code></td>
                            <td>{{ $alert->occurrences }}</td>
                            <td>{{ $alert->last_triggered_at?->format('m-d H:i:s') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.system-health.alerts.resolve', $alert) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">确认恢复</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">当前没有未恢复的告警</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- 备份台账 --}}
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-database me-2"></i>备份台账</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr><th>文件名</th><th style="width:5rem">状态</th><th style="width:6rem">大小</th><th style="width:10rem">完成时间</th></tr>
                        </thead>
                        <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td class="small">{{ $backup->filename }}</td>
                                <td>
                                    <span class="badge bg-{{ $backup->isSuccess() ? 'success' : ($backup->status === 'failed' ? 'danger' : 'secondary') }}">
                                        {{ $backup->status }}
                                    </span>
                                </td>
                                <td class="small">{{ $backup->sizeForHumans() }}</td>
                                <td class="small">{{ $backup->finished_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">尚无备份记录</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 最近失败任务 --}}
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-triangle-exclamation me-2"></i>最近失败队列任务</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr><th>队列</th><th>失败时间</th></tr>
                        </thead>
                        <tbody>
                        @forelse($queue['latest_failures'] ?? [] as $failure)
                            <tr>
                                <td class="small">{{ $failure->queue }}</td>
                                <td class="small">{{ \Carbon\Carbon::parse($failure->failed_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-4">没有失败任务</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 调度清单 --}}
    <div class="card">
        <div class="card-header"><i class="fa-regular fa-clock me-2"></i>定时任务清单</div>
        <div class="card-body">
            <pre class="bg-light border rounded p-3 mb-0" style="max-height:22rem;overflow:auto;">{{ $schedule }}</pre>
            <div class="text-muted small mt-2">
                调度执行依赖 crontab（Linux）或计划任务（Windows），清单存在不代表正在运行 —— 请以「调度心跳」为准。
            </div>
        </div>
    </div>
@endsection
