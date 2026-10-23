@extends('layouts.business')

@section('title', '编辑电商订单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-flatbed"></i>编辑电商订单</h2>
        <a href="{{ route('app.ecommerce.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('app.ecommerce.update', $order) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="platform">平台 <span class="text-danger">*</span></label>
                        <select id="platform" name="platform" class="form-select" required>
                            @foreach($platforms as $key => $label)
                                <option value="{{ $key }}" {{ old('platform', $order->platform) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="platform_order_no">平台订单号 <span class="text-danger">*</span></label>
                        <input type="text" id="platform_order_no" name="platform_order_no" class="form-control" required
                               value="{{ old('platform_order_no', $order->platform_order_no) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="total_amount">订单金额 <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control" required
                               value="{{ old('total_amount', $order->total_amount) }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">状态</label>
                        <select id="status" name="status" class="form-select">
                            @foreach($statusLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('status', $order->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="buyer_name">买家姓名</label>
                        <input type="text" id="buyer_name" name="buyer_name" class="form-control"
                               value="{{ old('buyer_name', $order->buyer_name) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="buyer_phone">联系电话</label>
                        <input type="text" id="buyer_phone" name="buyer_phone" class="form-control"
                               value="{{ old('buyer_phone', $order->buyer_phone) }}">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">同步时间</label>
                        <div class="form-control-plaintext">{{ $order->synced_at ?: '未同步' }}</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="shipping_address">收货地址</label>
                        <input type="text" id="shipping_address" name="shipping_address" class="form-control"
                               value="{{ old('shipping_address', $order->shipping_address) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="items_text">订单明细</label>
                        <textarea id="items_text" name="items_text" rows="4" class="form-control"
                                  placeholder="每行一条：物料编码  数量  单价">{{ old('items_text', collect($order->items ?? [])->map(fn ($i) => implode(' ', [($i['code'] ?? ''), ($i['qty'] ?? 1), ($i['price'] ?? 0)]))->implode("\n")) }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>更新</button>
                    <a href="{{ route('app.ecommerce.index') }}" class="btn btn-light">取消</a>
                </div>
            </form>
        </div>
    </div>
@endsection
