@extends('layouts.business')

@section('title', '供应商质量评分')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-industry"></i>供应商质量评分</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.ai.supplier-quality') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="supplier_id">选择供应商</label>
                    <select id="supplier_id" name="supplier_id" class="form-select">
                        <option value="">选择供应商...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
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
                        <div class="fs-2 fw-bold">{{ $result['total'] }}</div>
                        <div class="text-muted small">本月来料批次</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-success">{{ $result['passed'] }}</div>
                        <div class="text-muted small">合格批次</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold {{ $result['passRate'] >= 95 ? 'text-success' : ($result['passRate'] >= 85 ? 'text-warning-emphasis' : 'text-danger') }}">
                            {{ $result['passRate'] }}%
                        </div>
                        <div class="text-muted small">合格率</div>
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
