@extends('layouts.business')

@section('title', '新增员工')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-plus"></i>新增员工</h2>
        <a href="{{ route('app.hr.employees.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.hr.employees.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="emp_no">工号 <span class="text-danger">*</span></label>
                        <input type="text" id="emp_no" name="emp_no" class="form-control" required
                               placeholder="如 E010" value="{{ old('emp_no') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">姓名 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="department_id">部门</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="position_id">任职岗位</label>
                        <select id="position_id" name="position_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}" {{ old('position_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}@if($p->default_role)（授予 {{ $p->default_role_label }}）@endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">岗位在后台「组织架构 → 岗位管理」维护，可设置任职默认授予的角色</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="phone">电话</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="email">邮箱</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="hire_date">入职日期</label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control" value="{{ old('hire_date') }}">
                    </div>
                    <div class="col-12 col-md-8">
                        <label class="form-label" for="user_id">关联系统账号</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">（不关联）</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}（{{ $u->email }}）
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">关联后，该员工可用自己的账号登录业务系统；保存时按其任职岗位自动授予对应角色</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.hr.employees.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
