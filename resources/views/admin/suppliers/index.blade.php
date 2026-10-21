@extends('layouts.admin')

@section('title', '供应商管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">供应商管理</h2>
        <a href="{{ route('admin.suppliers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建供应商</a>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">编码</th>
                <th class="text-left py-3">名称</th>
                <th class="text-left py-3">联系人</th>
                <th class="text-left py-3">电话</th>
                <th class="text-left py-3">地址</th>
                <th class="text-right py-3">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $supplier->code }}</td>
                <td class="py-3">{{ $supplier->name }}</td>
                <td class="py-3">{{ $supplier->contact_person }}</td>
                <td class="py-3">{{ $supplier->phone }}</td>
                <td class="py-3">{{ $supplier->address }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $suppliers->links() }}
</div>
@endsection
