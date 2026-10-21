@extends('layouts.business')
@section('title', '客户信用评估')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">👤 客户信用评估</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <form method="GET" class="mb-6 flex gap-2">
        <select name="customer_id" class="border rounded px-3 py-2">
            <option value="">选择客户...</option>
            @foreach($customers as $c)
            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">评估</button>
    </form>

    @if($result)
    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
        <div class="border rounded p-3"><span class="text-gray-500">客户：</span>{{ $result['customer']->name }}</div>
        <div class="border rounded p-3"><span class="text-gray-500">历史订单：</span>{{ $result['stats']['orderCount'] }}笔 / ¥{{ number_format($result['stats']['orderTotal']) }}</div>
        <div class="border rounded p-3"><span class="text-gray-500">应收总额：</span>¥{{ number_format($result['stats']['totalReceivable']) }}</div>
        <div class="border rounded p-3"><span class="text-gray-500">未收余额：</span>¥{{ number_format($result['stats']['outstanding']) }}</div>
        <div class="border rounded p-3"><span class="text-gray-500">逾期笔数：</span>{{ $result['stats']['overdueCount'] }}</div>
        <div class="border rounded p-3"><span class="text-gray-500">当前额度：</span>¥{{ number_format($result['customer']->credit_limit) }}</div>
    </div>
    @if(!empty($result['assessment']))
    <div class="bg-blue-50 border rounded p-4">
        <h3 class="font-bold mb-2">AI 评估结果</h3>
        <p><span class="px-2 py-1 bg-blue-600 text-white rounded">{{ $result['assessment']['grade'] ?? '?' }} 级</span></p>
        <p class="mt-2 text-sm"><span class="text-gray-500">建议额度：</span>¥{{ number_format(($result['assessment']['suggest_limit'] ?? 0) * 10000) }}</p>
        <p class="text-sm mt-1">{{ $result['assessment']['reason'] ?? '' }}</p>
    </div>
    @endif
    @endif
</div>
@endsection
