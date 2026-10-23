@extends('layouts.business')

@section('title', '销售漏斗')

@section('content')
    @php
        $totalValue = collect($pipeline)->sum('value');
        $totalCount = collect($pipeline)->sum('count');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-filter"></i>销售漏斗</h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('app.crm.leads') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-user-plus me-1"></i>线索
            </a>
            <a href="{{ route('app.crm.opportunities') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-bullseye me-1"></i>商机
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">商机总数</div>
                    <div class="fs-3 fw-bold text-primary">{{ $totalCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-info-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">预计总金额</div>
                    <div class="fs-3 fw-bold text-info">¥{{ number_format($totalValue, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">已成交金额</div>
                    <div class="fs-3 fw-bold text-success">
                        ¥{{ number_format(collect($pipeline)->firstWhere('key', 'closed_won')['value'] ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach($pipeline as $stage)
            <div class="col-12 col-md-6 col-xl-4 col-xxl-2">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small">{{ $stage['label'] }}</span>
                        <span class="badge bg-primary">{{ $stage['count'] }}</span>
                    </div>
                    <div class="card-body">
                        <div class="fs-5 fw-bold text-primary mb-1">¥{{ number_format($stage['value'], 0) }}</div>
                        <div class="vstack gap-2 mt-2">
                            @forelse($stage['opps'] as $opp)
                                <div class="border rounded p-2">
                                    <div class="small fw-semibold">{{ $opp->name }}</div>
                                    <div class="text-muted small">
                                        ¥{{ number_format($opp->expected_amount ?? 0, 0) }} · {{ $opp->probability }}%
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">—</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
