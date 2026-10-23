@extends('layouts.business')

@section('title', '薪资管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-money-check-dollar"></i>薪资管理</h2>
        <a href="{{ route('app.hr.payroll.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>新建工资单
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">本月工资单</div>
                <div class="stat-value">{{ $stats['count'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">应发合计</div>
                <div class="stat-value">{{ number_format($stats['gross'], 2) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">扣款合计</div>
                <div class="stat-value text-danger">{{ number_format($stats['deduction'], 2) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">实发合计</div>
                <div class="stat-value text-success">{{ number_format($stats['net'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.hr.payroll.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="period">薪资月份</label>
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
                    <a href="{{ route('app.hr.payroll.index') }}" class="btn btn-light">重置</a>
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
                            <th>月份</th>
                            <th>工号</th>
                            <th>姓名</th>
                            <th class="text-end">基本工资</th>
                            <th class="text-end">津贴/奖金</th>
                            <th class="text-end">应发</th>
                            <th class="text-end">扣款</th>
                            <th class="text-end">实发</th>
                            <th class="text-center">状态</th>
                            <th>发放日期</th>
                            <th class="text-end" style="width: 180px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sb = [
                                'draft' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                'approved' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                'paid' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                            ];
                        @endphp
                        @forelse($records as $row)
                            <tr>
                                <td class="font-monospace small">{{ $row->period }}</td>
                                <td class="font-monospace small">{{ $row->employee?->emp_no }}</td>
                                <td class="fw-semibold">{{ $row->employee?->name }}</td>
                                <td class="text-end">{{ number_format((float) $row->base_salary, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format((float) $row->allowance + (float) $row->bonus + (float) $row->overtime_pay, 2) }}
                                </td>
                                <td class="text-end">{{ number_format($row->grossPay(), 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($row->totalDeduction(), 2) }}</td>
                                <td class="text-end fw-semibold">{{ number_format($row->calcNetPay(), 2) }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $sb[$row->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$row->status] ?? $row->status }}
                                    </span>
                                </td>
                                <td class="small">{{ $row->paid_at ?: '—' }}</td>
                                <td class="text-end">
                                    @if($row->status !== 'paid')
                                        <form action="{{ route('app.hr.payroll.status', $row) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $row->status === 'draft' ? 'approved' : 'paid' }}">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="推进状态">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('app.hr.payroll.edit', $row) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.hr.payroll.destroy', $row) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该工资单？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="erp-empty">本月暂无工资单</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection
