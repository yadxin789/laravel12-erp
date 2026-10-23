@extends('layouts.admin')

@section('title', '审计日志')

@section('content')
    @php
        $actionMap = [
            'create' => ['新增', 'bg-success-subtle text-success-emphasis border-success-subtle'],
            'update' => ['修改', 'bg-primary-subtle text-primary-emphasis border-primary-subtle'],
            'delete' => ['删除', 'bg-danger-subtle text-danger-emphasis border-danger-subtle'],
            'login' => ['登录', 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
            'logout' => ['登出', 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle'],
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-list"></i>操作日志</h2>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="search">搜索操作描述</label>
                    <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="action_type">操作类型</label>
                    <select id="action_type" name="action_type" class="form-select">
                        <option value="">所有操作类型</option>
                        <option value="create" {{ request('action_type') === 'create' ? 'selected' : '' }}>新增</option>
                        <option value="update" {{ request('action_type') === 'update' ? 'selected' : '' }}>修改</option>
                        <option value="delete" {{ request('action_type') === 'delete' ? 'selected' : '' }}>删除</option>
                        <option value="login" {{ request('action_type') === 'login' ? 'selected' : '' }}>登录</option>
                        <option value="logout" {{ request('action_type') === 'logout' ? 'selected' : '' }}>登出</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>搜索
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light">重置</a>
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
                            <th>时间</th>
                            <th>操作人</th>
                            <th>操作类型</th>
                            <th>模块</th>
                            <th>操作描述</th>
                            <th>IP地址</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php $a = $actionMap[$log->action_type] ?? [$log->action_type, 'bg-light text-secondary border']; @endphp
                            <tr>
                                <td class="text-muted small">{{ $log->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $log->user->name ?? '-' }}</td>
                                <td><span class="badge border {{ $a[1] }}">{{ $a[0] }}</span></td>
                                <td>{{ $log->module }}</td>
                                <td>{{ $log->description }}</td>
                                <td class="text-muted small">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无日志记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
