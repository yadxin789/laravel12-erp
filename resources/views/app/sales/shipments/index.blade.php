@extends('layouts.business')

@section('title', '发货管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-truck"></i>发货管理</h2>
        @can('create', \App\Models\Sales\SalesOrder::class)
        <a href="{{ route('app.sales.shipments.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建发货单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>发货单号</th>
                            <th>销售订单</th>
                            <th>仓库</th>
                            <th>发货日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr>
                                <td class="font-monospace small">{{ $shipment->shipment_no }}</td>
                                <td>{{ $shipment->salesOrder?->order_no ?? '-' }}</td>
                                <td>{{ $shipment->warehouse?->name ?? '-' }}</td>
                                <td>{{ $shipment->shipment_date }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $shipment->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.sales.shipments.show', $shipment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无发货单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
@endsection
