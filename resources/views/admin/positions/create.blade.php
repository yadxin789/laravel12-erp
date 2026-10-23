@extends('layouts.admin')

@section('title', '新建岗位')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-id-badge"></i>新建岗位</h2>
        <a href="{{ route('admin.positions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.positions.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="code">岗位编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" required
                               placeholder="如 SALES_MGR" value="{{ old('code') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">岗位名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               placeholder="如 销售经理" value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="department_id">所属部门</label>
                        <select id="department_id" name="department_id" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="level">职级</label>
                        <select id="level" name="level" class="form-select">
                            <option value="">（未指定）</option>
                            @foreach(['高管', '经理', '主管', '专员', '员工'] as $lv)
                                <option value="{{ $lv }}" {{ old('level') === $lv ? 'selected' : '' }}>{{ $lv }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="headcount">编制人数</label>
                        <input type="number" id="headcount" name="headcount" class="form-control" min="1"
                               value="{{ old('headcount', 1) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="sort_order">排序</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
                               value="{{ old('sort_order', 0) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="default_role">任职默认授予角色</label>
                        <select id="default_role" name="default_role" class="form-select">
                            <option value="">（不自动授权）</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" {{ old('default_role') === $r->name ? 'selected' : '' }}>
                                    {{ \App\Support\RoleCatalog::label($r) }}（{{ $r->name }}）
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            员工档案中任职该岗位并关联了系统账号时，自动为该账号授予此角色；
                            岗位只描述编制，真正的权限仍由角色决定
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                   {{ old('active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">启用</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label" for="description">岗位职责</label>
                    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                    <a href="{{ route('admin.positions.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
