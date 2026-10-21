@extends('layouts.business')
@section('title', '库存报表')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">库存报表</h2>
        <a href="{{ route('app.reports.index') }}" class="text-sm text-gray-500">← 返回报表中心</a>
    </div>

    <h3 class="font-bold mb-2">库存数量 Top 20</h3>
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">物料编码</th>
            <th class="px-3 py-2 text-left">名称</th>
            <th class="px-3 py-2 text-right">总库存</th>
        </tr></thead>
        <tbody>
        @foreach($topItems as $row)
            <tr class="border-b">
                <td class="px-3 py-2 font-mono">{{ $row->item?->code }}</td>
                <td class="px-3 py-2">{{ $row->item?->name }}</td>
                <td class="px-3 py-2 text-right">{{ $row->qty }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3 class="font-bold mb-2 text-red-600">⚠️ 低库存预警</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-red-50">
            <th class="px-3 py-2 text-left">物料</th>
            <th class="px-3 py-2 text-right">当前库存</th>
            <th class="px-3 py-2 text-right">安全库存</th>
        </tr></thead>
        <tbody>
        @forelse($lowStock as $item)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $item->code }} {{ $item->name }}</td>
                <td class="px-3 py-2 text-right text-red-600">{{ $item->total_qty }}</td>
                <td class="px-3 py-2 text-right">{{ $item->safety_stock }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400">无低库存物料</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
