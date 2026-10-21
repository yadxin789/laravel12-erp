@extends('layouts.business')

@section('title', '新建成品入库单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建成品入库单</h2>
    <form method="POST" action="{{ route('app.production.receipts.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">生产工单</label>
                <select name="production_order_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择工单</option>
                    @foreach($orders as $order)
                    <option value="{{ $order->id }}">{{ $order->order_no }} - {{ $order->item?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">入库日期</label>
                <input type="date" name="receipt_date" class="w-full px-4 py-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">仓库</label>
                <select name="warehouse_id" class="w-full px-4 py-2 border rounded" required>
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
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">确认入库</button>
    </form>
</div>
@endsection
