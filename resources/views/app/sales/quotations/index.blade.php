@extends('layouts.business')

@section('title', '报价单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-file-lines"></i>报价单</h2>
        <a href="{{ route('app.sales.quotations.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建报价单
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>报价单号</th>
                            <th>客户</th>
                            <th>报价日期</th>
                            <th>有效期至</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            <tr>
                                <td class="font-monospace small">{{ $quotation->quote_no }}</td>
                                <td>{{ $quotation->customer?->name }}</td>
                                <td>{{ $quotation->quote_date }}</td>
                                <td>{{ $quotation->valid_until }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $quotation->status?->label() ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.sales.quotations.show', $quotation) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无报价单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $quotations->links() }}
            </div>
        </div>
    </div>
@endsection
