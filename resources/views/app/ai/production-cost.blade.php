@extends('layouts.business')
@section('title', '生产成本分析')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">💰 生产成本分析</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <form method="GET" class="mb-6 flex gap-2">
        <select name="order_id" class="border rounded px-3 py-2">
            <option value="">选择已完工工单...</option>
            @foreach($orders as $o)
            <option value="{{ $o->id }}" {{ request('order_id') == $o->id ? 'selected' : '' }}>{{ $o->order_no }} - {{ $o->item?->name }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">分析</button>
    </form>
    @if($result)
    <div class="grid grid-cols-3 gap-4 mb-4 text-center">
        <div class="border rounded p-4"><p class="text-2xl">¥{{ number_format($result['order']->planned_cost) }}</p><p class="text-xs text-gray-500">计划成本</p></div>
        <div class="border rounded p-4"><p class="text-2xl">¥{{ number_format($result['order']->actual_cost) }}</p><p class="text-xs text-gray-500">实际成本</p></div>
        <div class="border rounded p-4"><p class="text-2xl {{ $result['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $result['variancePct'] }}%</p><p class="text-xs text-gray-500">差异</p></div>
    </div>
    <div class="bg-blue-50 border rounded p-4 text-sm">{{ $result['aiAnalysis'] }}</div>
    @endif
</div>
@endsection
