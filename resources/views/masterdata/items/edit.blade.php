@extends('layouts.app')

@section('title', '编辑物料')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">编辑物料</h2>
        <a href="{{ route('admin.items.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">返回</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.items.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block mb-2 font-medium">物料编码 *</label>
                    <input type="text" name="code" required value="{{ $item->code }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">物料名称 *</label>
                    <input type="text" name="name" required value="{{ $item->name }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">规格型号</label>
                    <input type="text" name="spec" value="{{ $item->spec }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">类别</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">请选择</option>
                        @foreach(\App\Models\MasterData\ItemCategory::all() as $cat)
                            <option value="{{ $cat->id }}" {{ $item->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-medium">单位 *</label>
                    <select name="unit_id" required class="w-full border border-gray-300 rounded px-3 py-2">
                        <option value="">请选择</option>
                        @foreach(\App\Models\MasterData\Unit::all() as $u)
                            <option value="{{ $u->id }}" {{ $item->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block mb-2 font-medium">标准成本</label>
                    <input type="number" step="0.0001" name="standard_cost" value="{{ $item->standard_cost }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">安全库存</label>
                    <input type="number" name="safety_stock" value="{{ $item->safety_stock }}" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block mb-2 font-medium">物料图片</label>
                    @if($item->image_url)
                        <div class="mb-2">
                            <img src="{{ $item->image_url }}" class="w-20 h-20 object-cover rounded">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block mb-2 font-medium">物料描述</label>
                    <textarea name="description" class="w-full border border-gray-300 rounded px-3 py-2" rows="3">{{ $item->description }}</textarea>
                </div>
                <div class="flex gap-4 items-center">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="batch_management" value="1" {{ $item->batch_management ? 'checked' : '' }}> 批次管理
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="serial_management" value="1" {{ $item->serial_management ? 'checked' : '' }}> 序列号管理
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="quality_inspection_required" value="1" {{ $item->quality_inspection_required ? 'checked' : '' }}> 需要质检
                    </label>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">保存</button>
            </div>
        </form>
    </div>
@endsection
