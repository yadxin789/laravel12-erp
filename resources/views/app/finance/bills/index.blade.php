@extends('layouts.business')
@section('title', '应付管理')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">供应商账单（应付）</h2>
        <a href="{{ route('app.finance.bills.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">新建账单</a>
    </div>
    <table class="w-full">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">账单号</th>
            <th class="px-3 py-2 text-left">供应商</th>
            <th class="px-3 py-2 text-left">日期</th>
            <th class="px-3 py-2 text-right">金额</th>
            <th class="px-3 py-2 text-right">已付</th>
            <th class="px-3 py-2 text-right">余额</th>
            <th class="px-3 py-2 text-center">状态</th>
        </tr></thead>
        <tbody>
        @foreach($bills as $bill)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono text-sm">{{ $bill->bill_no }}</td>
                <td class="px-3 py-2">{{ $bill->supplier?->name }}</td>
                <td class="px-3 py-2">{{ $bill->bill_date }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($bill->total_amount, 2) }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($bill->paid_amount, 2) }}</td>
                <td class="px-3 py-2 text-right font-medium">¥{{ number_format($bill->total_amount - $bill->paid_amount, 2) }}</td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-1 rounded text-xs
                        {{ $bill->status === 'paid' ? 'bg-green-100 text-green-800' : ($bill->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $bill->status }}
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $bills->links() }}</div>
</div>
@endsection
