@extends('layouts.business')
@section('title', '采购统计报表')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">采购统计 {{ $year }}年</h2>
        <a href="{{ route('app.reports.index') }}" class="text-sm text-gray-500">← 返回报表中心</a>
    </div>

    <h3 class="font-bold mb-2">按月统计</h3>
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">月份</th>
            <th class="px-3 py-2 text-right">订单数</th>
            <th class="px-3 py-2 text-right">采购金额</th>
        </tr></thead>
        <tbody>
        @foreach($monthly as $m)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $m->month }}月</td>
                <td class="px-3 py-2 text-right">{{ $m->order_count }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($m->total_amount, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3 class="font-bold mb-2">供应商采购 Top 10</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">供应商</th>
            <th class="px-3 py-2 text-right">订单数</th>
            <th class="px-3 py-2 text-right">累计金额</th>
        </tr></thead>
        <tbody>
        @foreach($bySupplier as $row)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $row->supplier?->name }}</td>
                <td class="px-3 py-2 text-right">{{ $row->cnt }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($row->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
