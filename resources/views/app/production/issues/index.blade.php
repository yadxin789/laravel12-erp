@extends('layouts.business')

@section('title', '领料管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-box-open"></i>领料管理</h2>
        @can('create', \App\Models\Production\ProductionOrder::class)
        <a href="{{ route('app.production.issues.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建领料单
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>领料单号</th>
                            <th>生产工单</th>
                            <th>仓库</th>
                            <th>领料日期</th>
                            <th class="text-center">状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                            <tr>
                                <td class="font-monospace small">{{ $issue->issue_no }}</td>
                                <td>{{ $issue->productionOrder?->order_no ?? '-' }}</td>
                                <td>{{ $issue->warehouse?->name ?? '-' }}</td>
                                <td>{{ $issue->issue_date }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $issue->status ?? '-' }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('app.production.issues.show', $issue) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无领料单</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $issues->links() }}
            </div>
        </div>
    </div>
@endsection
