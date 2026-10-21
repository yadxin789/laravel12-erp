@extends('layouts.app')

@section('title', '供应商管理')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">供应商管理</h2>
        <a href="{{ route('admin.suppliers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">创建供应商</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 mb-6">
            <input type="text" name="code" placeholder="供应商编码" value="{{ request('code') }}" class="border border-gray-300 rounded px-3 py-2">
            <input type="text" name="name" placeholder="供应商名称" value="{{ request('name') }}" class="border border-gray-300 rounded px-3 py-2">
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
                @foreach ($suppliers as $supplier)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $supplier->code }}</td>
                        <td class="px-4 py-2">{{ $supplier->name }}</td>
                        <td class="px-4 py-2">{{ $supplier->contact_person }}</td>
                        <td class="px-4 py-2">{{ $supplier->phone }}</td>
                        <td class="px-4 py-2">{{ $supplier->address }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('admin.suppliers.show', $supplier) }}" class="text-blue-600 hover:underline">查看</a>
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-green-600 hover:underline ml-4">编辑</a>
                            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline ml-4">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">删除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $suppliers->links() }}
    </div>
@endsection
