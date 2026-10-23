@extends('layouts.business')

@section('title', '请购单')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-clipboard-list"></i>请购单</h2>
        <a href="{{ route('app.purchase.requisitions.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建请购单
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>请购单号</th>
                            <th>仓库</th>
                            <th>请购日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requisitions as $requisition)
                            <tr>
                                <td class="font-monospace small">{{ $requisition->req_no }}</td>
                                <td>{{ $requisition->warehouse?->name ?? '-' }}</td>
                                <td>{{ $requisition->requisition_date ?? $requisition->created_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $requisition->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.purchase.requisitions.show', $requisition) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无请购单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $requisitions->links() }}
            </div>
        </div>
    </div>
@endsection
