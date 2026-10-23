@extends('layouts.admin')

@section('title', '仓库管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-warehouse"></i>仓库管理</h2>
        @can('create warehouses')
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建仓库
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>名称</th>
                            <th>类型</th>
                            <th>地址</th>
                            <th class="text-end" style="width: 160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td class="font-monospace small">{{ $warehouse->code }}</td>
                                <td>{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->warehouse_type }}</td>
                                <td>{{ $warehouse->address }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        @can('edit warehouses', $warehouse)
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="erp-empty">暂无仓库数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $warehouses->links() }}
            </div>
        </div>
    </div>
@endsection
