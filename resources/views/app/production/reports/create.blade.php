@extends('layouts.business')

@section('title', '新建生产汇报')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建生产汇报</h2>
    <form method="POST" action="{{ route('app.production.reports.store') }}">
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
                <label class="block text-gray-700 mb-2">工序</label>
                <input type="text" name="operation_id" placeholder="工序名称" class="w-full px-4 py-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">汇报数量</label>
                <input type="number" name="reported_quantity" class="w-full px-4 py-2 border rounded" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">不合格数量</label>
                <input type="number" name="rejected_quantity" class="w-full px-4 py-2 border rounded" min="0" value="0">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">报废数量</label>
                <input type="number" name="scrapped_quantity" class="w-full px-4 py-2 border rounded" min="0" value="0">
            </div>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">备注</label>
            <textarea name="remark" class="w-full px-4 py-2 border rounded" rows="3"></textarea>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">提交汇报</button>
    </form>
</div>
@endsection
