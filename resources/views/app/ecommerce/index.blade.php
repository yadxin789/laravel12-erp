@extends('layouts.business')
@section('title', '电商订单')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">电商平台订单</h2>
        <form method="POST" action="{{ route('app.ecommerce.sync') }}">
            @csrf
            <button class="bg-blue-600 text-white px-4 py-2 rounded">同步订单</button>
        </form>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">平台</th><th class="px-3 py-2">平台单号</th>
            <th class="px-3 py-2">买家</th><th class="px-3 py-2">金额</th>
            <th class="px-3 py-2">状态</th><th class="px-3 py-2">同步时间</th>
        </tr></thead>
        <tbody>
        @forelse($orders as $o)
            <tr class="border-b">
                <td class="px-3 py-2">
                    <span class="px-2 py-1 rounded text-xs
                        {{ $o->platform === 'taobao' ? 'bg-orange-100 text-orange-800' : '' }}
                        {{ $o->platform === 'jd' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $o->platform === 'amazon' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $o->platform === 'pdd' ? 'bg-blue-100 text-blue-800' : '' }}">
                        {{ $o->platform }}
                    </span>
                </td>
                <td class="px-3 py-2 font-mono">{{ $o->platform_order_no }}</td>
                <td class="px-3 py-2">{{ $o->buyer_name }}</td>
                <td class="px-3 py-2">¥{{ number_format($o->total_amount, 2) }}</td>
                <td class="px-3 py-2">{{ $o->status }}</td>
                <td class="px-3 py-2 text-gray-500">{{ $o->synced_at?->format('Y-m-d H:i') ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-3 py-8 text-center text-gray-400">暂无电商订单，点击"同步订单"拉取</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
