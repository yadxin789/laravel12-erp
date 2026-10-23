@extends('layouts.business')

@section('title', '成品入库')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>成品入库</h2>
        @can('create', \App\Models\Production\ProductionOrder::class)
        <a href="{{ route('app.production.receipts.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建入库单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>入库单号</th>
                            <th>生产工单</th>
                            <th>仓库</th>
                            <th class="text-end">数量</th>
                            <th>批次号</th>
                            <th>入库日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td class="font-monospace small">{{ $receipt->receipt_no }}</td>
                                <td>{{ $receipt->productionOrder?->order_no ?? '-' }}</td>
                                <td>{{ $receipt->warehouse?->name ?? '-' }}</td>
                                <td class="text-end">{{ $receipt->quantity }}</td>
                                <td>{{ $receipt->batch_number ?? '-' }}</td>
                                <td>{{ $receipt->receipt_date }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $receipt->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.production.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无成品入库单</td>
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
