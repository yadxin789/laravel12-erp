@extends('layouts.app')

@section('title', '创建物料')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">创建物料</h2>
        <a href="{{ route('admin.items.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">返回</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.items.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 font-medium">物料编码 *</label>
                    <input type="text" name="code" required value="{{ old('code') }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">物料名称 *</label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">规格型号</label>
                    <input type="text" name="spec" value="{{ old('spec') }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">类别</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">请选择</option>
                        @foreach(\App\Models\MasterData\ItemCategory::all() as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-medium">单位 *</label>
                    <select name="unit_id" required class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">请选择</option>
                        @foreach(\App\Models\MasterData\Unit::all() as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-medium">标准成本</label>
                    <input type="number" step="0.0001" name="standard_cost" value="{{ old('standard_cost', 0) }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">安全库存</label>
                    <input type="number" name="safety_stock" value="{{ old('safety_stock', 0) }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">物料图片</label>
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block mb-2 font-medium">物料描述</label>
                    <textarea name="description" class="w-full border border-gray-300 rounded px-3 py-2" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="flex gap-4 items-center">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="batch_management" value="1"> 批次管理
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="serial_management" value="1"> 序列号管理
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="quality_inspection_required" value="1"> 需要质检
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">保存</button>
            </div>
        </form>
    </div>
@endsection
