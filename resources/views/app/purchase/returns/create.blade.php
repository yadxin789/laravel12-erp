@extends('layouts.business')

@section('title', '新建采购退货单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建采购退货单</h2>
    <form method="POST" action="{{ route('app.purchase.returns.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">采购订单</label>
                <select name="purchase_order_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择订单</option>
                    @foreach($orders as $order)
                    <option value="{{ $order->id }}">{{ $order->order_no }} - {{ $order->supplier?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">退货日期</label>
                <input type="date" name="return_date" class="w-full px-4 py-2 border rounded" required>
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
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">退货原因</label>
            <textarea name="reason" class="w-full px-4 py-2 border rounded" required rows="3"></textarea>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2">退货明细</label>
            <div id="return-lines">
                <div class="grid grid-cols-3 gap-4 mb-2">
                    <select name="lines[0][purchase_order_line_id]" class="px-4 py-2 border rounded" required>
                        <option value="">选择订单行</option>
                    </select>
                    <input type="number" name="lines[0][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required min="1">
                    <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
                </div>
            </div>
            <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded">添加明细</button>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">创建退货单</button>
    </form>
</div>

<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('return-lines');
    const html = `
        <div class="grid grid-cols-3 gap-4 mb-2">
            <select name="lines[${lineIndex}][purchase_order_line_id]" class="px-4 py-2 border rounded" required>
                <option value="">选择订单行</option>
            </select>
            <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required min="1">
            <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    lineIndex++;
}
function removeLine(btn) {
    btn.parentElement.remove();
}
</script>
@endsection
