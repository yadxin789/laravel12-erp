@extends('layouts.business')
@section('title', '异常订单检测')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">⚠️ 异常订单检测</h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="text-sm text-gray-500">← 返回</a>
    </div>
    @if($total == 0)
    <p class="text-center text-gray-400 py-8">本周未检测到异常订单</p>
    @else
    <p class="text-red-600 mb-4">检测到 {{ $total }} 个异常订单</p>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">类型</th><th class="px-3 py-2">订单号</th><th class="px-3 py-2">详情</th>
        </tr></thead>
        <tbody>
        @foreach($flags as $f)
            <tr class="border-b">
                <td class="px-3 py-2"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">{{ $f['type'] }}</span></td>
                <td class="px-3 py-2 font-mono">{{ $f['order_no'] }}</td>
                <td class="px-3 py-2 text-gray-600">{{ $f['detail'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
