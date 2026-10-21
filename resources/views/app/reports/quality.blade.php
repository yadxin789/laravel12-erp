@extends('layouts.business')
@section('title', '质量报表')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">质量报表 {{ $year }}年</h2>
        <a href="{{ route('app.reports.index') }}" class="text-sm text-gray-500">← 返回报表中心</a>
    </div>

    <h3 class="font-bold mb-2">按月检验结果</h3>
    <table class="w-full text-sm mb-6">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">月份</th>
            <th class="px-3 py-2 text-right">总检验</th>
            <th class="px-3 py-2 text-right">合格</th>
            <th class="px-3 py-2 text-right">不合格</th>
            <th class="px-3 py-2 text-right">合格率</th>
        </tr></thead>
        <tbody>
        @foreach($monthly as $m)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $m->month }}月</td>
                <td class="px-3 py-2 text-right">{{ $m->total }}</td>
                <td class="px-3 py-2 text-right text-green-600">{{ $m->passed }}</td>
                <td class="px-3 py-2 text-right text-red-600">{{ $m->failed }}</td>
                <td class="px-3 py-2 text-right">{{ $m->total > 0 ? round($m->passed / $m->total * 100, 1) : 0 }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3 class="font-bold mb-2">按检验类型</h3>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">检验类型</th>
            <th class="px-3 py-2 text-right">数量</th>
        </tr></thead>
        <tbody>
        @foreach($byType as $row)
            <tr class="border-b">
                <td class="px-3 py-2">{{ $row->inspection_type }}</td>
                <td class="px-3 py-2 text-right">{{ $row->total }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
