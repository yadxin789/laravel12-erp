@extends('layouts.business')
@section('title', '收付款记录')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">收付款记录</h2>
        <a href="{{ route('app.finance.payments.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">登记收付款</a>
    </div>
    <table class="w-full">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">单号</th>
            <th class="px-3 py-2 text-left">类型</th>
            <th class="px-3 py-2 text-left">往来单位</th>
            <th class="px-3 py-2 text-left">日期</th>
            <th class="px-3 py-2 text-right">金额</th>
            <th class="px-3 py-2 text-left">方式</th>
        </tr></thead>
        <tbody>
        @foreach($payments as $p)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono text-sm">{{ $p->payment_no }}</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-1 rounded text-xs {{ $p->type === 'receipt' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $p->type === 'receipt' ? '收款' : '付款' }}
                    </span>
                </td>
                <td class="px-3 py-2">{{ $p->customer?->name ?: $p->supplier?->name }}</td>
                <td class="px-3 py-2">{{ $p->payment_date }}</td>
                <td class="px-3 py-2 text-right font-medium">¥{{ number_format($p->amount, 2) }}</td>
                <td class="px-3 py-2 text-sm text-gray-500">{{ $p->method }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $payments->links() }}</div>
</div>
@endsection
