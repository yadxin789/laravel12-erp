@extends('layouts.business')

@section('title', '电商订单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-cart-shopping"></i>电商订单</h2>
        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('app.ecommerce.sync') }}">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="fa-solid fa-arrows-rotate me-1"></i>同步平台订单
                </button>
            </form>
            <a href="{{ route('app.ecommerce.create') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus me-1"></i>手工建单
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">订单总数</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">待处理</div>
                <div class="stat-value text-warning">{{ $stats['pending'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">已发货</div>
                <div class="stat-value text-primary">{{ $stats['shipped'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">订单金额合计</div>
                <div class="stat-value">{{ number_format($stats['amount'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.ecommerce.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">关键字</label>
                    <input type="text" id="keyword" name="keyword" class="form-control"
                           placeholder="平台订单号 / 买家 / 电话" value="{{ request('keyword') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="platform">平台</label>
                    <select id="platform" name="platform" class="form-select">
                        <option value="">全部平台</option>
                        @foreach($platforms as $key => $label)
                            <option value="{{ $key }}" {{ request('platform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="status">状态</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">全部状态</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>筛选</button>
                    <a href="{{ route('app.ecommerce.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>平台</th>
                            <th>平台订单号</th>
                            <th>买家</th>
                            <th class="text-center">明细</th>
                            <th class="text-end">金额</th>
                            <th>同步时间</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $sb = [
                                    'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    'synced' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                    'shipped' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                    'completed' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                    'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                ];
                            @endphp
                            <tr>
                                <td>{{ $platforms[$order->platform] ?? $order->platform }}</td>
                                <td class="font-monospace small">
                                    <a href="{{ route('app.ecommerce.show', $order) }}" class="text-decoration-none">{{ $order->platform_order_no }}</a>
                                </td>
                                <td>
                                    {{ $order->buyer_name ?: '—' }}
                                    @if($order->buyer_phone)
                                        <div class="text-muted small">{{ $order->buyer_phone }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $order->items_count }}</td>
                                <td class="text-end">{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td class="small">{{ $order->synced_at ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $sb[$order->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.ecommerce.edit', $order) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.ecommerce.destroy', $order) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除该订单？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="erp-empty">暂无电商订单</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endsection
