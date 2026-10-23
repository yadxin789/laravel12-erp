@extends('layouts.business')

@section('title', '绩效考核')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-ranking-star"></i>绩效考核</h2>
        <a href="{{ route('app.hr.performances.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建考核
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">本期考核人数</div>
                <div class="stat-value">{{ $stats['count'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">平均分</div>
                <div class="stat-value text-primary">{{ $stats['avg'] }}</div>
            </div></div>
        </div>
        @foreach($gradeLabels as $g => $gLabel)
            <div class="col-6 col-md-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">{{ $g }} 级（{{ $gLabel }}）</div>
                    <div class="stat-value">{{ $stats['grades'][$g] ?? 0 }} 人</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.hr.performances.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="period">考核月份</label>
                    <input type="month" id="period" name="period" class="form-control" value="{{ $period }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="employee_id">员工</label>
                    <select id="employee_id" name="employee_id" class="form-select">
                        <option value="">全部</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->emp_no }} {{ $e->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label" for="grade">等级</label>
                    <select id="grade" name="grade" class="form-select">
                        <option value="">全部</option>
                        @foreach($gradeLabels as $g => $gLabel)
                            <option value="{{ $g }}" {{ request('grade') === $g ? 'selected' : '' }}>{{ $g }} {{ $gLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
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
                    <a href="{{ route('app.hr.performances.index') }}" class="btn btn-light">重置</a>
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
                            <th>考核周期</th>
                            <th>类型</th>
                            <th>工号</th>
                            <th>姓名</th>
                            <th class="text-end">得分</th>
                            <th class="text-center">等级</th>
                            <th class="d-none d-lg-table-cell">评语</th>
                            <th>考核人</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $gb = [
                                'A' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                'B' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'C' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                'D' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                            ];
                        @endphp
                        @forelse($reviews as $review)
                            <tr>
                                <td class="font-monospace small">{{ $review->period }}</td>
                                <td>{{ $typeLabels[$review->type] ?? $review->type }}</td>
                                <td class="font-monospace small">{{ $review->employee?->emp_no }}</td>
                                <td class="fw-semibold">{{ $review->employee?->name }}</td>
                                <td class="text-end fw-semibold">{{ $review->score }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $gb[$review->grade] ?? 'bg-light text-secondary border' }}">
                                        {{ $review->grade }} {{ $review->grade_label }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell small text-muted">{{ \Illuminate\Support\Str::limit($review->summary, 40) ?: '—' }}</td>
                                <td>{{ $review->reviewer ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $review->status === 'completed' ? 'bg-success-subtle text-success-emphasis border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' }}">
                                        {{ $statusLabels[$review->status] ?? $review->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.hr.performances.edit', $review) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.hr.performances.destroy', $review) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该绩效评估？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="erp-empty">本期暂无绩效评估</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
@endsection
