@extends('layouts.business')

@section('title', '新建生产计划')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建生产计划</h2>
    <form method="POST" action="{{ route('app.production.plans.store') }}">
        @csrf
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">计划日期</label>
                <input type="date" name="plan_date" class="w-full px-4 py-2 border rounded" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">备注</label>
                <input type="text" name="remark" class="w-full px-4 py-2 border rounded" placeholder="可选备注">
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">计划明细</label>
            <div id="plan-lines">
                <div class="grid grid-cols-6 gap-4 mb-2 items-center font-bold text-gray-600">
                    <div>物料</div>
                    <div>数量</div>
                    <div>计划日期</div>
                    <div>工作中心</div>
                    <div>对应销售订单</div>
                    <div></div>
                </div>
                <div class="grid grid-cols-6 gap-4 mb-2 items-center">
                    <select name="lines[0][item_id]" class="px-4 py-2 border rounded" required>
                        <option value="">选择物料</option>
                        @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="lines[0][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required step="0.0001">
                    <input type="date" name="lines[0][plan_date]" class="px-4 py-2 border rounded">
                    <select name="lines[0][work_center_id]" class="px-4 py-2 border rounded">
                        <option value="">选择工作中心</option>
                        @foreach($workCenters as $workCenter)
                        <option value="{{ $workCenter->id }}">{{ $workCenter->name }}</option>
                        @endforeach
                    </select>
                    <select name="lines[0][sales_order_id]" class="px-4 py-2 border rounded">
                        <option value="">选择销售订单</option>
                        @foreach($salesOrders as $salesOrder)
                        <option value="{{ $salesOrder->id }}">{{ $salesOrder->order_no }} - {{ $salesOrder->customer?->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
                </div>
            </div>
            <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded mt-2">添加明细</button>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">保存</button>
        <a href="{{ route('app.production.plans.index') }}" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">取消</a>
    </form>
</div>

<script>
let lineIndex = 1;
const items = @json($items);
const workCenters = @json($workCenters);
const salesOrders = @json($salesOrders);

function addLine() {
    const container = document.getElementById('plan-lines');

    const itemOptions = items.map(item => `<option value="${item.id}">${item.name} - ${item.spec || ''}</option>`).join('');
    const workCenterOptions = '<option value="">选择工作中心</option>' + workCenters.map(wc => `<option value="${wc.id}">${wc.name}</option>`).join('');
    const salesOrderOptions = '<option value="">选择销售订单</option>' + salesOrders.map(so => `<option value="${so.id}">${so.order_no} - ${so.customer?.name || ''}</option>`).join('');

    const html = `
        <div class="grid grid-cols-6 gap-4 mb-2 items-center">
            <select name="lines[${lineIndex}][item_id]" class="px-4 py-2 border rounded" required>
                <option value="">选择物料</option>
                ${itemOptions}
            </select>
            <input type="number" name="lines[${lineIndex}][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required step="0.0001">
            <input type="date" name="lines[${lineIndex}][plan_date]" class="px-4 py-2 border rounded">
            <select name="lines[${lineIndex}][work_center_id]" class="px-4 py-2 border rounded">
                ${workCenterOptions}
            </select>
            <select name="lines[${lineIndex}][sales_order_id]" class="px-4 py-2 border rounded">
                ${salesOrderOptions}
            </select>
            <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    lineIndex++;
}

function removeLine(btn) {
    const container = document.getElementById('plan-lines');
    // 保留标题行和至少一行明细
    if (container.children.length > 2) {
        btn.parentElement.remove();
    } else {
        alert('至少需要一条明细行');
    }
}
</script>
@endsection
