@extends('layouts.business')

@section('title', '新建生产汇报')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>新建生产汇报</h2>
        <a href="{{ route('app.production.reports.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.production.reports.store') }}">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="production_order_id">生产工单 <span class="text-danger">*</span></label>
                        <select id="production_order_id" name="production_order_id" class="form-select" required>
                            <option value="">选择工单</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" {{ old('production_order_id') == $order->id ? 'selected' : '' }}>{{ $order->order_no }} - {{ $order->item?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="operation_id">工序 <span class="text-danger">*</span></label>
                        <input type="text" id="operation_id" name="operation_id" class="form-control"
                               value="{{ old('operation_id') }}" placeholder="工序编号或名称" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="reported_quantity">汇报数量 <span class="text-danger">*</span></label>
                        <input type="number" id="reported_quantity" name="reported_quantity" class="form-control" required min="1" value="{{ old('reported_quantity') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="rejected_quantity">返工数量</label>
                        <input type="number" id="rejected_quantity" name="rejected_quantity" class="form-control" min="0" value="{{ old('rejected_quantity', 0) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="scrapped_quantity">报废数量</label>
                        <input type="number" id="scrapped_quantity" name="scrapped_quantity" class="form-control" min="0" value="{{ old('scrapped_quantity', 0) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="remark">备注</label>
                        <textarea id="remark" name="remark" rows="3" class="form-control">{{ old('remark') }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>提交汇报</button>
                    <a href="{{ route('app.production.reports.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
