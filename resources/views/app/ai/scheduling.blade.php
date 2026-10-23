@extends('layouts.business')

@section('title', '智能排程建议')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-days"></i>智能排程建议</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>待开工工单（{{ $orders->count() }} 个）</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>工单号</th>
                            <th>产品</th>
                            <th class="text-end">数量</th>
                            <th>计划交期</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td class="font-monospace small">{{ $o->order_no }}</td>
                                <td>{{ $o->item?->name ?? '-' }}</td>
                                <td class="text-end">{{ $o->quantity }}</td>
                                <td>{{ $o->plan_end_date ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="erp-empty">暂无待开工工单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(!empty($suggestion))
        <div class="card border-primary-subtle">
            <div class="card-header"><i class="fa-solid fa-robot me-2"></i>AI 优先排序建议</div>
            <div class="card-body">
                <div class="vstack gap-2">
                    @foreach($suggestion as $s)
                        <div class="d-flex flex-wrap align-items-center gap-3 border rounded p-3">
                            <span class="fw-bold text-primary">#{{ $s['priority'] ?? '' }}</span>
                            <span class="font-monospace small">{{ $s['order_no'] ?? '' }}</span>
                            <span class="text-muted small">{{ $s['reason'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
