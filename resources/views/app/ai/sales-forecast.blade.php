@extends('layouts.business')
@section('title', '销售预测')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">📈 销售预测</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <h3 class="font-bold mb-2">近12个月历史</h3>
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100"><th class="px-3 py-2">月份</th><th class="px-3 py-2 text-right">销售额</th></tr></thead>
        <tbody>
        @foreach($history as $h)
            <tr class="border-b"><td class="px-3 py-2">{{ $h->month }}</td><td class="px-3 py-2 text-right">¥{{ number_format($h->total) }}</td></tr>
        @endforeach
        </tbody>
    </table>
    @if(!empty($forecast))
    <h3 class="font-bold mb-2">AI 预测（未来3个月）</h3>
    <div class="bg-blue-50 border rounded p-4">
        <p><span class="text-gray-500">趋势：</span>{{ $forecast['trend'] ?? '—' }}</p>
        <p class="mt-1">{{ $forecast['note'] ?? '' }}</p>
        @if(!empty($forecast['forecast']))
        <table class="w-full text-sm mt-3">
            @foreach($forecast['forecast'] as $f)
            <tr><td class="py-1">{{ $f['month'] }}</td><td class="text-right">¥{{ number_format(($f['amount'] ?? 0) * 10000) }}</td></tr>
            @endforeach
        </table>
        @endif
    </div>
    @endif
</div>
@endsection
