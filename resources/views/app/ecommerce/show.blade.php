@extends('layouts.business')

@section('title', '电商订单详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-shopping"></i>订单详情</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.ecommerce.edit', $order) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen me-1"></i>编辑
            </a>
            <a href="{{ route('app.ecommerce.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>订单信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">平台</div>
                            <div class="fw-semibold">{{ $platforms[$order->platform] ?? $order->platform }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">平台订单号</div>
                            <div class="fw-semibold font-monospace">{{ $order->platform_order_no }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">买家</div>
                            <div class="fw-semibold">{{ $order->buyer_name ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">联系电话</div>
                            <div class="fw-semibold">{{ $order->buyer_phone ?: '—' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">订单金额</div>
                            <div class="fw-semibold">{{ number_format((float) $order->total_amount, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">同步时间</div>
                            <div class="fw-semibold small">{{ $order->synced_at ?: '未同步' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">收货地址</div>
                            <div class="fw-semibold">{{ $order->shipping_address ?: '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('app.ecommerce.status', $order) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label class="form-label small mb-0">订单状态</label>
                            <select name="status" class="form-select form-select-sm">
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-grid">
                            <button type="submit" class="btn btn-sm btn-outline-primary">更新状态</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-list me-2"></i>订单明细（{{ $order->items_count }} 行）</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>物料编码</th>
                                    <th class="text-end">数量</th>
                                    <th class="text-end">单价</th>
                                    <th class="text-end">小计</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sum = 0; @endphp
                                @forelse($order->items ?? [] as $item)
                                    @php
                                        $qty = (float) ($item['qty'] ?? 0);
                                        $price = (float) ($item['price'] ?? 0);
                                        $sum += $qty * $price;
                                    @endphp
                                    <tr>
                                        <td class="font-monospace small">{{ $item['code'] ?? '—' }}</td>
                                        <td class="text-end">{{ $qty }}</td>
                                        <td class="text-end">{{ number_format($price, 2) }}</td>
                                        <td class="text-end">{{ number_format($qty * $price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="erp-empty">该订单没有明细</td></tr>
                                @endforelse
                            </tbody>
                            @if($order->items_count > 0)
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">明细合计</td>
                                        <td class="text-end">{{ number_format($sum, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
