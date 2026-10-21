@extends('layouts.business')
@section('title', '应收管理')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">客户发票（应收）</h2>
        <a href="{{ route('app.finance.invoices.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">新建发票</a>
    </div>
    <table class="w-full">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">发票号</th>
            <th class="px-3 py-2 text-left">客户</th>
            <th class="px-3 py-2 text-left">日期</th>
            <th class="px-3 py-2 text-right">金额</th>
            <th class="px-3 py-2 text-right">已收</th>
            <th class="px-3 py-2 text-right">余额</th>
            <th class="px-3 py-2 text-center">状态</th>
        </tr></thead>
        <tbody>
        @foreach($invoices as $inv)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono text-sm">{{ $inv->invoice_no }}</td>
                <td class="px-3 py-2">{{ $inv->customer?->name }}</td>
                <td class="px-3 py-2">{{ $inv->invoice_date }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($inv->total_amount, 2) }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($inv->paid_amount, 2) }}</td>
                <td class="px-3 py-2 text-right font-medium">¥{{ number_format($inv->total_amount - $inv->paid_amount, 2) }}</td>
                <td class="px-3 py-2 text-center">
                    <span class="px-2 py-1 rounded text-xs
                        {{ $inv->status === 'paid' ? 'bg-green-100 text-green-800' : ($inv->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ $inv->status }}
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
@endsection
