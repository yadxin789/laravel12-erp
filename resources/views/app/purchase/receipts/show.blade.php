@extends('layouts.business')

@section('title', '收货单详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-packing"></i>收货单详情 #{{ $receipt->receipt_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($receipt->status !== 'confirmed')
                <form action="{{ route('app.purchase.receipts.confirm', $receipt) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确认收货后将增加库存，确定继续吗？')">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>确认收货</button>
                </form>
            @endif
            <a href="{{ route('app.purchase.receipts.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">收货单号</div>
                    <div class="fw-semibold font-monospace">{{ $receipt->receipt_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">采购订单</div>
                    <div class="fw-semibold">{{ $receipt->purchaseOrder?->order_no ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">仓库</div>
                    <div class="fw-semibold">{{ $receipt->warehouse?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $receipt->status ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">收货日期</div>
                    <div class="fw-semibold">{{ $receipt->receipt_date ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $receipt->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($receipt->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>收货明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">订单数量</th>
                                <th class="text-end">收货数量</th>
                                <th>批次号</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipt->lines as $line)
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
