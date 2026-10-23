@extends('layouts.admin')

@section('title', '物流运单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-list-check"></i>物流运单</h2>
        <a href="{{ route('admin.logistics.settings') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-gear me-1"></i>物流设置
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logistics.shipments') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label" for="keyword">运单号</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="status">状态</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">全部</option>
                        @foreach (\App\Models\Logistics\LogisticsShipment::STATUS_LABELS as $k => $label)
                            <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-fill"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('admin.logistics.shipments') }}" class="btn btn-light">重置</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>运单号</th>
                            <th>快递公司</th>
                            <th>状态</th>
                            <th>最新轨迹</th>
                            <th>创建人</th>
                            <th>更新时间</th>
                            <th class="text-end">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shipments as $s)
                            <tr>
                                <td class="font-monospace">{{ $s->tracking_no }}</td>
                                <td>{{ $s->carrier_name ?: '—' }}</td>
                                <td><span class="badge border {{ $s->statusBadge() }}">{{ $s->statusLabel() }}</span></td>
                                <td class="small text-muted">
                                    {{ $s->latestTrace()?->description ? mb_substr($s->latestTrace()->description, 0, 30) : '—' }}
                                </td>
                                <td>{{ $s->creator?->name ?? '—' }}</td>
                                <td class="small text-muted">{{ $s->last_traced_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.inventory.logistics', ['tracking_no' => $s->tracking_no, 'carrier' => $s->carrier_key]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i>查看
                                    </a>
                                    <form method="POST" action="{{ route('admin.logistics.shipments.trace', $s) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success">
                                            <i class="fa-solid fa-rotate me-1"></i>刷新
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无运单，可在「仓库管理 → 物流查询」登记</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end p-3">
                {{ $shipments->links() }}
            </div>
        </div>
    </div>
@endsection
