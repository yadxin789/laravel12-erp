@extends('layouts.business')

@section('title', '新建检验单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建检验单</h2>
    <form method="POST" action="{{ route('app.quality.inspections.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">检验类型</label>
                <select name="inspection_type" class="w-full px-4 py-2 border rounded" required>
                    @foreach($types as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">检验日期</label>
                <input type="date" name="inspection_date" class="w-full px-4 py-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">物料</label>
                <select name="item_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择物料</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">批次号</label>
                <input type="text" name="batch_number" placeholder="批次号" class="w-full px-4 py-2 border rounded">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">仓库</label>
                <select name="warehouse_id" class="w-full px-4 py-2 border rounded">
                    <option value="">选择仓库</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">检验数量</label>
                <input type="number" name="inspected_size" class="w-full px-4 py-2 border rounded" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">合格数量</label>
                <input type="number" name="qualified_size" class="w-full px-4 py-2 border rounded" required min="0">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">不合格数量</label>
                <input type="number" name="rejected_size" class="w-full px-4 py-2 border rounded" required min="0">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">检验结果</label>
            <select name="result" class="w-full px-4 py-2 border rounded" required>
                <option value="qualified">合格</option>
                <option value="unqualified">不合格</option>
            </select>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">创建检验单</button>
    </form>
</div>
@endsection
