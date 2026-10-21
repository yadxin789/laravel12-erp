@extends('layouts.app')
@section('title', '客户详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">客户详情</h2>
        <div class="space-x-2">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">编辑</a>
            <a href="{{ route('admin.customers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><span class="font-semibold">编码：</span>{{ $customer->code }}</div>
        <div><span class="font-semibold">名称：</span>{{ $customer->name }}</div>
        <div><span class="font-semibold">联系人：</span>{{ $customer->contact_person ?? '-' }}</div>
        <div><span class="font-semibold">电话：</span>{{ $customer->phone ?? '-' }}</div>
        <div><span class="font-semibold">邮箱：</span>{{ $customer->email ?? '-' }}</div>
        <div><span class="font-semibold">地址：</span>{{ $customer->address ?? '-' }}</div>
        <div><span class="font-semibold">创建时间：</span>{{ $customer->created_at }}</div>
        <div><span class="font-semibold">更新时间：</span>{{ $customer->updated_at }}</div>
    </div>
    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('确定删除？')" class="mt-4">
        @csrf @method('DELETE')
        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">删除</button>
    </form>
</div>
@endsection
