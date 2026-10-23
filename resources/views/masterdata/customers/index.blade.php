@extends('layouts.app')

@section('title', '客户管理')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-building-user"></i>客户管理</h2>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>创建客户
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="code">客户编码</label>
                    <input type="text" id="code" name="code" value="{{ request('code') }}" class="form-control" placeholder="客户编码">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="name">客户名称</label>
                    <input type="text" id="name" name="name" value="{{ request('name') }}" class="form-control" placeholder="客户名称">
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass me-1"></i>搜索</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-light">重置</a>
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
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="font-monospace small">{{ $customer->code }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->contact_person }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->address }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline-primary">
                                            <i class="fa-regular fa-eye"></i>查看
                                        </a>
                                        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline-secondary">
                                            <i class="fa-regular fa-pen-to-square"></i>编辑
                                        </a>
                                        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('确定删除客户【{{ $customer->name }}】吗？')">
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
                                <td colspan="6" class="erp-empty">暂无客户数据</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
@endsection
