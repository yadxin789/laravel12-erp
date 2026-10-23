@extends('layouts.business')

@section('title', '编辑员工')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-pen"></i>编辑员工</h2>
        <a href="{{ route('app.hr.employees.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.employees.update', $employee) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="emp_no">工号 <span class="text-danger">*</span></label>
                        <input type="text" id="emp_no" name="emp_no" class="form-control" required
                               value="{{ old('emp_no', $employee->emp_no) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">姓名 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="{{ old('name', $employee->name) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="department_id">部门</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="position_id">任职岗位</label>
                        <select id="position_id" name="position_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id', $employee->position_id) == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}@if($p->default_role)（授予 {{ $p->default_role_label }}）@endif
                                </option>
                            @endforeach
                        </select>
                        @if($employee->position && ! $employee->position_id)
                            <div class="form-text text-warning">当前岗位「{{ $employee->position }}」为历史文本，请选择正式岗位以关联角色</div>
                        @endif
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="phone">电话</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="email">邮箱</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="hire_date">入职日期</label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control"
                               value="{{ old('hire_date', $employee->hire_date) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">状态</label>
                        <div class="form-control-plaintext">
                            <span class="badge border {{ $employee->status === 'left' ? 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle' : 'bg-success-subtle text-success-emphasis border-success-subtle' }}">
                                {{ $employee->status === 'left' ? '离职' : '在职' }}
                            </span>
                            @if($employee->leave_date)
                                <span class="text-muted small ms-2">离职日期 {{ $employee->leave_date }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="user_id">关联系统账号</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">（不关联）</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('user_id', $employee->user_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}（{{ $u->email }}）
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>更新</button>
                    <a href="{{ route('app.hr.employees.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
