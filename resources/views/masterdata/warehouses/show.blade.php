@extends('layouts.app')
@section('title', '仓库详情')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">仓库详情</h2>
        <div class="space-x-2">
            <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">编辑</a>
            <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><span class="font-semibold">编码：</span>{{ $warehouse->code }}</div>
        <div><span class="font-semibold">名称：</span>{{ $warehouse->name }}</div>
        <div><span class="font-semibold">类型：</span>{{ $warehouse->type ?? '-' }}</div>
        <div><span class="font-semibold">地址：</span>{{ $warehouse->address ?? '-' }}</div>
        <div><span class="font-semibold">创建时间：</span>{{ $warehouse->created_at }}</div>
        <div><span class="font-semibold">更新时间：</span>{{ $warehouse->updated_at }}</div>
    </div>
    <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('确定删除？')" class="mt-4">
        @csrf @method('DELETE')
        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">删除</button>
    </form>
</div>
@endsection
