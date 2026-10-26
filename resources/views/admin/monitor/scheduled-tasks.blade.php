@extends('layouts.admin')

@section('title', '定时任务')

@section('content')
    @include('admin.monitor._tabs', ['active' => 'admin.monitor.scheduled-tasks'])

    @php
        $beatOk = $heartbeat['healthy'] ?? null;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-regular fa-clock"></i>定时任务</h2>
        <div class="text-muted small">
            命令清单来自 <code>routes/console.php</code>，执行台账来自调度事件
            <code>ScheduledTaskFinished</code>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 概览 --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">已注册任务</div>
                    <div class="fs-3 fw-bold">{{ $summary['total'] }}</div>
                    <div class="text-muted small mt-1">其中 {{ $summary['never_ran'] }} 个尚无执行记录</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">调度心跳</div>
                    <div class="fs-5 fw-bold">
                        @if($beatOk === true)
                            <span class="text-success">正常</span>
                        @elseif($beatOk === false)
                            <span class="text-danger">已停止</span>
                        @else
                            <span class="text-secondary">无心跳</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">
                        @if($heartbeat['beat'])
                            {{ \Carbon\Carbon::parse($heartbeat['beat'])->diffForHumans() }}
                            · {{ $heartbeat['minutes_ago'] }} 分钟前
                        @else
                            尚未产生心跳，请确认 cron / 常驻进程
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">最近一次任务执行</div>
                    <div class="fs-6 fw-bold">
                        @if($summary['last_run_at'])
                            {{ \Carbon\Carbon::parse($summary['last_run_at'])->diffForHumans() }}
                        @else
                            <span class="text-danger">从未执行</span>
                        @endif
                    </div>
                    <div class="text-muted small mt-1">{{ $summary['last_run_at'] ?? '-' }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">30 分钟内待触发</div>
                    <div class="fs-3 fw-bold">{{ count($summary['due_soon']) }}</div>
                    <div class="text-muted small mt-1">
                        @forelse(array_slice($summary['due_soon'], 0, 3) as $task)
                            <div class="text-truncate"><code>{{ $task['command'] }}</code></div>
                        @empty
                            暂无
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 调度驱动器 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-plug me-2"></i>调度驱动器（谁在拉起这些任务）</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>载体</th>
                        <th style="width:8rem">类型</th>
                        <th>命令</th>
                        <th style="width:8rem">状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($drivers as $driver)
                        <tr>
                            <td class="fw-semibold">{{ $driver['name'] }}</td>
                            <td><span class="badge bg-light text-dark">{{ $driver['type'] }}</span></td>
                            <td><code class="small">{{ $driver['command'] }}</code></td>
                            <td>
                                @if(! $driver['detected'])
                                    <span class="badge bg-secondary">无法检测</span>
                                @elseif($driver['running'])
                                    <span class="badge bg-success">已就绪</span>
                                @else
                                    <span class="badge bg-danger">未配置</span>
                                @endif
                                <div class="text-muted small mt-1">{{ $driver['state'] }}</div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            @if(! $commandsAvailable)
                当前 Web 进程禁用了命令执行（<code>proc_open</code>），无法在线检测进程与计划任务，请以「调度心跳」和下方「执行台账」为准。
            @else
                两种载体二选一即可：cron/计划任务每分钟拉起 <code>schedule:run</code>，或用守护进程常驻 <code>schedule:work</code>。
            @endif
        </div>
    </div>

    {{-- 任务清单 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-list-check me-2"></i>任务清单与执行台账</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>任务命令</th>
                        <th style="width:9rem">执行频率</th>
                        <th style="width:11rem">下次触发</th>
                        <th style="width:11rem">上次执行</th>
                        <th style="width:7rem">结果</th>
                        <th style="width:9rem">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tasks as $task)
                        @php
                            $last = $task['last'];
                            $statusBadge = match ($last['status'] ?? null) {
                                'success' => 'success',
                                'failed' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold"><code>{{ $task['command'] }}</code></div>
                                @if($task['description'] && $task['description'] !== $task['command'])
                                    <div class="text-muted small">{{ Str::limit($task['description'], 80) }}</div>
                                @endif
                                <div class="mt-1">
                                    <span class="badge bg-light text-dark small">{{ $task['expression'] }}</span>
                                    @if($task['background'])
                                        <span class="badge bg-info text-dark small">后台执行</span>
                                    @endif
                                    @if($task['output_file'])
                                        <span class="badge bg-light text-dark small" title="{{ $task['output_file'] }}">
                                            <i class="fa-regular fa-file-lines"></i> 输出落盘
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $task['schedule_hint'] }}</td>
                            <td>
                                @if($task['next_run_at'])
                                    {{ $task['next_run_at']->format('m-d H:i:s') }}
                                    <div class="text-muted small">{{ $task['next_run_at']->diffForHumans() }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($last)
                                    {{ \Carbon\Carbon::parse($last['ran_at'])->format('m-d H:i:s') }}
                                    <div class="text-muted small">
                                        耗时 {{ $last['duration_ms'] !== null ? $last['duration_ms'] . ' ms' : '-' }}
                                    </div>
                                @else
                                    <span class="text-muted small">尚无记录</span>
                                @endif
                            </td>
                            <td>
                                @if($last)
                                    <span class="badge bg-{{ $statusBadge }}">
                                        {{ $last['status'] === 'success' ? '成功' : ($last['status'] === 'failed' ? '失败' : $last['status']) }}
                                    </span>
                                    @if(($last['exit_code'] ?? null) !== null)
                                        <div class="text-muted small">退出码 {{ $last['exit_code'] }}</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.monitor.scheduled-tasks.run') }}">
                                    @csrf
                                    <input type="hidden" name="command" value="{{ $task['command'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-play me-1"></i>立即执行
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @if($last && ($last['exception'] ?? null))
                            <tr class="table-danger">
                                <td colspan="6" class="small text-danger">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    最近一次失败：{{ Str::limit($last['exception'], 400) }}
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">没有注册任何定时任务</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            「立即执行」在检测到 worker 存活时投递到队列异步执行，否则在请求内同步执行；
            只允许执行清单中已注册的命令。
        </div>
    </div>

    {{-- 调度日志 --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fa-regular fa-file-lines me-2"></i>调度日志（scheduler.log）</span>
            @if($log['exists'])
                <span class="text-muted small">
                    {{ $log['size'] }} · 最后更新 {{ $log['modified_at']?->format('Y-m-d H:i:s') }}
                </span>
            @endif
        </div>
        <div class="card-body">
            @if($log['exists'] && $log['lines'] !== [])
                <pre class="bg-light border rounded p-3 mb-0 small" style="max-height:22rem;overflow:auto;">{{ implode("\n", $log['lines']) }}</pre>
            @elseif($log['exists'])
                <div class="text-muted">日志文件为空（多数调度命令未配置输出落盘）。</div>
            @else
                <div class="text-muted">
                    尚未生成 <code>{{ $log['path'] }}</code>。
                    仅当 route 中用 <code>appendOutputTo()</code> 指定落盘路径的任务才会写入该文件。
                </div>
            @endif
        </div>
    </div>
@endsection
