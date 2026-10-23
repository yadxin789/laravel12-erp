@extends('layouts.business')

@section('title', '生产工单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-industry"></i>生产工单</h2>
        @can('create', \App\Models\Production\ProductionOrder::class)
        <a href="{{ route('app.production.orders.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建工单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>工单号</th>
                            <th>产品</th>
                            <th class="text-end">数量</th>
                            <th>计划开始</th>
                            <th>计划结束</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="font-monospace small">{{ $order->order_no }}</td>
                                <td>{{ $order->item?->name ?? '-' }}</td>
                                <td class="text-end">{{ $order->quantity }}</td>
                                <td>{{ $order->plan_start_date ?? '-' }}</td>
                                <td>{{ $order->plan_end_date ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.production.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无生产工单</td>
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
