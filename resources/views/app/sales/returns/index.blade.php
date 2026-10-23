@extends('layouts.business')

@section('title', '销售退货管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-rotate-left"></i>销售退货管理</h2>
        @can('create sales returns')
        <a href="{{ route('app.sales.returns.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建退货单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('app.sales.returns.index') }}" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="search">搜索</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="搜索退货单号...">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('app.sales.returns.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>退货单号</th>
                            <th>销售订单</th>
                            <th>客户</th>
                            <th class="text-end">数量</th>
                            <th>状态</th>
                            <th>退货日期</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="font-monospace small">{{ $return->return_no }}</td>
                                <td>{{ $return->salesOrder?->order_no ?? '-' }}</td>
                                <td>{{ $return->customer?->name ?? '-' }}</td>
                                <td class="text-end">{{ $return->lines_sum_quantity ?? $return->lines?->sum('quantity') ?? 0 }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $return->status ?? '-' }}</span></td>
                                <td>{{ $return->return_date ?? '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('app.sales.returns.show', $return) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无退货记录</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $returns->links() }}
            </div>
        </div>
    </div>
@endsection
