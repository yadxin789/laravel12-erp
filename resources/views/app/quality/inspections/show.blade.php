@extends('layouts.business')

@section('title', '检验单详情')

@section('content')
    @php
        $result = $inspection->result;
        $resultBadge = match ($result) {
            \App\Enums\QualityResult::Pass => 'bg-success-subtle text-success-emphasis border border-success-subtle',
            \App\Enums\QualityResult::Fail => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
            \App\Enums\QualityResult::Conditional => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            \App\Enums\QualityResult::Waived => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
            default => 'bg-light text-secondary border',
        };
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-check"></i>检验单: {{ $inspection->inspection_no }}</h2>
        <a href="{{ route('app.quality.inspections.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验类型</div>
                    <div class="fw-semibold">{{ $inspection->inspection_type?->label() ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验日期</div>
                    <div class="fw-semibold">{{ $inspection->inspection_date?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">物料</div>
                    <div class="fw-semibold">{{ $inspection->item?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">批次号</div>
                    <div class="fw-semibold">{{ $inspection->batch_number ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">检验数量</div>
                    <div class="fw-semibold">{{ $inspection->inspected_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">合格数量</div>
                    <div class="fw-semibold text-success">{{ $inspection->qualified_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">不合格数量</div>
                    <div class="fw-semibold text-danger">{{ $inspection->rejected_size }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">结果</div>
                    <div><span class="badge {{ $resultBadge }}">{{ $result?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div class="fw-semibold">{{ $inspection->status?->label() ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $inspection->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
