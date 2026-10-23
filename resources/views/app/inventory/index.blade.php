@extends('layouts.business')

@section('title', '库存查询')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-boxes-stacked"></i>库存查询</h2>
        <div class="d-flex flex-wrap gap-2">
            @can('transfer inventory')
                <a href="{{ route('app.inventory.transfer') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-right-left me-1"></i>调拨
                </a>
            @endcan
            @can('adjust inventory')
                <a href="{{ route('app.inventory.adjust') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-sliders me-1"></i>调整
                </a>
            @endcan
            @can('freeze inventory')
                <a href="{{ route('app.inventory.freeze') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-snowflake me-1"></i>冻结/解冻
                </a>
            @endcan
            @can('stocktake inventory')
                <a href="{{ route('app.inventory.stocktake') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-clipboard-list me-1"></i>盘点
                </a>
            @endcan
            @can('trace inventory')
                <a href="{{ route('app.inventory.trace') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>批次追踪
                </a>
                <a href="{{ route('app.inventory.transactions') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i>库存流水
                </a>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.inventory.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label" for="item_id">物料</label>
                    <select id="item_id" name="item_id" class="form-select">
                        <option value="">全部物料</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="warehouse_id">仓库</label>
                    <select id="warehouse_id" name="warehouse_id" class="form-select">
                        <option value="">全部仓库</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="batch_number">批次号</label>
                    <input type="text" id="batch_number" name="batch_number" class="form-control" placeholder="批次号" value="{{ request('batch_number') }}">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
                    <a href="{{ route('app.inventory.index') }}" class="btn btn-light">重置</a>
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
                            <th>物料编码</th>
                            <th>物料名称</th>
                            <th>规格</th>
                            <th>仓库</th>
                            <th>批次号</th>
                            <th class="text-end">数量</th>
                            <th class="text-end">预留</th>
                            <th class="text-end">冻结</th>
                            <th class="text-end">可用</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $balance)
                            <tr>
                                <td class="font-monospace small">{{ $balance->item?->code }}</td>
                                <td>{{ $balance->item?->name }}</td>
                                <td>{{ $balance->item?->spec }}</td>
                                <td>{{ $balance->warehouse?->name }}</td>
                                <td>{{ $balance->batch_number ?? '-' }}</td>
                                <td class="text-end">{{ $balance->quantity }}</td>
                                <td class="text-end">{{ $balance->reserved_quantity }}</td>
                                <td class="text-end">{{ $balance->frozen_quantity }}</td>
                                <td class="text-end fw-semibold">{{ $balance->available_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="erp-empty">暂无库存数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $balances->links() }}
            </div>
        </div>
    </div>
@endsection
