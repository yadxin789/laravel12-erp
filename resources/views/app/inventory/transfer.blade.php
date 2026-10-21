@extends('layouts.business')

@section('title', '库存调拨')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">库存调拨</h2>
    <form method="POST" action="{{ route('app.inventory.transfer.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
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
                <label class="block text-gray-700 mb-2">转出仓库</label>
                <select name="from_warehouse_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择仓库</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">转入仓库</label>
                <select name="to_warehouse_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择仓库</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">数量</label>
                <input type="number" name="quantity" class="w-full px-4 py-2 border rounded" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">批次号</label>
                <input type="text" name="batch_number" placeholder="批次号" class="w-full px-4 py-2 border rounded">
            </div>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">确认调拨</button>
    </form>
</div>
@endsection
