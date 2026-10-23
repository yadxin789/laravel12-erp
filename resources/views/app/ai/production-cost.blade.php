@extends('layouts.business')

@section('title', '生产成本分析')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-coins"></i>生产成本分析</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.ai.production-cost') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="order_id">选择已完工工单</label>
                    <select id="order_id" name="order_id" class="form-select">
                        <option value="">选择已完工工单...</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}" {{ request('order_id') == $o->id ? 'selected' : '' }}>{{ $o->order_no }} - {{ $o->item?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>分析</button>
                </div>
            </form>
        </div>
    </div>

    @if($result)
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold">¥{{ number_format($result['order']->planned_cost ?? 0, 2) }}</div>
                        <div class="text-muted small">计划成本</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold">¥{{ number_format($result['order']->actual_cost ?? 0, 2) }}</div>
                        <div class="text-muted small">实际成本</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-3 fw-bold {{ ($result['variance'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $result['variancePct'] ?? 0 }}%
                        </div>
                        <div class="text-muted small">差异</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-primary-subtle">
            <div class="card-header"><i class="fa-solid fa-robot me-2"></i>AI 分析</div>
            <div class="card-body">
                <div class="alert alert-primary mb-0" style="white-space: pre-wrap;">{{ $result['aiAnalysis'] }}</div>
            </div>
        </div>
    @endif
@endsection
