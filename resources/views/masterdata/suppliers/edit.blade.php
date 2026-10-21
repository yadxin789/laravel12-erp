@extends('layouts.app')
@section('title', '编辑供应商')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">编辑供应商</h2>
    <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium mb-1">编码</label><input type="text" name="code" value="{{ old('code', $supplier->code) }}" class="w-full border rounded px-3 py-2" required></div>
            <div><label class="block text-sm font-medium mb-1">名称</label><input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="w-full border rounded px-3 py-2" required></div>
            <div><label class="block text-sm font-medium mb-1">联系人</label><input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="w-full border rounded px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-1">电话</label><input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full border rounded px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-1">邮箱</label><input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full border rounded px-3 py-2"></div>
            <div><label class="block text-sm font-medium mb-1">地址</label><input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="w-full border rounded px-3 py-2"></div>
        </div>
        <div class="mt-4 space-x-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">保存</button>
            <a href="{{ route('admin.suppliers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
        </div>
    </form>
</div>
@endsection
