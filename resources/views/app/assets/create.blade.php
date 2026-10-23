@extends('layouts.business')

@section('title', '登记资产')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>登记资产</h2>
        <a href="{{ route('app.assets.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.assets.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="asset_no">资产编号 <span class="text-danger">*</span></label>
                        <input type="text" id="asset_no" name="asset_no" class="form-control" required
                               placeholder="如 FA-2026-0001" value="{{ old('asset_no') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="name">资产名称 <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="category">分类</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">（未分类）</option>
                            @foreach($categories as $c)
                                <option value="{{ $c }}" {{ old('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="original_cost">资产原值 <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="original_cost" name="original_cost" class="form-control"
                               required value="{{ old('original_cost') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="useful_life_years">折旧年限（年） <span class="text-danger">*</span></label>
                        <input type="number" id="useful_life_years" name="useful_life_years" class="form-control"
                               min="1" max="50" required value="{{ old('useful_life_years', 5) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="purchase_date">购置日期 <span class="text-danger">*</span></label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-control" required
                               value="{{ old('purchase_date') }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="start_depreciation_date">起折日期</label>
                        <input type="date" id="start_depreciation_date" name="start_depreciation_date" class="form-control"
                               value="{{ old('start_depreciation_date') }}">
                        <div class="form-text">留空则与购置日期相同</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'in_use') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="assigned_to">使用人</label>
                        <select id="assigned_to" name="assigned_to" class="form-select">
                            <option value="">（未领用）</option>
                            @foreach($employees as $e)
                                <option value="{{ $e->id }}" {{ old('assigned_to') == $e->id ? 'selected' : '' }}>
                                    {{ $e->emp_no }} {{ $e->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="location">存放位置</label>
                        <input type="text" id="location" name="location" class="form-control"
                               placeholder="如 一号车间 / 办公楼 3 层" value="{{ old('location') }}">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>登记</button>
                    <a href="{{ route('app.assets.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
