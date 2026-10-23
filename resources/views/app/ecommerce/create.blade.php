@extends('layouts.business')

@section('title', '手工建单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-plus"></i>手工建单</h2>
        <a href="{{ route('app.ecommerce.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.ecommerce.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="platform">平台 <span class="text-danger">*</span></label>
                        <select id="platform" name="platform" class="form-select" required>
                            @foreach($platforms as $key => $label)
                                <option value="{{ $key }}" {{ old('platform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="platform_order_no">平台订单号 <span class="text-danger">*</span></label>
                        <input type="text" id="platform_order_no" name="platform_order_no" class="form-control" required
                               value="{{ old('platform_order_no') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="total_amount">订单金额 <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control" required
                               value="{{ old('total_amount') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', 'pending') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="buyer_name">买家姓名</label>
                        <input type="text" id="buyer_name" name="buyer_name" class="form-control" value="{{ old('buyer_name') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="buyer_phone">联系电话</label>
                        <input type="text" id="buyer_phone" name="buyer_phone" class="form-control" value="{{ old('buyer_phone') }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">收货地址</div>
                        <input type="text" name="shipping_address" class="form-control" value="{{ old('shipping_address') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="items_text">订单明细</label>
                        <textarea id="items_text" name="items_text" rows="4" class="form-control"
                                  placeholder="每行一条：物料编码  数量  单价&#10;如：RM-001  10  25.5">{{ old('items_text') }}</textarea>
                        <div class="form-text">三个字段用空格分隔，留空表示无明细</div>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>创建</button>
                    <a href="{{ route('app.ecommerce.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
