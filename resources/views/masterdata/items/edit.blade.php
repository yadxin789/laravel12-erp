@extends('layouts.app')

@section('title', '编辑物料')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-regular fa-pen-to-square"></i>编辑物料</h2>
        <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.items.update', $item) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="code">物料编码 <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" required value="{{ old('code', $item->code) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="name">物料名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $item->name) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="spec">规格型号</label>
                        <input type="text" id="spec" name="spec" value="{{ old('spec', $item->spec) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="category_id">类别</label>
                        <select id="category_id" name="category_id" class="form-select">
                            <option value="">请选择</option>
                            @foreach(\App\Models\MasterData\ItemCategory::all() as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="unit_id">单位 <span class="text-danger">*</span></label>
                        <select id="unit_id" name="unit_id" required class="form-select">
                            <option value="">请选择</option>
                            @foreach(\App\Models\MasterData\Unit::all() as $u)
                                <option value="{{ $u->id }}" {{ old('unit_id', $item->unit_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="standard_cost">标准成本</label>
                        <input type="number" step="0.0001" id="standard_cost" name="standard_cost"
                               value="{{ old('standard_cost', $item->standard_cost) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="safety_stock">安全库存</label>
                        <input type="number" id="safety_stock" name="safety_stock" value="{{ old('safety_stock', $item->safety_stock) }}" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="image">物料图片</label>
                        @if($item->image_url)
                            <div class="mb-2">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="rounded border" width="72" height="72" style="object-fit: cover;">
                            </div>
                        @endif
                        <input type="file" id="image" name="image" accept="image/*" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">物料描述</label>
                        <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $item->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <span class="form-label d-block">管理选项</span>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="batch_management" value="1" id="batch_management"
                                   {{ old('batch_management', $item->batch_management) ? 'checked' : '' }}>
                            <label class="form-check-label" for="batch_management">批次管理</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="serial_management" value="1" id="serial_management"
                                   {{ old('serial_management', $item->serial_management) ? 'checked' : '' }}>
                            <label class="form-check-label" for="serial_management">序列号管理</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="quality_inspection_required" value="1" id="quality_inspection_required"
                                   {{ old('quality_inspection_required', $item->quality_inspection_required) ? 'checked' : '' }}>
                            <label class="form-check-label" for="quality_inspection_required">需要质检</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('admin.items.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
