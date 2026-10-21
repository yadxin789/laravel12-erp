@extends('layouts.business')
@section('title', '供应商质量评分')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">🏭 供应商质量评分</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    <form method="GET" class="mb-6 flex gap-2">
        <select name="supplier_id" class="border rounded px-3 py-2">
            <option value="">选择供应商...</option>
            @foreach($suppliers as $s)
            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">分析</button>
    </form>
    @if($result)
    <div class="grid grid-cols-3 gap-4 mb-4 text-center">
        <div class="border rounded p-4"><p class="text-2xl font-bold">{{ $result['total'] }}</p><p class="text-xs text-gray-500">本月来料批次</p></div>
        <div class="border rounded p-4"><p class="text-2xl font-bold text-green-600">{{ $result['passed'] }}</p><p class="text-xs text-gray-500">合格批次</p></div>
        <div class="border rounded p-4"><p class="text-2xl font-bold {{ $result['passRate'] >= 95 ? 'text-green-600' : ($result['passRate'] >= 85 ? 'text-yellow-600' : 'text-red-600') }}">{{ $result['passRate'] }}%</p><p class="text-xs text-gray-500">合格率</p></div>
    </div>
    <div class="bg-blue-50 border rounded p-4 text-sm">{{ $result['aiAnalysis'] }}</div>
    @endif
</div>
@endsection
