@extends('layouts.admin')

@section('title', '新建部门')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-sitemap"></i>新建部门</h2>
        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="code">部门编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" required
                               placeholder="如 SALES" value="{{ old('code') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">部门名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                               placeholder="如 销售部" value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="short_name">简称</label>
                        <input type="text" id="short_name" name="short_name" class="form-control" value="{{ old('short_name') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="parent_id">上级部门</label>
                        <select id="parent_id" name="parent_id" class="form-select">
                            <option value="">（顶级部门）</option>
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}（{{ $p->code }}）
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="sort_order">排序</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
                               value="{{ old('sort_order', 0) }}">
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1"
                                   {{ old('active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">启用</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="description">部门职责</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
