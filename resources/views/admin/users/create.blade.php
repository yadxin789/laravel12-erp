@extends('layouts.admin')

@section('title', '新建用户')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-plus"></i>新建用户</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="name">用户名 <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="email">邮箱 <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required value="{{ old('email') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">密码 <span class="text-danger">*</span></label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="role">岗位角色 <span class="text-danger">*</span></label>
                            <select id="role" name="role" class="form-select" required>
                                <option value="">请选择角色</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                        {{ \App\Support\RoleCatalog::label($role) }}（{{ $role->name }}）
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">权限由角色决定；员工任职岗位后也可自动获得对应角色</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
