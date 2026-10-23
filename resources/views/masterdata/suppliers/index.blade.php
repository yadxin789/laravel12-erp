@extends('layouts.app')

@section('title', '供应商管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-truck-field"></i>供应商管理</h2>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>创建供应商
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="code">供应商编码</label>
                    <input type="text" id="code" name="code" value="{{ request('code') }}" class="form-control" placeholder="供应商编码">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="name">供应商名称</label>
                    <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control" placeholder="供应商名称">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light">重置</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>编码</th>
                            <th>名称</th>
                            <th>联系人</th>
                            <th>电话</th>
                            <th>地址</th>
                            <th class="text-end" style="width: 200px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
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
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定删除供应商【{{ $supplier->name }}】吗？')">
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
