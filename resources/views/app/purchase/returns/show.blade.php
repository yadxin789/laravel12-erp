@extends('layouts.business')

@section('title', '采购退货详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-right-left"></i>采购退货详情 #{{ $return->return_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($return->status !== 'approved')
                <form action="{{ route('app.purchase.returns.approve', $return) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>审核通过</button>
                </form>
            @endif
            <a href="{{ route('app.purchase.returns.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">退货单号</div>
                    <div class="fw-semibold font-monospace">{{ $return->return_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">采购订单</div>
                    <div class="fw-semibold">{{ $return->purchaseOrder?->order_no ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">供应商</div>
                    <div class="fw-semibold">{{ $return->supplier?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $return->status ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">退货日期</div>
                    <div class="fw-semibold">{{ $return->return_date ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">退货金额</div>
                    <div class="fw-semibold">¥{{ number_format($return->total_amount ?? 0, 2) }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">退货原因</div>
                    <div class="fw-semibold">{{ $return->reason ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $return->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($return->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>退货明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">数量</th>
                                <th class="text-end">单价</th>
                                <th class="text-end">金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->lines as $line)
                                <tr>
                                    <td>{{ $line->item?->name }}</td>
                                    <td class="text-end">{{ $line->quantity }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end">{{ number_format($line->quantity * ($line->unit_price ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
