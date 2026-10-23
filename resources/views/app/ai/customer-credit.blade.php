@extends('layouts.business')

@section('title', '客户信用评估')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-user-shield"></i>客户信用评估</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.ai.customer-credit') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="customer_id">选择客户</label>
                    <select id="customer_id" name="customer_id" class="form-select">
                        <option value="">选择客户...</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>评估</button>
                </div>
            </form>
        </div>
    </div>

    @if($result)
        <div class="card mb-4">
            <div class="card-header"><i class="fa-solid fa-chart-column me-2"></i>客户数据统计</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">客户</div>
                        <div class="fw-semibold">{{ $result['customer']->name }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">历史订单</div>
                        <div class="fw-semibold">{{ $result['stats']['orderCount'] }} 笔 / ¥{{ number_format($result['stats']['orderTotal'] ?? 0, 2) }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">应收总额</div>
                        <div class="fw-semibold">¥{{ number_format($result['stats']['totalReceivable'] ?? 0, 2) }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">未收余额</div>
                        <div class="fw-semibold">¥{{ number_format($result['stats']['outstanding'] ?? 0, 2) }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">逾期笔数</div>
                        <div class="fw-semibold">{{ $result['stats']['overdueCount'] ?? 0 }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">当前额度</div>
                        <div class="fw-semibold">¥{{ number_format($result['customer']->credit_limit ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($result['assessment']))
            <div class="card border-primary-subtle">
                <div class="card-header"><i class="fa-solid fa-robot me-2"></i>AI 评估结果</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-primary">{{ $result['assessment']['grade'] ?? '?' }} 级</span>
                        <span class="text-muted small">建议额度：</span>
                        <span class="fw-semibold">¥{{ number_format(($result['assessment']['suggest_limit'] ?? 0) * 10000, 2) }}</span>
                    </div>
                    <p class="mb-0 text-muted">{{ $result['assessment']['reason'] ?? '' }}</p>
                </div>
            </div>
        @endif
    @endif
@endsection
