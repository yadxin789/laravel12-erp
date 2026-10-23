@extends('layouts.business')

@section('title', '运单 ' . $shipment->tracking_no)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0">
            <i class="fa-solid fa-truck-fast"></i>运单 {{ $shipment->tracking_no }}
            <span class="badge border {{ $shipment->statusBadge() }} align-middle">{{ $shipment->statusLabel() }}</span>
        </h2>
        <a href="{{ route('app.inventory.logistics') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>返回查询
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-file-invoice me-2"></i>运单信息</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">运单号</span>
                            <span class="font-monospace">{{ $shipment->tracking_no }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">快递公司</span><span>{{ $shipment->carrier_name ?: '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">收件人</span><span>{{ $shipment->receiver_name ?: '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">联系电话</span><span>{{ $shipment->receiver_phone ?: '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">收件地址</span><span>{{ $shipment->receiver_address ?: '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">登记人</span><span>{{ $shipment->creator?->name ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">最近同步</span>
                            <span>{{ $shipment->last_traced_at?->format('Y-m-d H:i') ?? '—' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">签收时间</span>
                            <span>{{ $shipment->signed_at?->format('Y-m-d H:i') ?? '—' }}</span>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('admin.logistics.shipments.trace', $shipment) }}" class="mt-3">
                        @csrf
                        <button class="btn btn-outline-success btn-sm w-100">
                            <i class="fa-solid fa-rotate me-1"></i>刷新轨迹
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-route me-2"></i>物流轨迹</div>
                <div class="card-body">
                    @forelse ($shipment->traces as $t)
                        <div class="mb-3 ps-3 border-start">
                            <div class="small text-muted">{{ $t->traced_at?->format('Y-m-d H:i') ?? '—' }}</div>
                            <div>{{ $t->description }}</div>
                            @if ($t->location)
                                <div class="small text-muted">{{ $t->location }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="erp-empty mb-0">暂无轨迹，点击「刷新轨迹」获取。</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
