@extends('layouts.admin')

@section('title', '队列监控')

@section('content')
    @include('admin.monitor._tabs', ['active' => 'admin.monitor.queue'])

    @php
        $pendingLevel = ! is_int($summary['pending'] ?? null)
            ? 'secondary'
            : ($summary['pending'] >= $thresholds['pending_critical'] ? 'danger'
                : ($summary['pending'] >= $thresholds['pending_warning'] ? 'warning' : 'success'));
        $lag = $summary['lag_minutes'] ?? null;
        $lagBad = is_int($lag) && $lag >= $thresholds['lag_minutes'];
        $maxCount = max(1, (int) max(array_column($trend, 'count')));
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-layer-group"></i>队列监控</h2>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.monitor.queue.retry-all') }}"
                  onsubmit="return confirm('重试全部失败任务，确认继续？');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning" @disabled(($summary['failed'] ?? 0) === 0)>
                    <i class="fa-solid fa-rotate-right me-1"></i>重试全部失败
                </button>
            </form>
            <form method="POST" action="{{ route('admin.monitor.queue.restart') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-power-off me-1"></i>优雅重启 worker
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
                    <div class="text-muted small mb-1">待处理（pending）</div>
                    <div class="fs-3 fw-bold text-{{ $pendingLevel }}">
                        {{ $summary['pending'] ?? '-' }}
                    </div>
                    <div class="text-muted small mt-1">
                        告警 {{ $thresholds['pending_warning'] }} / 严重 {{ $thresholds['pending_critical'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">处理中（reserved）</div>
                    <div class="fs-3 fw-bold">{{ $summary['processing'] ?? '-' }}</div>
                    <div class="text-muted small mt-1">已被 worker 领取、尚未完成</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">最旧任务等待时长</div>
                    <div class="fs-3 fw-bold {{ $lagBad ? 'text-danger' : '' }}">
                        {{ $lag === null ? '-' : $lag . ' 分钟' }}
                    </div>
                    <div class="text-muted small mt-1">
                        @if($lagBad)
                            超过 {{ $thresholds['lag_minutes'] }} 分钟未消费，worker 可能已停
                        @else
                            阈值 {{ $thresholds['lag_minutes'] }} 分钟
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">失败任务</div>
                    <div class="fs-3 fw-bold {{ ($failureStats['total'] ?? 0) > 0 ? 'text-danger' : '' }}">
                        {{ $failureStats['total'] ?? '-' }}
                    </div>
                    <div class="text-muted small mt-1">
                        近 1 小时 {{ $failureStats['last_1h'] ?? 0 }} · 近 24 小时 {{ $failureStats['last_24h'] ?? 0 }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fa-solid fa-chart-column me-2"></i>近 12 小时入队趋势
                    <span class="text-muted small ms-2">队列里现存任务的时间分布</span>
                </div>
                <div class="card-body">
                    @if($trend === [])
                        <div class="text-muted">近 12 小时没有任务入队。</div>
                    @else
                        <div class="d-flex align-items-end gap-1" style="height:9rem;">
                            @foreach($trend as $point)
                                @php $height = (int) round($point['count'] / $maxCount * 100); @endphp
                                <div class="flex-fill text-center" title="{{ $point['hour'] }}：{{ $point['count'] }} 条">
                                    <div class="mx-auto rounded-top {{ $point['count'] > $maxCount * 0.6 ? 'bg-danger' : 'bg-primary' }}"
                                         style="width:80%; min-height:2px; height:{{ max(2, $height) }}%;"></div>
                                    <div class="text-muted mt-1" style="font-size:.7rem;">{{ Str::before($point['hour'], ':') }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-nodes me-2"></i>驱动与 worker</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">连接驱动</dt>
                        <dd class="col-7"><code>{{ $summary['driver'] }}</code></dd>

                        <dt class="col-5">延迟任务</dt>
                        <dd class="col-7">{{ $summary['delayed'] ?? '-' }}</dd>

                        <dt class="col-5">worker 进程</dt>
                        <dd class="col-7">
                            @if(! $workers['detected'])
                                <span class="badge bg-secondary">无法检测</span>
                            @elseif($workers['count'] > 0)
                                <span class="badge bg-success">{{ $workers['count'] }} 个运行中</span>
                            @else
                                <span class="badge bg-danger">未检测到</span>
                            @endif
                        </dd>

                        <dt class="col-5">批次任务</dt>
                        <dd class="col-7">
                            @if(($batches['available'] ?? false))
                                共 {{ $batches['total'] }} · 进行中 {{ $batches['pending'] }} · 已完成 {{ $batches['finished'] }}
                            @else
                                <span class="text-muted">批次表不可用</span>
                            @endif
                        </dd>
                    </dl>

                    @if(! ($summary['inspectable'] ?? false))
                        <div class="alert alert-info mt-3 mb-0 small">
                            当前队列驱动为 <code>{{ $summary['driver'] }}</code>，无法从 <code>jobs</code> 表统计积压。
                            生产环境请使用 <code>database</code> 或 <code>redis</code> 驱动，
                            <code>sync</code> 会让任务在请求内同步执行（失去异步意义且无法重试）。
                        </div>
                    @endif

                    @if(! $workers['detected'])
                        <div class="alert alert-warning mt-3 mb-0 small">
                            无法枚举 worker 进程（Web 进程无权限或命令不可用）。
                            请以「最旧任务等待时长」判断 worker 是否存活。
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 按队列 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-table-list me-2"></i>按队列维度</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>队列</th>
                        <th style="width:6rem">待处理</th>
                        <th style="width:6rem">处理中</th>
                        <th style="width:6rem">延迟</th>
                        <th style="width:9rem">最旧任务</th>
                        <th style="width:6rem">最大尝试</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($byQueue as $row)
                        <tr>
                            <td><code>{{ $row->queue }}</code></td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $row->processing }}</td>
                            <td>{{ $row->delayed }}</td>
                            <td class="small">
                                @if($row->oldest_at)
                                    {{ \Carbon\Carbon::createFromTimestamp($row->oldest_at)->diffForHumans() }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $row->max_attempts }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">
                            @if(($summary['inspectable'] ?? false))
                                队列为空，没有待处理任务
                            @else
                                当前驱动不支持队列透视
                            @endif
                        </td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 失败任务 --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>失败任务
                <span class="badge bg-danger ms-1">{{ $failureStats['total'] ?? 0 }}</span>
            </span>
            <form method="POST" action="{{ route('admin.monitor.queue.flush') }}"
                  onsubmit="return confirm('清空全部失败任务记录，确认继续？');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger" @disabled(($failureStats['total'] ?? 0) === 0)>
                    <i class="fa-solid fa-trash me-1"></i>清空失败记录
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:6rem">队列</th>
                        <th>异常摘要</th>
                        <th style="width:11rem">失败时间</th>
                        <th style="width:11rem">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($failures as $failure)
                        <tr>
                            <td><code class="small">{{ $failure->queue }}</code></td>
                            <td class="small text-danger">
                                {{ Str::limit($failure->exception, 220) }}
                                <div class="text-muted mt-1">uuid <code>{{ Str::limit($failure->uuid, 20) }}</code></div>
                            </td>
                            <td class="small">{{ \Carbon\Carbon::parse($failure->failed_at)->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('admin.monitor.queue.retry', $failure->uuid) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">重试</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.monitor.queue.forget', $failure->uuid) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger">删除</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">没有失败任务</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            重试前请先修复根因（查看完整堆栈：<code>php artisan queue:failed</code>），
            反复重试失败任务会快速抬高 attempts 并最终再次进入失败队列。
        </div>
    </div>
@endsection
