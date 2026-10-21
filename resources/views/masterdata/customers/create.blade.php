@extends('layouts.app')

@section('title', '创建客户')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">创建客户</h2>
        <a href="{{ route('admin.customers.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">返回</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 font-medium">客户编码</label>
                    <input type="text" name="code" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">客户名称</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">联系人</label>
                    <input type="text" name="contact_person" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">电话</label>
                    <input type="text" name="phone" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">邮箱</label>
                    <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">地址</label>
                    <input type="text" name="address" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">保存</button>
            </div>
        </form>
    </div>
@endsection
