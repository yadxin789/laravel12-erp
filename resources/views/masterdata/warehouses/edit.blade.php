@extends('layouts.app')
@section('title', '编辑仓库')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">编辑仓库</h2>
    <form action="{{ route('admin.warehouses.update', $warehouse) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">编码</label><input type="text" name="code" value="{{ old('code', $warehouse->code) }}" class="w-full border rounded px-3 py-2" required></div>
            <div><label class="block text-sm font-medium mb-1">名称</label><input type="text" name="name" value="{{ old('name', $warehouse->name) }}" class="w-full border rounded px-3 py-2" required></div>
            <div><label class="block text-sm font-medium mb-1">类型</label><input type="text" name="type" value="{{ old('type', $warehouse->type) }}" class="w-full border rounded px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-1">地址</label><input type="text" name="address" value="{{ old('address', $warehouse->address) }}" class="w-full border rounded px-3 py-2"></div>
        </div>
        <div class="mt-4 space-x-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">保存</button>
            <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
        </div>
    </form>
</div>
@endsection
