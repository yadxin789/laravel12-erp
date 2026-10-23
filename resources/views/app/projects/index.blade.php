@extends('layouts.business')

@section('title', '项目管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>项目管理</h2>
        <a href="{{ route('app.projects.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建项目
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">项目总数</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">进行中</div>
                <div class="stat-value text-primary">{{ $stats['in_progress'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">已完成</div>
                <div class="stat-value text-success">{{ $stats['completed'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">项目预算合计</div>
                <div class="stat-value">{{ number_format($stats['budget'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.projects.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">项目名称</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="status">状态</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">全部</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                    <a href="{{ route('app.projects.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>项目名称</th>
                            <th>客户</th>
                            <th>负责人</th>
                            <th class="text-center">任务</th>
                            <th>周期</th>
                            <th class="text-end">预算</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            @php
                                $badges = [
                                    'planning' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                    'in_progress' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                    'on_hold' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    'completed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                    'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                ];
                            @endphp
                            <tr>
                                <td class="fw-semibold">
                                    <a href="{{ route('app.projects.show', $project) }}" class="text-decoration-none">{{ $project->name }}</a>
                                </td>
                                <td>{{ $project->customer?->name ?: '—' }}</td>
                                <td>{{ $project->manager?->name ?: '—' }}</td>
                                <td class="text-center">{{ $project->tasks_count }}</td>
                                <td class="small">{{ $project->start_date ?: '—' }} ~ {{ $project->end_date ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $project->budget, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $badges[$project->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$project->status] ?? $project->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.projects.destroy', $project) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除项目「{{ $project->name }}」？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="erp-empty">暂无项目</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
@endsection
