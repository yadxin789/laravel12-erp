@extends('layouts.business')

@section('title', '应聘者管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-users-viewfinder"></i>应聘者管理</h2>
        <a href="{{ route('app.hr.applications.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>登记应聘者
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">应聘总数</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">已投递</div>
                <div class="stat-value">{{ $stats['by_status']['applied'] ?? 0 }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">面试中</div>
                <div class="stat-value text-primary">{{ $stats['by_status']['interview'] ?? 0 }}</div>
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
            <form method="GET" action="{{ route('app.hr.applications.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="status">应聘状态</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">全部</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="position_id">应聘岗位</label>
                    <select id="position_id" name="position_id" class="form-select">
                        <option value="">全部</option>
                        @foreach($positions as $p)
                            <option value="{{ $p->id }}" {{ request('position_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">姓名/编号/电话</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                    <a href="{{ route('app.hr.applications.index') }}" class="btn btn-light">重置</a>
                    <a href="{{ route('app.hr.recruitments.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-bullhorn me-1"></i>招聘需求
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
                            <th>编号</th>
                            <th>姓名</th>
                            <th>应聘岗位</th>
                            <th class="d-none d-lg-table-cell">来源</th>
                            <th class="d-none d-lg-table-cell">学历/年限</th>
                            <th class="text-end d-none d-md-table-cell">期望薪资</th>
                            <th>面试时间</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 220px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sb = [
                                'applied' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                'screening' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                'interview' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'offered' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                'hired' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'rejected' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                            ];
                        @endphp
                        @forelse($applications as $app)
                            <tr>
                                <td class="font-monospace small">{{ $app->app_no }}</td>
                                <td class="fw-semibold">
                                    {{ $app->name }}
                                    @if($app->phone)
                                        <div class="small text-muted">{{ $app->phone }}</div>
                                    @endif
                                </td>
                                <td>{{ $app->position?->name ?: '—' }}</td>
                                <td class="d-none d-lg-table-cell small">{{ $app->source ?: '—' }}</td>
                                <td class="d-none d-lg-table-cell small">
                                    {{ $app->education ?: '—' }}@if($app->experience_years !== null) / {{ $app->experience_years }}年 @endif
                                </td>
                                <td class="text-end d-none d-md-table-cell small">
                                    {{ $app->expect_salary ? number_format((float) $app->expect_salary) : '—' }}
                                </td>
                                <td class="small">{{ $app->interview_at?->format('Y-m-d H:i') ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $sb[$app->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$app->status] ?? $app->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if(! $app->isFinished())
                                        @php $next = \App\Models\HR\JobApplication::NEXT[$app->status] ?? []; @endphp
                                        @foreach($next as $target)
                                            <form action="{{ route('app.hr.applications.status', $app) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $target }}">
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-{{ $target === 'rejected' ? 'danger' : 'success' }}">
                                                    {{ $statusLabels[$target] }}
                                                </button>
                                            </form>
                                        @endforeach
                                    @endif

                                    @if($app->status === 'hired')
                                        <form action="{{ route('app.hr.applications.to-employee', $app) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('转为正式员工档案？')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="转为员工档案">
                                                <i class="fa-solid fa-user-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('app.hr.applications.edit', $app) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.hr.applications.destroy', $app) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该应聘者记录？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="erp-empty">暂无应聘者</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
@endsection
