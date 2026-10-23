@extends('layouts.app')

@section('title', '创建仓库')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-plus"></i>创建仓库</h2>
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.warehouses.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="code">仓库编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" required value="{{ old('code') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">仓库名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name') }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="type">仓库类型</label>
                        <select id="type" name="type" class="form-select">
                            <option value="raw_material" {{ old('type') === 'raw_material' ? 'selected' : '' }}>原材料仓</option>
                            <option value="work_in_progress" {{ old('type') === 'work_in_progress' ? 'selected' : '' }}>半成品仓</option>
                            <option value="finished_goods" {{ old('type') === 'finished_goods' ? 'selected' : '' }}>成品仓</option>
                            <option value="waste" {{ old('type') === 'waste' ? 'selected' : '' }}>废品仓</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="address">地址</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control">
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
