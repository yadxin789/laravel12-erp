@extends('layouts.business')

@section('title', '员工详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-id-card"></i>员工详情</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.hr.employees.edit', $employee) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen me-1"></i>编辑
            </a>
            <a href="{{ route('app.hr.employees.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>基本信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">工号</div>
                            <div class="fw-semibold font-monospace">{{ $employee->emp_no }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">姓名</div>
                            <div class="fw-semibold">{{ $employee->name }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">部门</div>
                            <div class="fw-semibold">{{ $employee->department ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">岗位</div>
                            <div class="fw-semibold">{{ $employee->position ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">电话</div>
                            <div class="fw-semibold">{{ $employee->phone ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">邮箱</div>
                            <div class="fw-semibold">{{ $employee->email ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">入职日期</div>
                            <div class="fw-semibold">{{ $employee->hire_date ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">在职状态</div>
                            <div class="fw-semibold">
                                <span class="badge border {{ $employee->status === 'left' ? 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' : 'bg-success-subtle text-success-emphasis border-success-subtle' }}">
                                    {{ $employee->status === 'left' ? '离职' : '在职' }}
                                </span>
                                @if($employee->leave_date)
                                    <span class="text-muted small ms-1">（{{ $employee->leave_date }} 离职）</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">任职岗位 → 系统角色</div>
                            <div class="fw-semibold">
                                {{ $employee->position ?: '—' }}
                                @if($employee->role_name)
                                    <span class="badge border bg-primary-subtle text-primary-emphasis border-primary-subtle ms-1">
                                        {{ $employee->role_label }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">关联系统账号</div>
                            <div class="fw-semibold">
                                {{ $employee->user ? $employee->user->name . '（' . $employee->user->email . '）' : '未关联' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-calendar-check me-2"></i>{{ $month }} 考勤汇总</div>
                <div class="card-body">
                    @php
                        $labels = ['normal' => '正常', 'late' => '迟到', 'early' => '早退', 'absent' => '缺勤', 'leave' => '请假'];
                        $badges = [
                            'normal' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                            'late' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                            'early' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                            'absent' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                            'leave' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                        ];
                    @endphp
                    <div class="row g-2 mb-3">
                        @foreach($labels as $key => $label)
                            <div class="col-6 col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <div class="text-muted small">{{ $label }}</div>
                                    <div class="fs-5 fw-bold">{{ $summary[$key] ?? 0 }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>上班</th>
                                    <th>下班</th>
                                    <th class="text-center">状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->attendances as $att)
                                    <tr>
                                        <td>{{ $att->date }}</td>
                                        <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</td>
                                        <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge border {{ $badges[$att->status] ?? 'bg-light text-secondary border' }}">
                                                {{ $labels[$att->status] ?? $att->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="erp-empty">暂无考勤记录</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
