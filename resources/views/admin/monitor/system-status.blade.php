@extends('layouts.admin')

@section('title', '系统工作状态')

@section('content')
    @include('admin.monitor._tabs', ['active' => 'admin.monitor.system-status'])

    @php
        $env = $status['environment'];
        $runtime = $status['runtime'];
        $db = $status['database'];
        $cache = $status['cache'];
        $queue = $status['queue'];
        $schedule = $status['schedule'];
        $backup = $status['backup'];
        $storage = $status['storage'];
        $logs = $status['logs'];
        $health = $status['health'];
        $alerts = $status['alerts'];
        $counts = $status['counts'];

        $healthBadge = $health['healthy'] === true ? 'success' : ($health['healthy'] === false ? 'danger' : 'secondary');
        $healthText = $health['healthy'] === true ? '健康' : ($health['healthy'] === false ? '异常' : '未检查');
        $mem = $runtime['memory'];
        $diskPercent = $runtime['disk_usage_percent'];
        $statusService = app(\App\Services\SystemStatusService::class);
        $human = fn ($bytes) => $statusService->humanBytes((float) $bytes);
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-heart-pulse"></i>系统工作状态</h2>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.monitor.system-status.run-health') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-stethoscope me-1"></i>立即体检（含告警）
                </button>
            </form>
            <form method="POST" action="{{ route('admin.monitor.system-status.clear-cache') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-eraser me-1"></i>清空体检缓存
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($env['maintenance'])
        <div class="alert alert-warning">
            <i class="fa-solid fa-screwdriver-wrench me-1"></i>
            系统当前处于维护模式（<code>php artisan down</code>），前台已被拦截至维护页。
            注意：调度任务仍在运行，业务数据可能继续变化。
        </div>
    @endif

    {{-- 核心状态 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">体检结果</div>
                    <div class="fs-4 fw-bold"><span class="badge bg-{{ $healthBadge }}">{{ $healthText }}</span></div>
                    <div class="text-muted small mt-2">
                        检查于 {{ $health['checked_at']?->format('m-d H:i:s') ?? '-' }}
                        @if($health['duration_ms'])· 耗时 {{ $health['duration_ms'] }} ms @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">数据库连接</div>
                    <div class="fs-5 fw-bold">
                        @if($db['reachable'])
                            <span class="text-success">正常</span>
                            <div class="text-muted small fw-normal mt-1">{{ $db['driver'] }} · {{ $db['latency_ms'] }} ms</div>
                        @else
                            <span class="text-danger">不可连通</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-2">
                        @if($db['size_mb'] !== null)
                            库体积 {{ $db['size_mb'] }} MB · {{ $db['tables'] }} 张表
                        @else
                            {{ $db['version'] ? Str::limit($db['version'], 40) : '-' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">队列积压</div>
                    <div class="fs-5 fw-bold">
                        {{ $queue['pending'] ?? '-' }}
                        <span class="text-muted fw-normal small">/ 失败 {{ $queue['failed'] ?? '-' }}</span>
                    </div>
                    <div class="text-muted small mt-2">
                        驱动 {{ $queue['driver'] }} · 最旧等待 {{ $queue['lag_minutes'] ?? '-' }} 分钟
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">未恢复告警</div>
                    <div class="fs-3 fw-bold {{ ($alerts['firing_count'] ?? 0) > 0 ? 'text-danger' : '' }}">
                        {{ $alerts['firing_count'] ?? 0 }}
                    </div>
                    <div class="text-muted small mt-2">
                        监控开关 {{ ($alerts['monitoring_enabled'] ?? false) ? '已开启' : '已关闭（维护窗口）' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 运行环境 --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-server me-2"></i>运行环境</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">应用环境</dt>
                        <dd class="col-7">
                            <span class="badge bg-{{ $env['environment'] === 'production' ? 'success' : 'warning text-dark' }}">
                                {{ $env['environment'] }}
                            </span>
                            @if($env['debug'])
                                <span class="badge bg-danger ms-1">APP_DEBUG=true</span>
                            @endif
                        </dd>

                        <dt class="col-5">Laravel / PHP</dt>
                        <dd class="col-7">{{ $env['laravel'] }} / {{ $env['php'] }}</dd>

                        <dt class="col-5">操作系统</dt>
                        <dd class="col-7">{{ $env['os'] }}</dd>

                        <dt class="col-5">Web 容器</dt>
                        <dd class="col-7">{{ $env['server'] ?: '-' }}</dd>

                        <dt class="col-5">时区 / 语言</dt>
                        <dd class="col-7">{{ $env['timezone'] }} / {{ $env['locale'] }}</dd>

                        <dt class="col-5">memory_limit / max_execution_time</dt>
                        <dd class="col-7">{{ $env['memory_limit'] }} / {{ $env['max_execution_time'] }}s</dd>

                        <dt class="col-5">OPcache</dt>
                        <dd class="col-7">
                            @if($env['opcache_enabled'] === true)
                                <span class="badge bg-success">已启用</span>
                            @elseif($env['opcache_enabled'] === false)
                                <span class="badge bg-warning text-dark">未启用</span>
                            @else
                                <span class="text-muted">不可用</span>
                            @endif
                        </dd>

                        <dt class="col-5">访问地址</dt>
                        <dd class="col-7"><code class="small">{{ $env['url'] }}</code></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-gauge-high me-2"></i>机器资源</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">开机时长</dt>
                        <dd class="col-7">{{ $runtime['uptime_human'] ?? '无法获取' }}</dd>

                        <dt class="col-5">CPU 负载（1/5/15 分钟）</dt>
                        <dd class="col-7">
                            {{ $runtime['load']['1min'] ?? '-' }} /
                            {{ $runtime['load']['5min'] ?? '-' }} /
                            {{ $runtime['load']['15min'] ?? '-' }}
                            @if($runtime['cpu_cores'])
                                <div class="text-muted small">{{ $runtime['cpu_cores'] }} 核</div>
                            @endif
                        </dd>

                        <dt class="col-5">物理内存</dt>
                        <dd class="col-7">
                            @if($mem['total_mb'] !== null)
                                {{ round($mem['used_mb']) }} / {{ round($mem['total_mb']) }} MB（{{ $mem['percent'] }}%）
                                <div class="progress mt-1" style="height:.4rem;">
                                    <div class="progress-bar {{ $mem['percent'] >= 85 ? 'bg-danger' : 'bg-primary' }}"
                                         style="width:{{ min(100, $mem['percent']) }}%"></div>
                                </div>
                            @else
                                无法获取
                            @endif
                        </dd>

                        <dt class="col-5">存储占用</dt>
                        <dd class="col-7">
                            @if($diskPercent !== null)
                                {{ $diskPercent }}%
                                <div class="progress mt-1" style="height:.4rem;">
                                    <div class="progress-bar {{ $diskPercent >= 90 ? 'bg-danger' : ($diskPercent >= 80 ? 'bg-warning' : 'bg-success') }}"
                                         style="width:{{ min(100, $diskPercent) }}%"></div>
                                </div>
                            @else
                                无法获取
                            @endif
                        </dd>

                        <dt class="col-5">缓存驱动</dt>
                        <dd class="col-7">
                            <code>{{ $cache['store'] }}</code>
                            @if($cache['writable'])
                                <span class="badge bg-success ms-1">可读写</span>
                                <div class="text-muted small">{{ $cache['latency_ms'] }} ms</div>
                            @else
                                <span class="badge bg-danger ms-1">不可写</span>
                            @endif
                            @if($cache['store'] === 'array')
                                <div class="text-danger small mt-1">互斥锁失效，withoutOverlapping 不起作用</div>
                            @endif
                        </dd>

                        <dt class="col-5">storage / cache 可写</dt>
                        <dd class="col-7">
                            @if($storage['writable'])
                                <span class="badge bg-success">是</span>
                            @else
                                <span class="badge bg-danger">否</span>
                                <div class="text-danger small">请检查 storage 与 bootstrap/cache 目录权限</div>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    {{-- 队列与调度联动 --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-layer-group me-2"></i>队列与调度</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <tbody class="small">
                        <tr>
                            <td style="width:11rem">队列处理中 / 延迟</td>
                            <td>{{ $queue['processing'] ?? '-' }} / {{ $queue['delayed'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>worker 进程</td>
                            <td>
                                @if(! $queue['workers']['detected'])
                                    <span class="badge bg-secondary">无法检测</span>
                                @elseif($queue['workers']['count'] > 0)
                                    <span class="badge bg-success">{{ $queue['workers']['count'] }} 个运行中</span>
                                @else
                                    <span class="badge bg-danger">未检测到</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>已注册定时任务</td>
                            <td>
                                <a href="{{ route('admin.monitor.scheduled-tasks') }}">{{ $schedule['total'] }} 个</a>
                                ，其中 {{ $schedule['never_ran'] }} 个从未执行过
                            </td>
                        </tr>
                        <tr>
                            <td>调度心跳</td>
                            <td>
                                @if(($schedule['heartbeat']['healthy'] ?? null) === true)
                                    <span class="badge bg-success">正常</span>
                                @elseif(($schedule['heartbeat']['healthy'] ?? null) === false)
                                    <span class="badge bg-danger">已停止</span>
                                @else
                                    <span class="badge bg-secondary">无心跳</span>
                                @endif
                                <span class="text-muted ms-2">
                                    {{ $schedule['heartbeat']['beat'] ? $schedule['heartbeat']['minutes_ago'] . ' 分钟前' : '尚未产生' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>调度驱动器</td>
                            <td>
                                @foreach($schedule['drivers'] as $driver)
                                    <div>
                                        {{ $driver['name'] }}：
                                        @if(! $driver['detected'])
                                            <span class="badge bg-secondary">无法检测</span>
                                        @elseif($driver['running'])
                                            <span class="badge bg-success">已就绪</span>
                                        @else
                                            <span class="badge bg-danger">未配置</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-database me-2"></i>数据库与备份</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <tbody class="small">
                        <tr>
                            <td style="width:9rem">驱动 / 主机</td>
                            <td><code>{{ $db['driver'] }}</code> @ {{ $db['host'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>版本</td>
                            <td class="small">{{ $db['version'] ? Str::limit($db['version'], 60) : '-' }}</td>
                        </tr>
                        <tr>
                            <td>连接数</td>
                            <td>
                                @if($db['connections_max'])
                                    {{ $db['connections_used'] }} / {{ $db['connections_max'] }}
                                    （{{ round($db['connections_used'] / max(1, $db['connections_max']) * 100) }}%）
                                @else
                                    {{ $db['tables'] ?? '-' }} 张表
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>最近成功备份</td>
                            <td>
                                @if(($backup['last_success'] ?? null))
                                    {{ $backup['last_success']->finished_at?->diffForHumans() }}
                                    <div class="text-muted small">{{ $backup['last_success']->sizeForHumans() }} · {{ $backup['last_success']->filename }}</div>
                                @else
                                    <span class="text-danger">无成功记录</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>备份时效</td>
                            <td>
                                @if(($backup['age_hours'] ?? null) !== null)
                                    {{ $backup['age_hours'] }} 小时前（阈值 {{ config('monitoring.thresholds.backup_max_age_hours') }} 小时）
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>用户 / 角色 / 待审批</td>
                            <td>{{ $counts['users'] }} / {{ $counts['roles'] }} / {{ $counts['pending_approvals'] }}</td>
                        </tr>
                        <tr>
                            <td>24 小时操作日志</td>
                            <td>{{ $counts['audit_logs_24h'] }} 条</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 健康检查明细 --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa-solid fa-clipboard-check me-2"></i>体检项明细</span>
            <span class="text-muted small">由 <code>php artisan erp:health</code> 产生，后台每分钟自动巡检</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:5rem">状态</th>
                        <th style="width:5rem">分组</th>
                        <th>检查项</th>
                        <th>实测值</th>
                        <th style="width:12rem">阈值 / 说明</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($health['checks'] as $check)
                        <tr>
                            <td>
                                <span class="badge bg-{{ $check['status'] === 'ok' ? 'success' : ($check['status'] === 'warn' ? 'warning text-dark' : 'danger') }}">
                                    {{ $check['status'] === 'ok' ? '正常' : ($check['status'] === 'warn' ? '警告' : '异常') }}
                                </span>
                            </td>
                            <td>{{ $check['group'] }}</td>
                            <td><code>{{ $check['name'] }}</code></td>
                            <td>{{ is_scalar($check['value']) ? $check['value'] : json_encode($check['value'], JSON_UNESCAPED_UNICODE) }}</td>
                            <td class="text-muted small">{{ $check['threshold'] ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">暂无体检结果，请点击「立即体检」</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 未恢复告警 --}}
    @if(($alerts['firing_count'] ?? 0) > 0)
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fa-solid fa-bell me-2"></i>未恢复告警
                <span class="badge bg-light text-danger ms-1">{{ $alerts['firing_count'] }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <tbody class="small">
                    @foreach($alerts['firing'] as $alert)
                        <tr>
                            <td style="width:6rem">
                                <span class="badge bg-{{ $alert->level === 'critical' ? 'danger' : 'warning text-dark' }}">
                                    {{ $alert->levelLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $alert->title }}</div>
                                <div class="text-muted">{{ Str::limit($alert->message, 160) }}</div>
                            </td>
                            <td style="width:12rem">{{ $alert->last_triggered_at?->format('m-d H:i:s') }}</td>
                            <td style="width:5rem">{{ $alert->occurrences }} 次</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- 磁盘与日志 --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-hard-drive me-2"></i>目录空间</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr><th>目录</th><th style="width:7rem">可用</th><th style="width:7rem">总量</th><th style="width:5rem">可用%</th></tr>
                        </thead>
                        <tbody class="small">
                        @foreach($storage['paths'] as $item)
                            <tr>
                                <td>
                                    {{ $item['label'] }}
                                    <div class="text-muted" style="font-size:.72rem;"><code>{{ $item['path'] }}</code></div>
                                </td>
                                <td>{{ $item['free'] !== null ? $human($item['free']) : '-' }}</td>
                                <td>{{ $item['total'] !== null ? $human($item['total']) : '-' }}</td>
                                <td>
                                    @if($item['percent_free'] !== null)
                                        <span class="{{ $item['percent_free'] <= 10 ? 'text-danger' : ($item['percent_free'] <= 20 ? 'text-warning' : '') }}">
                                            {{ $item['percent_free'] }}%
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fa-regular fa-file-lines me-2"></i>日志文件
                    <span class="text-muted small ms-2">合计 {{ $human($logs['total_size']) }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                        <tr><th>文件</th><th style="width:6rem">大小</th><th style="width:11rem">最后写入</th></tr>
                        </thead>
                        <tbody class="small">
                        @forelse($logs['files'] as $file)
                            <tr>
                                <td><code>{{ $file['name'] }}</code></td>
                                <td>{{ $file['size_human'] }}</td>
                                <td>{{ $file['modified_at']->format('m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">无日志文件</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 最近错误 --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa-solid fa-bug me-2"></i>最近错误日志</span>
            <span class="text-muted small">
                来源 {{ $logs['source_file'] ? basename($logs['source_file']) : '-' }} · {{ $logs['error_window'] }}
            </span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                <tr><th style="width:7rem">级别</th><th style="width:11rem">时间</th><th>摘要</th></tr>
                </thead>
                <tbody>
                @forelse($logs['recent_errors'] as $error)
                    <tr>
                        <td><span class="badge bg-{{ $error['level'] === 'ERROR' ? 'warning text-dark' : 'danger' }}">{{ $error['level'] }}</span></td>
                        <td class="small">{{ $error['at'] }}</td>
                        <td class="small text-danger"><code style="white-space:pre-wrap;">{{ Str::limit($error['line'], 240) }}</code></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">最近 300 行日志中没有 ERROR 级别记录</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted small">
            快照生成于 {{ $status['generated_at']->format('Y-m-d H:i:s') }}。
            需要完整堆栈请查看 <code>storage/logs/laravel-*.log</code> 或执行 <code>php artisan pail</code>。
        </div>
    </div>
@endsection
