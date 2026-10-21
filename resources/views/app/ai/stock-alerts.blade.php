@extends('layouts.business')
@section('title', '库存预警分析')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">📦 库存预警分析</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <p class="text-red-600 mb-4">{{ $summary }}</p>
    @if(!empty($items))
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">物料</th><th class="px-3 py-2 text-right">现有</th>
            <th class="px-3 py-2 text-right">安全库存</th><th class="px-3 py-2 text-right">缺口</th>
        </tr></thead>
        <tbody>
        @foreach($items as $item)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $item->code }} {{ $item->name }}</td>
                <td class="px-3 py-2 text-right">{{ $item->qty }}</td>
                <td class="px-3 py-2 text-right">{{ $item->safety_stock }}</td>
                <td class="px-3 py-2 text-right text-red-600">{{ $item->qty - $item->safety_stock }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
    @if(!empty($suggestions))
    <h3 class="font-bold mb-2">AI 补货建议</h3>
    <div class="space-y-2">
        @foreach($suggestions as $s)
        <div class="border rounded p-3 text-sm">
            <span class="font-medium">{{ $s['name'] ?? '' }}</span>
            <span class="ml-2 text-blue-600">建议补货 {{ $s['suggest_qty'] ?? '' }}</span>
            <span class="ml-2 px-2 rounded text-xs {{ ($s['urgency'] ?? '') === '高' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $s['urgency'] ?? '' }}</span>
            <p class="text-gray-500 mt-1 text-xs">{{ $s['note'] ?? '' }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
