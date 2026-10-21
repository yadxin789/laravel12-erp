@extends('layouts.business')
@section('title', '对账单')
@section('content')
<div class="grid grid-cols-2 gap-6">
    {{-- 应收 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4 text-green-700">应收账款</h2>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50">
                <th class="px-3 py-2 text-left">客户</th>
                <th class="px-3 py-2 text-right">应收</th>
                <th class="px-3 py-2 text-right">已收</th>
                <th class="px-3 py-2 text-right">余额</th>
            </tr></thead>
            <tbody>
            @foreach($arSummary as $row)
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $row->customer?->name }}</td>
                    <td class="px-3 py-2 text-right">¥{{ number_format($row->total, 2) }}</td>
                    <td class="px-3 py-2 text-right">¥{{ number_format($row->paid, 2) }}</td>
                    <td class="px-3 py-2 text-right font-medium {{ $row->balance > 0 ? 'text-red-600' : 'text-green-600' }}">¥{{ number_format($row->balance, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 应付 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4 text-blue-700">应付账款</h2>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50">
                <th class="px-3 py-2 text-left">供应商</th>
                <th class="px-3 py-2 text-right">应付</th>
                <th class="px-3 py-2 text-right">已付</th>
                <th class="px-3 py-2 text-right">余额</th>
            </tr></thead>
            <tbody>
            @foreach($apSummary as $row)
                <tr class="border-b">
                    <td class="px-3 py-2">{{ $row->supplier?->name }}</td>
                    <td class="px-3 py-2 text-right">¥{{ number_format($row->total, 2) }}</td>
                    <td class="px-3 py-2 text-right">¥{{ number_format($row->paid, 2) }}</td>
                    <td class="px-3 py-2 text-right font-medium {{ $row->balance > 0 ? 'text-red-600' : 'text-blue-600' }}">¥{{ number_format($row->balance, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
