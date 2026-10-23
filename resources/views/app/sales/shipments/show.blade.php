@extends('layouts.business')

@section('title', '发货单详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-truck"></i>发货单详情 #{{ $shipment->shipment_no }}</h2>
        <a href="{{ route('app.sales.shipments.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">发货单号</div>
                    <div class="fw-semibold font-monospace">{{ $shipment->shipment_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">销售订单</div>
                    <div class="fw-semibold">{{ $shipment->salesOrder?->order_no ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">客户</div>
                    <div class="fw-semibold">{{ $shipment->customer?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">仓库</div>
                    <div class="fw-semibold">{{ $shipment->warehouse?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $shipment->status ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">发货日期</div>
                    <div class="fw-semibold">{{ $shipment->shipment_date ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $shipment->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($shipment->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>发货明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">订单数量</th>
                                <th class="text-end">发货数量</th>
                                <th>批次号</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($shipment->lines as $line)
                                <tr>
                                    <td>{{ $line->item?->name }}</td>
                                    <td class="text-end">{{ $line->order_quantity ?? '-' }}</td>
                                    <td class="text-end">{{ $line->quantity }}</td>
                                    <td>{{ $line->batch_number ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
