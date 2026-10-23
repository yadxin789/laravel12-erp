@extends('layouts.business')

@section('title', '资产详情')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>资产详情</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('app.assets.edit', $asset) }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-pen me-1"></i>编辑
            </a>
            <a href="{{ route('app.assets.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>资产信息</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">资产编号</div>
                            <div class="fw-semibold font-monospace">{{ $asset->asset_no }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">资产名称</div>
                            <div class="fw-semibold">{{ $asset->name }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">分类</div>
                            <div class="fw-semibold">{{ $asset->category ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">存放位置</div>
                            <div class="fw-semibold">{{ $asset->location ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">使用人</div>
                            <div class="fw-semibold">{{ $asset->assignee?->name ?: '未领用' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">状态</div>
                            <div class="fw-semibold">{{ $statusLabels[$asset->status] ?? $asset->status }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">购置日期</div>
                            <div class="fw-semibold">{{ $asset->purchase_date }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">起折日期</div>
                            <div class="fw-semibold">{{ $asset->start_depreciation_date ?: '—' }}</div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="text-muted small">折旧年限</div>
                            <div class="fw-semibold">{{ $asset->useful_life_years }} 年</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-calculator me-2"></i>折旧信息（直线法，残值率 5%）</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted small">资产原值</div>
                            <div class="fw-semibold">{{ number_format((float) $asset->original_cost, 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">月折旧额</div>
                            <div class="fw-semibold">{{ number_format($asset->monthlyDepreciation(), 2) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">已计提月数</div>
                            <div class="fw-semibold">{{ $asset->elapsedMonths() }} 个月</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">累计折旧</div>
                            <div class="fw-semibold text-warning">{{ number_format($asset->accumulatedDepreciation(), 2) }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">账面净值</div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($asset->netValue(), 2) }}</div>
                        </div>
                    </div>

                    <hr>

                    <form method="POST" action="{{ route('app.assets.status', $asset) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-12 col-md-5">
                            <label class="form-label small mb-0">变更状态</label>
                            <select name="status" class="form-select form-select-sm">
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $asset->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-7 d-grid">
                            <button type="submit" class="btn btn-sm btn-outline-primary">更新状态</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
