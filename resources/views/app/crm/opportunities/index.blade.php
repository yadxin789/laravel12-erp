@extends('layouts.business')
@section('title', '商机管理')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">商机管理</h2>
        <div class="flex gap-2">
            <a href="{{ route('app.crm.pipeline') }}" class="bg-purple-600 text-white px-4 py-2 rounded">销售漏斗</a>
            <a href="{{ route('app.crm.leads') }}" class="bg-gray-600 text-white px-4 py-2 rounded">线索列表</a>
            <a href="{{ route('app.crm.opportunities.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">新建商机</a>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">商机名称</th>
            <th class="px-3 py-2 text-left">关联线索/客户</th>
            <th class="px-3 py-2 text-right">预计金额</th>
            <th class="px-3 py-2 text-center">概率</th>
            <th class="px-3 py-2 text-left">阶段</th>
            <th class="px-3 py-2 text-left">预计成交日</th>
        </tr></thead>
        <tbody>
        @foreach($opps as $opp)
            <tr class="border-b">
                <td class="px-3 py-2 font-medium">{{ $opp->name }}</td>
                <td class="px-3 py-2">{{ $opp->lead?->name ?: $opp->customer?->name ?: '—' }}</td>
                <td class="px-3 py-2 text-right">¥{{ number_format($opp->expected_amount, 2) }}</td>
                <td class="px-3 py-2 text-center">{{ $opp->probability }}%</td>
                <td class="px-3 py-2">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $opp->stage }}</span>
                </td>
                <td class="px-3 py-2 text-gray-500">{{ $opp->expected_close_date ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $opps->links() }}</div>
</div>
@endsection
