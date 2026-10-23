@extends('layouts.business')

@section('title', '库存调拨')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-right-left"></i>库存调拨</h2>
        <a href="{{ route('app.inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.inventory.transfer.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="item_id">物料 <span class="text-danger">*</span></label>
                        <select id="item_id" name="item_id" class="form-select" required>
                            <option value="">选择物料</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="from_warehouse_id">转出仓库 <span class="text-danger">*</span></label>
                        <select id="from_warehouse_id" name="from_warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="to_warehouse_id">转入仓库 <span class="text-danger">*</span></label>
                        <select id="to_warehouse_id" name="to_warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="quantity">数量 <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required min="1" value="{{ old('quantity') }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label" for="batch_number">批次号</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-control" placeholder="批次号" value="{{ old('batch_number') }}">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-left me-1"></i>确认调拨</button>
                    <a href="{{ route('app.inventory.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
