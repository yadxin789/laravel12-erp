@extends('layouts.admin')

@section('title', '客户管理')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">客户管理</h2>
        <a href="{{ route('admin.customers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">新建客户</a>
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
            @foreach($customers as $customer)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-3">{{ $customer->code }}</td>
                <td class="py-3">{{ $customer->name }}</td>
                <td class="py-3">{{ $customer->contact_person }}</td>
                <td class="py-3">{{ $customer->phone }}</td>
                <td class="py-3">{{ $customer->address }}</td>
                <td class="py-3 text-right">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800 mr-2">查看</a>
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="text-blue-600 hover:text-blue-800 mr-2">编辑</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $customers->links() }}
</div>
@endsection
