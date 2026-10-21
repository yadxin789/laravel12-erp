@extends('layouts.business')
@section('title', '智能排程建议')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">📅 智能排程建议</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <h3 class="font-bold mb-2">待开工工单（{{ $orders->count() }}个）</h3>
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">工单号</th><th class="px-3 py-2">产品</th>
            <th class="px-3 py-2 text-right">数量</th><th class="px-3 py-2">计划交期</th>
        </tr></thead>
        <tbody>
        @foreach($orders as $o)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono">{{ $o->order_no }}</td>
                <td class="px-3 py-2">{{ $o->item?->name }}</td>
                <td class="px-3 py-2 text-right">{{ $o->quantity }}</td>
                <td class="px-3 py-2">{{ $o->plan_end_date ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if(!empty($suggestion))
    <h3 class="font-bold mb-2">AI 优先排序建议</h3>
    <div class="space-y-2">
        @foreach($suggestion as $s)
        <div class="border rounded p-3 text-sm flex gap-4">
            <span class="font-bold text-blue-600 w-8">#{{ $s['priority'] ?? '' }}</span>
            <span class="font-mono">{{ $s['order_no'] ?? '' }}</span>
            <span class="text-gray-500">{{ $s['reason'] ?? '' }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
