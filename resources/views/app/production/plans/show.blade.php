@extends('layouts.business')

@section('title', '生产计划详情')

@section('content')
    @php
        $badge = match (true) {
            $plan->status === \App\Enums\ProductionPlanStatus::Approved => 'bg-success-subtle text-success-emphasis border border-success-subtle',
            $plan->status === \App\Enums\ProductionPlanStatus::Converted => 'bg-primary-subtle text-primary-emphasis border border-primary-subtle',
            $plan->status === \App\Enums\ProductionPlanStatus::Closed => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
            default => 'bg-light text-secondary border',
        };
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-calendar-days"></i>生产计划详情 - {{ $plan->plan_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($plan->canApprove())
                <form action="{{ route('app.production.plans.approve', $plan) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确定审批此计划吗？')">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i>审批</button>
                </form>
            @endif
            @if($plan->canConvertToOrders())
                <form action="{{ route('app.production.plans.convert', $plan) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确定将此计划转为生产工单吗？')">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-left me-1"></i>生成工单</button>
                </form>
            @endif
            @if($plan->canUpdate())
                <a href="{{ route('app.production.plans.edit', $plan) }}" class="btn btn-warning">
                    <i class="fa-regular fa-pen-to-square me-1"></i>编辑
                </a>
            @endif
            <a href="{{ route('app.production.plans.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="text-muted small">计划号</div>
                    <div class="fw-semibold font-monospace">{{ $plan->plan_no }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge {{ $badge }}">{{ $plan->status?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">计划日期</div>
                    <div class="fw-semibold">{{ $plan->plan_date?->format('Y-m-d') ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">创建人</div>
                    <div class="fw-semibold">{{ $plan->creator?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">审批人</div>
                    <div class="fw-semibold">{{ $plan->approver?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="text-muted small">审批时间</div>
                    <div class="fw-semibold">{{ $plan->approved_at?->format('Y-m-d H:i') ?? '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">备注</div>
                    <div class="fw-semibold">{{ $plan->remark ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-list me-2"></i>计划明细</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>行号</th>
                            <th>物料</th>
                            <th class="text-end">数量</th>
                            <th>计划日期</th>
                            <th>工作中心</th>
                            <th>对应销售订单</th>
                            <th>备注</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plan->lines as $line)
                            <tr>
                                <td>{{ $line->line_number }}</td>
                                <td>{{ $line->item?->name }} - {{ $line->item?->spec }}</td>
                                <td class="text-end">{{ number_format($line->quantity, 4) }}</td>
                                <td>{{ $line->plan_date ?? '-' }}</td>
                                <td>{{ $line->workCenter?->name ?? '-' }}</td>
                                <td>
                                    @if($line->salesOrder)
                                        <a href="{{ route('app.sales.orders.show', $line->salesOrder) }}">{{ $line->salesOrder->order_no }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $line->remark ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无明细数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($plan->lines->count() > 0)
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-sigma me-2"></i>汇总</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">总计明细行数</div>
                        <div class="fw-semibold">{{ $plan->lines->count() }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">总计物料种类</div>
                        <div class="fw-semibold">{{ $plan->lines->pluck('item_id')->unique()->count() }}</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small">总计数量</div>
                        <div class="fw-semibold">{{ number_format($plan->lines->sum('quantity'), 4) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
