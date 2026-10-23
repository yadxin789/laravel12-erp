@extends('layouts.admin')

@section('title', '新建权限')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-key"></i>新建权限</h2>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-7">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.permissions.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="display">权限名称（中文）</label>
                            <input type="text" id="display" name="display" class="form-control"
                                   placeholder="如：查看销售订单" value="{{ old('display') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="name">权限标识 <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required
                                   placeholder="如：view sales orders" value="{{ old('name') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="group">所属模块</label>
                            <select id="group" name="group" class="form-select">
                                <option value="">（未分组）</option>
                                @foreach($groups as $key => $label)
                                    <option value="{{ $key }}" {{ old('group') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-light">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
