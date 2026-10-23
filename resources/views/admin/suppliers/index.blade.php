@extends('layouts.admin')

@section('title', '供应商管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-truck-field"></i>供应商管理</h2>
        @can('create suppliers')
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>新建供应商
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
                            <th>联系人</th>
                            <th>电话</th>
                            <th>地址</th>
                            <th class="text-end" style="width: 160px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td class="font-monospace small">{{ $supplier->code }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->address }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        @can('edit suppliers', $supplier)
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">暂无供应商数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
@endsection
