@extends('layouts.business')

@section('title', '采购退货')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-right-left"></i>采购退货</h2>
        <a href="{{ route('app.purchase.returns.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建退货单
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>退货单号</th>
                            <th>采购订单</th>
                            <th>供应商</th>
                            <th>退货日期</th>
                            <th>原因</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="font-monospace small">{{ $return->return_no }}</td>
                                <td>{{ $return->purchaseOrder?->order_no ?? '-' }}</td>
                                <td>{{ $return->supplier?->name ?? '-' }}</td>
                                <td>{{ $return->return_date }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($return->reason, 30) ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $return->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.purchase.returns.show', $return) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="erp-empty">暂无采购退货单</td>
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
