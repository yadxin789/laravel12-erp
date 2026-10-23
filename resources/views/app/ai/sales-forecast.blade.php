@extends('layouts.business')

@section('title', '销售预测')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-chart-line"></i>销售预测</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-clock-rotate-left me-2"></i>近 12 个月历史</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>月份</th>
                            <th class="text-end">销售额</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $h)
                            <tr>
                                <td>{{ $h->month }}</td>
                                <td class="text-end">¥{{ number_format($h->total ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="erp-empty">暂无历史销售数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(!empty($forecast))
        <div class="card border-primary-subtle">
            <div class="card-header"><i class="fa-solid fa-robot me-2"></i>AI 预测（未来 3 个月）</div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted">趋势：</span>
                    <span class="fw-semibold">{{ $forecast['trend'] ?? '—' }}</span>
                </div>
                <p class="text-muted">{{ $forecast['note'] ?? '' }}</p>

                @if(!empty($forecast['forecast']))
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>月份</th>
                                    <th class="text-end">预测金额</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($forecast['forecast'] as $f)
                                    <tr>
                                        <td>{{ $f['month'] ?? '-' }}</td>
                                        <td class="text-end">¥{{ number_format(($f['amount'] ?? 0) * 10000, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
