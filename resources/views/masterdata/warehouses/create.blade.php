@extends('layouts.app')

@section('title', '创建仓库')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">创建仓库</h2>
        <a href="{{ route('admin.warehouses.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">返回</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.warehouses.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 font-medium">仓库编码</label>
                    <input type="text" name="code" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">仓库名称</label>
                    <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">仓库类型</label>
                    <select name="type" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="raw_material">原材料仓</option>
                        <option value="work_in_progress">半成品仓</option>
                        <option value="finished_goods">成品仓</option>
                        <option value="waste">废品仓</option>
                    </select>
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
