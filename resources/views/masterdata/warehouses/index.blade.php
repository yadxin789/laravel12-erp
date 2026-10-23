@extends('layouts.app')

@section('title', '仓库管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-warehouse"></i>仓库管理</h2>
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>创建仓库
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="code">仓库编码</label>
                    <input type="text" id="code" name="code" value="{{ request('code') }}" class="form-control" placeholder="仓库编码">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="name">仓库名称</label>
                    <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control" placeholder="仓库名称">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>名称</th>
                            <th>类型</th>
                            <th>地址</th>
                            <th class="text-end" style="width: 200px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $warehouse)
                            <tr>
                                <td class="font-monospace small">{{ $warehouse->code }}</td>
                                <td>{{ $warehouse->name }}</td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis">{{ $warehouse->type_label }}</span>
                                </td>
                                <td>{{ $warehouse->address }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定删除仓库【{{ $warehouse->name }}】吗？')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="fa-regular fa-trash-can"></i>删除
                                            </button>
                                        </form>
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
