@extends('layouts.app')
@section('title', '批次追溯')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">批次追溯</h2>
    <form action="{{ route('inventory.trace') }}" method="GET" class="mb-6 flex gap-4">
        <input type="text" name="batch_number" placeholder="批次号" value="{{ request('batch_number') }}" class="border rounded px-3 py-2">
        <input type="text" name="item_id" placeholder="物料ID" value="{{ request('item_id') }}" class="border rounded px-3 py-2">
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">查询</button>
    </form>
    @if(isset($transactions) && $transactions->isNotEmpty())
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2 text-left">流水号</th>
                    <th class="border px-4 py-2 text-left">物料</th>
                    <th class="border px-4 py-2 text-left">类型</th>
                    <th class="border px-4 py-2 text-right">数量</th>
                    <th class="border px-4 py-2 text-left">批次号</th>
                    <th class="border px-4 py-2 text-left">时间</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $t->transaction_no }}</td>
                    <td class="border px-4 py-2">{{ $t->item?->name }}</td>
                    <td class="border px-4 py-2">{{ $t->transaction_type }}</td>
                    <td class="border px-4 py-2 text-right">{{ $t->quantity }}</td>
                    <td class="border px-4 py-2">{{ $t->batch_number ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $t->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-gray-500 text-center py-8">请输入查询条件进行追溯</div>
    @endif
</div>
@endsection
