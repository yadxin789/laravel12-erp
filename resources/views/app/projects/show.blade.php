@extends('layouts.business')

@section('title', '项目详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>{{ $project->name }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen me-1"></i>编辑
            </a>
            <a href="{{ route('app.projects.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>项目信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">状态</div>
                            <div class="fw-semibold">{{ $statusLabels[$project->status] ?? $project->status }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">负责人</div>
                            <div class="fw-semibold">{{ $project->manager?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">客户</div>
                            <div class="fw-semibold">{{ $project->customer?->name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">预算</div>
                            <div class="fw-semibold">{{ number_format((float) $project->budget, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">周期</div>
                            <div class="fw-semibold small">{{ $project->start_date ?: '—' }} ~ {{ $project->end_date ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">已投入工时</div>
                            <div class="fw-semibold">{{ $project->total_hours }} 小时</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">项目说明</div>
                            <div class="fw-semibold">{{ $project->description ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-list-check me-2"></i>添加任务</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('app.projects.tasks.store', $project) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12 col-md-5">
                                <input type="text" name="name" class="form-control" placeholder="任务名称" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="status" class="form-select">
                                    @foreach($taskStatusLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <select name="priority" class="form-select">
                                    @foreach($priorityLabels as $key => $label)
                                        <option value="{{ $key }}" {{ $key == 2 ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="assignee_id" class="form-select">
                                    <option value="">未指派</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="date" name="due_date" class="form-control" title="截止日期">
                            </div>
                            <div class="col-12 col-md-6 d-grid">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>添加任务</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-tasks me-2"></i>任务列表（{{ $project->tasks->count() }}）</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>任务</th>
                            <th class="text-center">优先级</th>
                            <th>负责人</th>
                            <th>截止日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end">工时(h)</th>
                            <th class="text-end" style="width: 160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->tasks as $task)
                            @php
                                $tb = [
                                    'todo' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                    'in_progress' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                    'done' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                ];
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $task->name }}</td>
                                <td class="text-center">{{ $priorityLabels[$task->priority] ?? '中' }}</td>
                                <td>{{ $task->assignee?->name ?: '—' }}</td>
                                <td>{{ $task->due_date ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $tb[$task->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $taskStatusLabels[$task->status] ?? $task->status }}
                                    </span>
                                </td>
                                <td class="text-end">{{ $task->total_hours }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('app.projects.tasks.update', $task) }}" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $task->status === 'done' ? 'todo' : ($task->status === 'todo' ? 'in_progress' : 'done') }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="切换状态">
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="collapse"
                                            data-bs-target="#time-{{ $task->id }}" title="登记工时">
                                        <i class="fa-solid fa-clock"></i>
                                    </button>
                                    <form action="{{ route('app.projects.tasks.destroy', $task) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该任务？其工时记录会一并删除')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="collapse" id="time-{{ $task->id }}">
                                <td colspan="7" class="bg-body-tertiary">
                                    <form method="POST" action="{{ route('app.projects.tasks.time.store', $task) }}" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small mb-0">日期</label>
                                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-6 col-md-2">
                                            <label class="form-label small mb-0">工时</label>
                                            <input type="number" step="0.1" name="hours" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label small mb-0">说明</label>
                                            <input type="text" name="description" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-12 col-md-3 d-grid">
                                            <button type="submit" class="btn btn-sm btn-success">登记工时</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if($project->tasks->isEmpty())
                            <tr><td colspan="7" class="erp-empty">暂无任务，请在上方添加</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-clock me-2"></i>最近工时记录</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th>日期</th>
                            <th>任务</th>
                            <th>登记人</th>
                            <th class="text-end">工时</th>
                            <th>说明</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timeEntries as $entry)
                            <tr>
                                <td>{{ $entry->date }}</td>
                                <td>{{ $entry->task?->name }}</td>
                                <td>{{ $entry->user?->name }}</td>
                                <td class="text-end">{{ $entry->hours }}</td>
                                <td class="small text-muted">{{ $entry->description ?: '—' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('app.projects.time.destroy', $entry) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该工时记录？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="erp-empty">暂无工时记录</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
