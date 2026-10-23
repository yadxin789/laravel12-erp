@extends('layouts.business')

@section('title', '考勤管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-check"></i>考勤管理</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.hr.employees.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-users me-1"></i>员工档案
            </a>
            <a href="{{ route('app.hr.attendances.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>登记考勤
            </a>
        </div>
    </div>

    <div class="row g-2 mb-4">
        @php
            $badges = [
                'normal' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                'late' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'early' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                'absent' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                'leave' => 'bg-info-subtle text-info-emphasis border-info-subtle',
            ];
        @endphp
        @foreach($statusLabels as $key => $label)
            <div class="col-6 col-md-2">
                <div class="card h-100">
                    <div class="card-body text-center py-2">
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-4 fw-bold">{{ $summary[$key] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.hr.attendances.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="month">月份</label>
                    <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
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
                    <a href="{{ route('app.hr.attendances.index') }}" class="btn btn-light">重置</a>
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
                            <th>日期</th>
                            <th>工号</th>
                            <th>姓名</th>
                            <th>上班</th>
                            <th>下班</th>
                            <th class="text-center">状态</th>
                            <th>备注</th>
                            <th class="text-end" style="width: 120px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                            <tr>
                                <td>{{ $att->date }}</td>
                                <td class="font-monospace small">{{ $att->employee?->emp_no }}</td>
                                <td class="fw-semibold">{{ $att->employee?->name }}</td>
                                <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</td>
                                <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $badges[$att->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$att->status] ?? $att->status }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $att->remark ?: '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.hr.attendances.edit', $att) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.hr.attendances.destroy', $att) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该考勤记录？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无考勤记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
@endsection
