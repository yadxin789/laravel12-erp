@extends('layouts.business')

@section('title', '采购订单详情')

@section('content')
    @php
        use App\Enums\PurchaseOrderStatus;
        $status = $order->status;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-shopping"></i>采购订单: {{ $order->order_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($status === PurchaseOrderStatus::Draft)
                <form action="{{ route('app.purchase.orders.submit', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>提交审核</button>
                </form>
            @elseif($status === PurchaseOrderStatus::Submitted)
                <form action="{{ route('app.purchase.orders.approve', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>审核通过</button>
                </form>
            @endif
            @if($status !== PurchaseOrderStatus::Cancelled)
                <form action="{{ route('app.purchase.orders.cancel', $order) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确定取消该采购订单吗？')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-ban me-1"></i>取消订单</button>
                </form>
            @endif
            <a href="{{ route('app.purchase.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">供应商</div>
                    <div class="fw-semibold">{{ $order->supplier?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">订单日期</div>
                    <div class="fw-semibold">{{ $order->order_date }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">交货日期</div>
                    <div class="fw-semibold">{{ $order->delivery_date }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $status?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">订单金额</div>
                    <div class="fw-semibold">¥{{ number_format($order->total_amount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>订单明细</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>物料</th>
                            <th>规格</th>
                            <th class="text-end">数量</th>
                            <th class="text-end">单价</th>
                            <th class="text-end">金额</th>
                            <th>仓库</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->lines as $line)
                            <tr>
                                <td>{{ $line->item?->name }}</td>
                                <td>{{ $line->item?->spec }}</td>
                                <td class="text-end">{{ $line->quantity }}</td>
                                <td class="text-end">{{ $line->unit_price }}</td>
                                <td class="text-end">{{ $line->amount }}</td>
                                <td>{{ $line->warehouse?->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无订单明细</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
