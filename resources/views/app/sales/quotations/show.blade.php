@extends('layouts.business')

@section('title', '报价单详情')

@section('content')
    @php
        use App\Enums\QuotationStatus;
        $status = $quotation->status;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-lines"></i>报价单: {{ $quotation->quote_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($status === QuotationStatus::Draft)
                <form action="{{ route('app.sales.quotations.submit', $quotation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>提交报价</button>
                </form>
            @endif
            @if($status === QuotationStatus::Submitted)
                <form action="{{ route('app.sales.quotations.convert', $quotation) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-right-left me-1"></i>生成销售订单</button>
                </form>
            @endif
            <a href="{{ route('app.sales.quotations.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">客户</div>
                    <div class="fw-semibold">{{ $quotation->customer?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">报价日期</div>
                    <div class="fw-semibold">{{ $quotation->quote_date }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">有效期至</div>
                    <div class="fw-semibold">{{ $quotation->valid_until ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $status?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">报价金额</div>
                    <div class="fw-semibold">¥{{ number_format($quotation->total_amount ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>报价明细</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotation->lines as $line)
                            <tr>
                                <td>{{ $line->item?->name }}</td>
                                <td>{{ $line->item?->spec }}</td>
                                <td class="text-end">{{ $line->quantity }}</td>
                                <td class="text-end">{{ $line->unit_price }}</td>
                                <td class="text-end">{{ $line->amount }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无报价明细</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
