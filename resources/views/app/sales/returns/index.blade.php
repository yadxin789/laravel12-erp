@extends('layouts.business')
@section('title', '销售退货管理')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">销售退货管理</h2>
        <a href="{{ route('app.sales.returns.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">新建退货单</a>
    </div>
    <div class="mb-4 flex gap-4">
        <input type="text" name="search" placeholder="搜索退货单号..." class="border rounded px-3 py-2">
        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">搜索</button>
    </div>
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-4 py-2 text-left">退货单号</th>
                <th class="border px-4 py-2 text-left">销售订单</th>
                <th class="border px-4 py-2 text-left">客户</th>
                <th class="border px-4 py-2 text-right">数量</th>
                <th class="border px-4 py-2 text-left">状态</th>
                <th class="border px-4 py-2 text-left">退货日期</th>
                <th class="border px-4 py-2 text-left">操作</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $return)
            <tr class="hover:bg-gray-50">
                <td class="border px-4 py-2">{{ $return->return_no }}</td>
                <td class="border px-4 py-2">{{ $return->salesOrder?->order_no ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $return->customer?->name ?? '-' }}</td>
                <td class="border px-4 py-2 text-right">{{ $return->lines_sum_quantity ?? $return->lines?->sum('quantity') ?? 0 }}</td>
                <td class="border px-4 py-2">{{ $return->status ?? '-' }}</td>
                <td class="border px-4 py-2">{{ $return->return_date ?? '-' }}</td>
                <td class="border px-4 py-2">
                    <a href="{{ route('app.sales.returns.show', $return) }}" class="text-blue-500 hover:underline">查看</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="border px-4 py-8 text-center text-gray-500">暂无退货记录</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">{{ $returns->links() }}</div>
</div>
@endsection
