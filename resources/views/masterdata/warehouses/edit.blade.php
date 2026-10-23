@extends('layouts.app')

@section('title', '编辑仓库')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-regular fa-pen-to-square"></i>编辑仓库</h2>
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="code">编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" value="{{ old('code', $warehouse->code) }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $warehouse->name) }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="type">类型</label>
                        <select id="type" name="type" class="form-select">
                            <option value="raw_material" {{ old('type', $warehouse->type) === 'raw_material' ? 'selected' : '' }}>原材料仓</option>
                            <option value="work_in_progress" {{ old('type', $warehouse->type) === 'work_in_progress' ? 'selected' : '' }}>半成品仓</option>
                            <option value="finished_goods" {{ old('type', $warehouse->type) === 'finished_goods' ? 'selected' : '' }}>成品仓</option>
                            <option value="waste" {{ old('type', $warehouse->type) === 'waste' ? 'selected' : '' }}>废品仓</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="address">地址</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $warehouse->address) }}" class="form-control">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
