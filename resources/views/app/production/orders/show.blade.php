@extends('layouts.business')

@section('title', '生产工单详情')

@section('content')
    @php
        use App\Enums\ProductionOrderStatus;
        $status = $order->status;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-industry"></i>生产工单: {{ $order->order_no }}</h2>
        <div class="d-flex flex-wrap gap-2">
            @if($status === ProductionOrderStatus::Draft)
                <form action="{{ route('app.production.orders.release', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i>下达工单</button>
                </form>
            @elseif($status === ProductionOrderStatus::MaterialShortage)
                <form action="{{ route('app.production.orders.release', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="fa-solid fa-rotate me-1"></i>重新下达（已补料）</button>
                </form>
            @elseif($status === ProductionOrderStatus::Ready)
                <form action="{{ route('app.production.orders.start', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-play me-1"></i>开工生产</button>
                </form>
            @elseif($status === ProductionOrderStatus::InProgress)
                <form action="{{ route('app.production.orders.complete', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="fa-solid fa-flag-checkered me-1"></i>完工入库</button>
                </form>
            @elseif($status === ProductionOrderStatus::Completed)
                <form action="{{ route('app.production.orders.close', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-lock me-1"></i>关闭工单</button>
                </form>
            @endif
            @if($status !== ProductionOrderStatus::Closed && $status !== ProductionOrderStatus::Cancelled)
                <form action="{{ route('app.production.orders.cancel', $order) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('确定取消该生产工单吗？')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-ban me-1"></i>取消工单</button>
                </form>
            @endif
            <a href="{{ route('app.production.orders.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>返回
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-muted small">产品</div>
                    <div class="fw-semibold">{{ $order->item?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">计划数量</div>
                    <div class="fw-semibold">{{ $order->quantity }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">已完成</div>
                    <div class="fw-semibold">{{ $order->completed_quantity }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">状态</div>
                    <div><span class="badge bg-light text-secondary border">{{ $status?->label() ?? '-' }}</span></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">计划开始</div>
                    <div class="fw-semibold">{{ $order->plan_start_date ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">计划结束</div>
                    <div class="fw-semibold">{{ $order->plan_end_date ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">BOM</div>
                    <div class="fw-semibold">{{ $order->bomHeader?->item?->name ?? '-' }}</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-muted small">工艺路线</div>
                    <div class="fw-semibold">{{ $order->routingHeader?->item?->name ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    @if($order->lines->isNotEmpty())
        <div class="card">
            <div class="card-header"><i class="fa-solid fa-list me-2"></i>工单物料明细</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>物料</th>
                                <th class="text-end">需求数量</th>
                                <th class="text-end">已领数量</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->lines as $line)
                                <tr>
                                    <td>{{ $line->item?->name }}</td>
                                    <td class="text-end">{{ $line->required_quantity }}</td>
                                    <td class="text-end">{{ $line->issued_quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
