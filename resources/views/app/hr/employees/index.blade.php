@extends('layouts.business')

@section('title', '员工档案')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-users"></i>员工档案</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.hr.attendances.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-calendar-check me-1"></i>考勤管理
            </a>
            <form method="POST" action="{{ route('app.hr.employees.sync-roles') }}"
                  onsubmit="return confirm('按每个人的任职岗位重新同步系统角色，确定继续？')">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-user-shield me-1"></i>同步岗位角色
                </button>
            </form>
            <a href="{{ route('app.hr.employees.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-plus me-1"></i>新增员工
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="text-muted small">员工总数</div>
                        <div class="stat-value">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                    <div>
                        <div class="text-muted small">在职</div>
                        <div class="stat-value text-success">{{ $stats['active'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon"><i class="fa-solid fa-user-xmark"></i></div>
                    <div>
                        <div class="text-muted small">已离职</div>
                        <div class="stat-value text-secondary">{{ $stats['left'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.hr.employees.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">关键字</label>
                    <input type="text" id="keyword" name="keyword" class="form-control"
                           placeholder="工号 / 姓名 / 电话" value="{{ request('keyword') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="department">部门</label>
                    <select id="department" name="department" class="form-select">
                        <option value="">全部</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ request('department') === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="status">状态</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">全部</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>在职</option>
                        <option value="left" {{ request('status') === 'left' ? 'selected' : '' }}>离职</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                    <a href="{{ route('app.hr.employees.index') }}" class="btn btn-light">重置</a>
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
                            <th>工号</th>
                            <th>姓名</th>
                            <th>部门</th>
                            <th>岗位</th>
                            <th>系统角色</th>
                            <th>电话</th>
                            <th>入职日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 180px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="font-monospace small">{{ $emp->emp_no }}</td>
                                <td class="fw-semibold">
                                    <a href="{{ route('app.hr.employees.show', $emp) }}" class="text-decoration-none">{{ $emp->name }}</a>
                                </td>
                                <td>{{ $emp->department ?: '—' }}</td>
                                <td>{{ $emp->position ?: '—' }}</td>
                                <td>
                                    @if($emp->role_name)
                                        <span class="badge border bg-primary-subtle text-primary-emphasis border-primary-subtle">
                                            {{ $emp->role_label }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>{{ $emp->phone ?: '—' }}</td>
                                <td>{{ $emp->hire_date ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $emp->status === 'left' ? 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' : 'bg-success-subtle text-success-emphasis border-success-subtle' }}">
                                        {{ $emp->status === 'left' ? '离职' : '在职' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.hr.employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @if($emp->status !== 'left')
                                        <form action="{{ route('app.hr.employees.leave', $emp) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定将「{{ $emp->name }}」办理离职？')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="办理离职">
                                                <i class="fa-solid fa-person-walking-arrow-right"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('app.hr.employees.destroy', $emp) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除员工「{{ $emp->name }}」？其考勤记录会一并删除')">
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
                                <td colspan="9" class="erp-empty">暂无员工档案</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
@endsection
