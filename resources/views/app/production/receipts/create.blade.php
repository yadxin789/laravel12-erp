@extends('layouts.business')

@section('title', '新建成品入库单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>新建成品入库单</h2>
        <a href="{{ route('app.production.receipts.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.production.receipts.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="production_order_id">生产工单 <span class="text-danger">*</span></label>
                        <select id="production_order_id" name="production_order_id" class="form-select" required>
                            <option value="">选择工单</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}">{{ $order->order_no }} - {{ $order->item?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="receipt_date">入库日期 <span class="text-danger">*</span></label>
                        <input type="date" id="receipt_date" name="receipt_date" class="form-control"
                               value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="warehouse_id">仓库 <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select" required>
                            <option value="">选择仓库</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="quantity">数量 <span class="text-danger">*</span></label>
                        <input type="number" id="quantity" name="quantity" class="form-control" required min="1" value="{{ old('quantity') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="batch_number">批次号</label>
                        <input type="text" id="batch_number" name="batch_number" class="form-control" value="{{ old('batch_number') }}" placeholder="批次号">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="remark">备注</label>
                        <textarea id="remark" name="remark" rows="2" class="form-control">{{ old('remark') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建入库单</button>
                    <a href="{{ route('app.production.receipts.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
