@extends('layouts.business')

@section('title', '销售订单详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">销售订单: {{ $order->order_no }}</h2>
        <div>
            @if($order->status === 'draft')
            <a href="{{ route('app.sales.orders.edit', $order) }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 mr-2">编辑</a>
            <form action="{{ route('app.sales.orders.submit', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">提交审核</button>
            </form>
            @elseif($order->status === 'submitted')
            <form action="{{ route('app.sales.orders.approve', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">审核通过</button>
            </form>
            @elseif($order->status === 'approved')
            <form action="{{ route('app.sales.orders.close', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">关闭订单</button>
            </form>
            @endif
            @if($order->status !== 'cancelled' && $order->status !== 'closed')
            <form action="{{ route('app.sales.orders.cancel', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">取消订单</button>
            </form>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">客户</p>
            <p class="font-medium">{{ $order->customer?->name }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">订单日期</p>
            <p class="font-medium">{{ $order->order_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">交货日期</p>
            <p class="font-medium">{{ $order->delivery_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">状态</p>
            <p class="font-medium">{{ $order->status }}</p>
        </div>
    </div>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">订单明细</h3>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">物料</th>
                <th class="text-left py-3">规格</th>
                <th class="text-right py-3">数量</th>
                <th class="text-right py-3">单价</th>
                <th class="text-right py-3">金额</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->lines as $line)
            <tr class="border-b">
                <td class="py-3">{{ $line->item?->name }}</td>
                <td class="py-3">{{ $line->item?->spec }}</td>
                <td class="py-3 text-right">{{ $line->quantity }}</td>
                <td class="py-3 text-right">{{ $line->unit_price }}</td>
                <td class="py-3 text-right">{{ $line->amount }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- AI 审批建议 --}}
    @if($order->status === 'submitted')
    <div class="mt-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
        <div class="flex justify-between items-center mb-2">
            <h3 class="font-bold text-purple-800">🤖 AI 审批建议</h3>
            <button onclick="getAiSuggestion()" class="text-sm text-purple-600 hover:underline">点击获取建议</button>
        </div>
        <div id="ai-suggestion" class="text-sm text-gray-700">
            <span class="text-gray-400">点击按钮，AI 将分析订单数据、客户信用、库存等给出审批建议</span>
        </div>
    </div>
    @endif
</div>

@include('components.attachment-section', ['model' => $order])

<script>
async function getAiSuggestion() {
    const box = document.getElementById('ai-suggestion');
    box.innerHTML = '<span class="text-blue-600">AI 分析中...</span>';
    try {
        const resp = await fetch('{{ route('app.ai.approval-suggestion') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order_type: 'sales', order_id: {{ $order->id }} })
        });
        const data = await resp.json();
        box.innerHTML = '<pre class="whitespace-pre-wrap font-sans">' + data.suggestion + '</pre>';
    } catch (e) {
        box.innerHTML = '<span class="text-red-600">获取建议失败</span>';
    }
}
</script>
@endsection
