@extends('layouts.business')
@section('title', '固定资产')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-4">固定资产管理</h2>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2">资产编号</th><th class="px-3 py-2">名称</th>
            <th class="px-3 py-2">类别</th><th class="px-3 py-2">原值</th>
            <th class="px-3 py-2">月折旧</th><th class="px-3 py-2">累计折旧</th>
            <th class="px-3 py-2">净值</th><th class="px-3 py-2">状态</th>
        </tr></thead>
        <tbody>
        @foreach($assets as $a)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono">{{ $a->asset_no }}</td>
                <td class="px-3 py-2">{{ $a->name }}</td>
                <td class="px-3 py-2">{{ $a->category ?: '—' }}</td>
                <td class="px-3 py-2">¥{{ number_format($a->original_cost, 2) }}</td>
                <td class="px-3 py-2">¥{{ number_format($a->monthly_depreciation, 2) }}</td>
                <td class="px-3 py-2">¥{{ number_format($a->accumulated_depreciation, 2) }}</td>
                <td class="px-3 py-2 font-medium">¥{{ number_format($a->net_value, 2) }}</td>
                <td class="px-3 py-2"><span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">{{ $a->status }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $assets->links() }}</div>
</div>
@endsection
