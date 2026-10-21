@extends('layouts.business')

@section('title', '发票识别结果')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">📄 发票识别结果</h2>
            <a href="{{ route('app.ai.invoice-ocr') }}" class="text-blue-600 hover:underline text-sm">重新上传</a>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <label class="text-gray-500 text-sm">供应商</label>
                <p class="font-medium text-lg">{{ $data['supplier_name'] ?? '—' }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">发票日期</label>
                <p class="font-medium">{{ $data['invoice_date'] ?? '—' }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">发票号码</label>
                <p class="font-medium">{{ $data['invoice_no'] ?? '—' }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">总金额</label>
                <p class="font-medium text-red-600 text-lg">¥{{ number_format($data['total_amount'] ?? 0, 2) }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">税额</label>
                <p class="font-medium">¥{{ number_format($data['tax_amount'] ?? 0, 2) }}</p>
            </div>
            <div>
                <label class="text-gray-500 text-sm">价税合计</label>
                <p class="font-medium">¥{{ number_format(($data['total_amount'] ?? 0) + ($data['tax_amount'] ?? 0), 2) }}</p>
            </div>
        </div>

        @if(!empty($data['items']))
        <h3 class="font-bold mb-2">明细行</h3>
        <table class="w-full border mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left">物料名称</th>
                    <th class="px-3 py-2 text-left">规格</th>
                    <th class="px-3 py-2 text-right">数量</th>
                    <th class="px-3 py-2 text-right">单价</th>
                    <th class="px-3 py-2 text-right">金额</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['items'] as $item)
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $item['item_name'] ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $item['spec'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $item['quantity'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $item['unit_price'] ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $item['amount'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        {{-- 原图 --}}
        @if($imagePath)
        <div class="mb-6">
            <label class="text-gray-500 text-sm block mb-2">发票原图</label>
            <img src="{{ asset('storage/' . $imagePath) }}" class="max-h-80 rounded border">
        </div>
        @endif

        {{-- 操作 --}}
        <div class="flex gap-3 pt-4 border-t">
            <a href="{{ route('app.purchase.orders.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                手动创建采购订单
            </a>
            <a href="{{ route('app.ai.invoice-ocr') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                识别下一张
            </a>
        </div>

        {{-- 原始 AI 输出（调试用） --}}
        <details class="mt-6 text-xs text-gray-400">
            <summary>查看 AI 原始输出</summary>
            <pre class="mt-2 p-3 bg-gray-50 rounded">{{ $raw }}</pre>
        </details>
    </div>
</div>
@endsection
