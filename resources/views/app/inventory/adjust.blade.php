@extends('layouts.business')

@section('title', '库存调整')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-sliders"></i>库存调整</h2>
        <a href="{{ route('app.inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.inventory.adjust.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="item_id">物料 <span class="text-danger">*</span></label>
                        <select id="item_id" name="item_id" class="form-select" required>
                            <option value="">选择物料</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="warehouse_id">仓库 <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="quantity">调整数量 <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required
                               placeholder="正数增加，负数减少" value="{{ old('quantity') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="reason">调整原因 <span class="text-danger">*</span></label>
                        <textarea id="reason" name="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i>确认调整</button>
                    <a href="{{ route('app.inventory.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
