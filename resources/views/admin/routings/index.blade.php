@extends('layouts.admin')

@section('title', '工艺路线管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-diagram-project"></i>工艺路线管理</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>物料名称</th>
                            <th>版本</th>
                            <th>生效日期</th>
                            <th>失效日期</th>
                            <th class="text-end">工序数</th>
                            <th class="text-end">标准工时(小时)</th>
                            <th>状态</th>
                            <th class="text-end" style="width: 100px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routings as $routing)
                            <tr>
                                <td class="font-monospace small">{{ $routing->code }}</td>
                                <td>{{ $routing->item->name ?? '-' }}</td>
                                <td>{{ $routing->version }}</td>
                                <td>{{ $routing->effective_date }}</td>
                                <td>{{ $routing->expiry_date ?? '-' }}</td>
                                <td class="text-end">{{ $routing->operations->count() }}</td>
                                <td class="text-end">{{ $routing->operations->sum('standard_time') }}</td>
                                <td><span class="badge bg-light text-secondary">{{ $routing->status }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.routings.show', $routing) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-regular fa-eye"></i>查看
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="erp-empty">暂无工艺路线数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $routings->links() }}
            </div>
        </div>
    </div>
@endsection
