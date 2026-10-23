@extends('layouts.business')

@section('title', '采购订单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-shopping"></i>采购订单</h2>
        @can('create', \App\Models\Purchasing\PurchaseOrder::class)
        <a href="{{ route('app.purchase.orders.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建订单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>订单号</th>
                            <th>供应商</th>
                            <th>订单日期</th>
                            <th class="text-end">金额</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-monospace small">{{ $order->order_no }}</td>
                                <td>{{ $order->supplier?->name ?? '-' }}</td>
                                <td>{{ $order->order_date }}</td>
                                <td class="text-end">¥ {{ number_format($order->total_amount ?? 0, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.purchase.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无采购订单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
