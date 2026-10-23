@extends('layouts.business')

@section('title', '质量报告')

@section('content')
    @php
        $total = $totalInspections ?? 0;
        $pass = $passCount ?? 0;
        $fail = $failCount ?? 0;
        $passRate = $total > 0 ? round($pass / $total * 100, 1) : 0;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-chart-column"></i>质量报告</h2>
        <a href="{{ route('app.quality.inspections.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回检验管理
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">总检验数</div>
                    <div class="fs-3 fw-bold text-primary">{{ $total }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">合格</div>
                    <div class="fs-3 fw-bold text-success">{{ $pass }}</div>
                    <div class="small text-muted">合格率 {{ $passRate }}%</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-danger-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">不合格</div>
                    <div class="fs-3 fw-bold text-danger">{{ $fail }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>检验记录</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>检验单号</th>
                            <th>检验类型</th>
                            <th>物料</th>
                            <th class="text-end">检验数量</th>
                            <th class="text-end">合格</th>
                            <th class="text-end">不合格</th>
                            <th>结果</th>
                            <th>日期</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inspections as $inspection)
                            <tr>
                                <td class="font-monospace small">{{ $inspection->inspection_no }}</td>
                                <td>{{ $inspection->inspection_type?->label() ?? '-' }}</td>
                                <td>{{ $inspection->item?->name ?? '-' }}</td>
                                <td class="text-end">{{ $inspection->inspected_size }}</td>
                                <td class="text-end text-success">{{ $inspection->qualified_size }}</td>
                                <td class="text-end text-danger">{{ $inspection->rejected_size }}</td>
                                <td>
                                    <span class="badge {{ $inspection->result === \App\Enums\QualityResult::Pass ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-danger-subtle text-danger-emphasis border border-danger-subtle' }}">
                                        {{ $inspection->result?->label() ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $inspection->inspection_date?->format('Y-m-d') ?? $inspection->created_at?->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无质量数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $inspections->links() }}
            </div>
        </div>
    </div>
@endsection
