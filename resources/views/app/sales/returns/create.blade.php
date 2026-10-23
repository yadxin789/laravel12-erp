@extends('layouts.business')

@section('title', '创建销售退货单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-rotate-left"></i>创建销售退货单</h2>
        <a href="{{ route('app.sales.returns.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('app.sales.returns.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="sales_order_id">销售订单 <span class="text-danger">*</span></label>
                        <select id="sales_order_id" name="sales_order_id" class="form-select" required>
                            <option value="">请选择</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" {{ old('sales_order_id') == $order->id ? 'selected' : '' }}>{{ $order->order_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="warehouse_id">仓库 <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select" required>
                            <option value="">请选择</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="return_date">退货日期 <span class="text-danger">*</span></label>
                        <input type="date" id="return_date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="reason">退货原因</label>
                        <select id="reason" name="reason" class="form-select">
                            <option value="">请选择</option>
                            <option value="质量不良" {{ old('reason') === '质量不良' ? 'selected' : '' }}>质量不良</option>
                            <option value="发错货" {{ old('reason') === '发错货' ? 'selected' : '' }}>发错货</option>
                            <option value="数量不符" {{ old('reason') === '数量不符' ? 'selected' : '' }}>数量不符</option>
                            <option value="其他" {{ old('reason') === '其他' ? 'selected' : '' }}>其他</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="remark">备注</label>
                        <textarea id="remark" name="remark" rows="2" class="form-control">{{ old('remark') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    <a href="{{ route('app.sales.returns.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
