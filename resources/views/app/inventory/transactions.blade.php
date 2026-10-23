@extends('layouts.business')

@section('title', '库存流水')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clock-rotate-left"></i>库存流水</h2>
        <a href="{{ route('app.inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>返回库存查询
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('app.inventory.transactions') }}" class="row g-3 align-items-end">
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
                    <label class="form-label" for="transaction_type">事务类型</label>
                    <select id="transaction_type" name="transaction_type" class="form-select">
                        <option value="">全部类型</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('transaction_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>查询</button>
                    <a href="{{ route('app.inventory.transactions') }}" class="btn btn-light">重置</a>
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
                            <th>事务单号</th>
                            <th>类型</th>
                            <th>物料</th>
                            <th>仓库</th>
                            <th>批次号</th>
                            <th class="text-end">数量</th>
                            <th>来源单据</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="font-monospace small">{{ $transaction->transaction_no }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $transaction->transaction_type?->label() ?? '-' }}</span></td>
                                <td>{{ $transaction->item?->name ?? '-' }}</td>
                                <td>{{ $transaction->warehouse?->name ?? '-' }}</td>
                                <td>{{ $transaction->batch_number ?? '-' }}</td>
                                <td class="text-end">{{ $transaction->quantity }}</td>
                                <td>
                                    @if($transaction->source_document_type)
                                        <span class="badge bg-light text-secondary border">{{ $transaction->source_document_type }}</span>
                                        @if($transaction->source_document_id)
                                            #{{ $transaction->source_document_id }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="erp-empty">暂无库存流水记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
