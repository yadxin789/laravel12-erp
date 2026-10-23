@extends('layouts.business')

@section('title', '电商订单合并')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-flatbed"></i>电商订单智能合并</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(($total ?? 0) == 0)
                <p class="erp-empty mb-0">暂无需要合并的订单</p>
            @else
                <div class="alert alert-primary"><i class="fa-solid fa-circle-info me-2"></i>发现 {{ $total }} 组同买家多平台订单可合并发货</div>
                <div class="vstack gap-3">
                    @foreach($groups as $g)
                        <div class="border rounded p-3">
                            <div class="fw-semibold">
                                {{ $g['buyer'] ?? '-' }}
                                <span class="text-muted small fw-normal">{{ $g['phone'] ?? '' }}</span>
                            </div>
                            <div class="small mt-1">订单：{{ implode('、', $g['orders'] ?? []) }}</div>
                            <div class="small">合计金额：<span class="fw-semibold">¥{{ number_format($g['total'] ?? 0, 2) }}</span></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
