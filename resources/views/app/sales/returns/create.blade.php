@extends('layouts.business')
@section('title', '创建销售退货单')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">创建销售退货单</h2>
    <form action="{{ route('app.sales.returns.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">销售订单</label>
                <select name="sales_order_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">请选择</option>
                    @foreach(\App\Models\Sales\SalesOrder::all() as $order)
                        <option value="{{ $order->id }}" {{ old('sales_order_id') == $order->id ? 'selected' : '' }}>{{ $order->order_no }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">仓库</label>
                <select name="warehouse_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">请选择</option>
                    @foreach(\App\Models\MasterData\Warehouse::all() as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">退货日期</label>
                <input type="date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">退货原因</label>
                <select name="reason" class="w-full border rounded px-3 py-2">
                    <option value="">请选择</option>
                    <option value="质量不良">质量不良</option>
                    <option value="发错货">发错货</option>
                    <option value="数量不符">数量不符</option>
                    <option value="其他">其他</option>
                </select>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">备注</label>
                <textarea name="remark" class="w-full border rounded px-3 py-2" rows="2">{{ old('remark') }}</textarea>
            </div>
        </div>
        <div class="mt-4 space-x-2">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">保存</button>
            <a href="{{ route('app.sales.returns.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">返回</a>
        </div>
    </form>
</div>
@endsection
