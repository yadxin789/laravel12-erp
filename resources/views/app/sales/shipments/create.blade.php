@extends('layouts.business')

@section('title', '新建发货单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建发货单</h2>
    <form method="POST" action="{{ route('app.sales.shipments.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">销售订单</label>
                <select name="sales_order_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择订单</option>
                    @foreach($orders as $order)
                    <option value="{{ $order->id }}">{{ $order->order_no }} - {{ $order->customer?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">发货日期</label>
                <input type="date" name="shipment_date" class="w-full px-4 py-2 border rounded" required>
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

        {{-- 扫码录入区 --}}
        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded">
            <label class="block text-gray-700 mb-2 font-medium">扫码查找物料（扫码枪/手动输入条码后回车）</label>
            <div class="flex gap-2">
                <input type="text" id="scan-input" placeholder="扫描条码或物料编码..." class="flex-1 px-4 py-2 border rounded font-mono" autofocus>
                <button type="button" id="scan-btn" class="bg-yellow-600 text-white px-6 py-2 rounded">查找</button>
            </div>
            <div id="scan-result" class="mt-2 text-sm"></div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 mb-2">发货明细</label>
            <div id="shipment-lines">
                <div class="grid grid-cols-3 gap-4 mb-2">
                    <select name="lines[0][sales_order_line_id]" class="px-4 py-2 border rounded" required>
                        <option value="">选择订单行</option>
                    </select>
                    <input type="number" name="lines[0][quantity]" placeholder="数量" class="px-4 py-2 border rounded" required min="1">
                    <button type="button" onclick="removeLine(this)" class="bg-red-500 text-white px-4 py-2 rounded">删除</button>
                </div>
            </div>
            <button type="button" onclick="addLine()" class="bg-gray-600 text-white px-4 py-2 rounded">添加明细</button>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">创建发货单</button>
    </form>
</div>

<script>
let lineIndex = 1;
function addLine() {
    const container = document.getElementById('shipment-lines');
    const html = `
        <div class="grid grid-cols-3 gap-4 mb-2">
            <select name="lines[${lineIndex}][sales_order_line_id]" class="px-4 py-2 border rounded" required>
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

// 扫码查找物料
const scanInput = document.getElementById('scan-input');
const scanBtn = document.getElementById('scan-btn');
const scanResult = document.getElementById('scan-result');

async function doScan() {
    const barcode = scanInput.value.trim();
    if (!barcode) return;
    scanResult.innerHTML = '<span class="text-blue-600">查找中...</span>';
    try {
        const resp = await fetch(`/api/items/by-barcode/${encodeURIComponent(barcode)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const data = await resp.json();
        if (data.error) {
            scanResult.innerHTML = `<span class="text-red-600">未找到物料: ${data.error}</span>`;
        } else {
            scanResult.innerHTML = `
                <div class="bg-green-50 p-2 rounded border border-green-200">
                    <b>${data.name}</b> (${data.code})<br>
                    规格: ${data.spec || '—'} | 类别: ${data.category || '—'} | 单位: ${data.unit || '—'}
                </div>
            `;
            const firstQty = document.querySelector('input[name$="[quantity]"]');
            if (firstQty) firstQty.focus();
        }
    } catch (e) {
        scanResult.innerHTML = '<span class="text-red-600">查询失败</span>';
    }
    scanInput.value = '';
}
scanBtn.addEventListener('click', doScan);
scanInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); doScan(); }
});
</script>
@endsection
