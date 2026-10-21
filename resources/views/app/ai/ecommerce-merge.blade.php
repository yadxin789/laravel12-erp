@extends('layouts.business')
@section('title', '电商订单合并')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">🛒 电商订单智能合并</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    @if($total == 0)
    <p class="text-center text-gray-400 py-8">暂无需要合并的订单</p>
    @else
    <p class="text-blue-600 mb-4">发现 {{ $total }} 组同买家多平台订单可合并发货</p>
    <div class="space-y-4">
        @foreach($groups as $g)
        <div class="border rounded p-4">
            <h3 class="font-bold">{{ $g['buyer'] }} <span class="text-sm text-gray-500">{{ $g['phone'] }}</span></h3>
            <p class="text-sm mt-1">订单：{{ implode('、', $g['orders']) }}</p>
            <p class="text-sm">合计金额：¥{{ number_format($g['total'], 2) }}</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
