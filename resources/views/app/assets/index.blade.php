@extends('layouts.business')

@section('title', '固定资产')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>固定资产</h2>
        <a href="{{ route('app.assets.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i>登记资产
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">资产数量</div>
                <div class="stat-value">{{ $stats['count'] }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">资产原值</div>
                <div class="stat-value">{{ number_format($stats['cost'], 2) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">累计折旧</div>
                <div class="stat-value text-warning">{{ number_format($stats['depreciation'], 2) }}</div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small">账面净值</div>
                <div class="stat-value text-success">{{ number_format($stats['net'], 2) }}</div>
            </div></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.assets.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="keyword">关键字</label>
                    <input type="text" id="keyword" name="keyword" class="form-control"
                           placeholder="资产编号 / 名称" value="{{ request('keyword') }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="category">分类</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">全部分类</option>
                        @foreach($categories as $c)
                            <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
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
                    <a href="{{ route('app.assets.index') }}" class="btn btn-light">重置</a>
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
                            <th>资产编号</th>
                            <th>资产名称</th>
                            <th>分类</th>
                            <th>存放位置</th>
                            <th>使用人</th>
                            <th class="text-end">原值</th>
                            <th class="text-end">月折旧</th>
                            <th class="text-end">净值</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 150px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                            @php
                                $sb = [
                                    'in_use' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                    'idle' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                                    'repair' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    'scrapped' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                ];
                            @endphp
                            <tr>
                                <td class="font-monospace small">{{ $asset->asset_no }}</td>
                                <td class="fw-semibold">
                                    <a href="{{ route('app.assets.show', $asset) }}" class="text-decoration-none">{{ $asset->name }}</a>
                                </td>
                                <td>{{ $asset->category ?: '—' }}</td>
                                <td>{{ $asset->location ?: '—' }}</td>
                                <td>{{ $asset->assignee?->name ?: '—' }}</td>
                                <td class="text-end">{{ number_format((float) $asset->original_cost, 2) }}</td>
                                <td class="text-end">{{ number_format($asset->monthlyDepreciation(), 2) }}</td>
                                <td class="text-end">{{ number_format($asset->netValue(), 2) }}</td>
                                <td class="text-center">
                                    <span class="badge border {{ $sb[$asset->status] ?? 'bg-light text-secondary border' }}">
                                        {{ $statusLabels[$asset->status] ?? $asset->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.assets.edit', $asset) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('app.assets.destroy', $asset) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('确定删除资产「{{ $asset->name }}」？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="erp-empty">暂无资产</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $assets->links() }}
            </div>
        </div>
    </div>
@endsection
