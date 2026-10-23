@extends('layouts.business')

@section('title', '收货管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-packing"></i>收货管理</h2>
        @can('create', \App\Models\Purchasing\PurchaseOrder::class)
        <a href="{{ route('app.purchase.receipts.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建收货单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>收货单号</th>
                            <th>采购订单</th>
                            <th>仓库</th>
                            <th>收货日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td class="font-monospace small">{{ $receipt->receipt_no }}</td>
                                <td>{{ $receipt->purchaseOrder?->order_no ?? '-' }}</td>
                                <td>{{ $receipt->warehouse?->name ?? '-' }}</td>
                                <td>{{ $receipt->receipt_date }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $receipt->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.purchase.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无收货单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $receipts->links() }}
            </div>
        </div>
    </div>
@endsection
