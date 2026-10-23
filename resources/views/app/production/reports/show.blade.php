@extends('layouts.business')

@section('title', '生产汇报详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>生产汇报详情 #{{ $report->report_no }}</h2>
        <a href="{{ route('app.production.reports.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">汇报单号</div>
                    <div class="fw-semibold font-monospace">{{ $report->report_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">生产工单</div>
                    <div class="fw-semibold">{{ $report->productionOrder?->order_no ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">汇报日期</div>
                    <div class="fw-semibold">{{ $report->report_date ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">汇报数量</div>
                    <div class="fw-semibold">{{ $report->quantity }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">合格数量</div>
                    <div class="fw-semibold">{{ ($report->quantity ?? 0) - ($report->scrap_quantity ?? 0) - ($report->rework_quantity ?? 0) }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">返工数量</div>
                    <div class="fw-semibold">{{ $report->rework_quantity ?? 0 }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">报废数量</div>
                    <div class="fw-semibold">{{ $report->scrap_quantity ?? 0 }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $report->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
