@extends('layouts.admin')

@section('title', '仓库管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">仓库管理</h2>
        <a href="{{ route('admin.warehouses.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建仓库</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">编码</th>
                <th class="text-left py-3">名称</th>
                <th class="text-left py-3">类型</th>
                <th class="text-left py-3">地址</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($warehouses as $warehouse)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $warehouse->code }}</td>
                <td class="py-3">{{ $warehouse->name }}</td>
                <td class="py-3">{{ $warehouse->warehouse_type }}</td>
                <td class="py-3">{{ $warehouse->address }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                    <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $warehouses->links() }}
</div>
@endsection
