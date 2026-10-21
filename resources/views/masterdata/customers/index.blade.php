@extends('layouts.app')

@section('title', '客户管理')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">客户管理</h2>
        <a href="{{ route('admin.customers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">创建客户</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 mb-6">
            <input type="text" name="code" placeholder="客户编码" value="{{ request('code') }}" class="border border-gray-300 rounded px-3 py-2">
            <input type="text" name="name" placeholder="客户名称" value="{{ request('name') }}" class="border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">搜索</button>
        </form>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">编码</th>
                    <th class="px-4 py-2 text-left">名称</th>
                    <th class="px-4 py-2 text-left">联系人</th>
                    <th class="px-4 py-2 text-left">电话</th>
                    <th class="px-4 py-2 text-left">地址</th>
                    <th class="px-4 py-2 text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $customer->code }}</td>
                        <td class="px-4 py-2">{{ $customer->name }}</td>
                        <td class="px-4 py-2">{{ $customer->contact_person }}</td>
                        <td class="px-4 py-2">{{ $customer->phone }}</td>
                        <td class="px-4 py-2">{{ $customer->address }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-blue-600 hover:underline">查看</a>
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="text-green-600 hover:underline ml-4">编辑</a>
                            <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="inline ml-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">删除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $customers->links() }}
    </div>
@endsection
