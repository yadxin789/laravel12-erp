@extends('layouts.business')

@section('title', '招聘管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-bullhorn"></i>招聘需求</h2>
        <a href="{{ route('app.hr.recruitments.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建需求
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">招聘中需求</div>
                <div class="stat-value">{{ $stats['open'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">待招人数</div>
                <div class="stat-value text-primary">{{ $stats['headcount'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">面试中</div>
                <div class="stat-value">{{ $stats['interview'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">已录用</div>
                <div class="stat-value text-success">{{ $stats['hired'] }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.hr.recruitments.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="department_id">需求部门</label>
                    <select id="department_id" name="department_id" class="form-select">
                        <option value="">全部</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
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
                    <a href="{{ route('app.hr.recruitments.index') }}" class="btn btn-light">重置</a>
                    <a href="{{ route('app.hr.applications.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-users me-1"></i>应聘者
                    </a>
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
                            <th>需求编号</th>
                            <th>需求名称</th>
                            <th>部门</th>
                            <th>岗位</th>
                            <th class="text-center">需求/已录用</th>
                            <th>薪资范围</th>
                            <th>期望到岗</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sb = [
                                'open' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'filled' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'closed' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                            ];
                        @endphp
                        @forelse($requests as $req)
                            <tr>
                                <td class="font-monospace small">{{ $req->req_no }}</td>
                                <td class="fw-semibold">{{ $req->title }}</td>
                                <td>{{ $req->department?->name ?: '—' }}</td>
                                <td>{{ $req->position?->name ?: '—' }}</td>
                                <td class="text-center">
                                    {{ $req->headcount }} / <span class="text-success">{{ $req->hired_count }}</span>
                                </td>
                                <td class="small">
                                    @if($req->salary_range_min || $req->salary_range_max)
                                        {{ number_format((float) $req->salary_range_min) }} - {{ number_format((float) $req->salary_range_max) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small">{{ $req->expect_date?->format('Y-m-d') ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $sb[$req->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$req->status] ?? $req->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.hr.recruitments.edit', $req) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.hr.recruitments.destroy', $req) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该招聘需求？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="erp-empty">暂无招聘需求</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
@endsection
