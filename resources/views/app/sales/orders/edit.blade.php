@extends('layouts.business')

@section('title', '编辑销售订单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">编辑销售订单 - {{ $order->order_no }}</h2>
    <form method="POST" action="{{ route('app.sales.orders.update', $order) }}">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">客户</label>
                <select name="customer_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">请选择客户</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $order->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">订单日期</label>
                <input type="date" name="order_date" class="w-full px-4 py-2 border rounded" value="{{ $order->order_date->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">交货日期</label>
                <input type="date" name="delivery_date" class="w-full px-4 py-2 border rounded" value="{{ $order->delivery_date->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">状态</label>
                <div class="px-4 py-2 bg-gray-100 rounded">
                    {{ $order->status->label() }}
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">订单明细</label>
            <div id="order-lines">
                @foreach($order->lines as $index => $line)
                <div class="grid grid-cols-5 gap-4 mb-2 items-center">
                    <select name="lines[{{ $index }}][item_id]" class="px-4 py-2 border rounded" required>
                        <option value="">选择物料</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $line->item_id == $item->id ? 'selected' : '' }}>{{ $item->name }} - {{ $item->spec }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="lines[{{ $index }}][quantity]" placeholder="数量" class="px-4 py-2 border rounded" value="{{ $line->quantity }}" required>
                    <input type="number" name="lines[{{ $index }}][unit_price]" placeholder="单价" class="px-4 py-2 border rounded" value="{{ $line->unit_price }}" required step="0.01">
                    <div class="text-sm text-gray-600">
                        小计: ¥{{ number_format($line->quantity * $line->unit_price, 2) }}
                    </div>
                    <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded">添加明细</button>
        </div>

        <div class="mb-4 p-4 bg-gray-50 rounded">
            <div class="text-lg font-bold">
                订单总额: ¥{{ number_format($order->total_amount, 2) }}
            </div>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">保存修改</button>
        <a href="{{ route('app.sales.orders.show', $order) }}" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">取消</a>
    </form>
</div>

<script>
let lineIndex = {{ $order->lines->count() }};
const items = @json($items);

function addLine() {
    const container = document.getElementById('order-lines');
    const options = items.map(item => `<option value="${item.id}">${item.name} - ${item.spec || ''}</option>`).join('');

    const html = `
        <div class="grid grid-cols-5 gap-4 mb-2 items-center">
            <select name="lines[${lineIndex}][item_id]" class="px-4 py-2 border rounded" required>
                <option value="">选择物料</option>
                ${options}
            </select>
            <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required>
            <input type="number" name="lines[${lineIndex}][unit_price]" placeholder="单价" class="px-4 py-2 border rounded" required step="0.01">
            <div class="text-sm text-gray-600"></div>
            <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    lineIndex++;
}

function removeLine(btn) {
    const container = document.getElementById('order-lines');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    } else {
        alert('至少需要一条明细行');
    }
}
</script>
@endsection
