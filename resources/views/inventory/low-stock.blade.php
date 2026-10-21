@extends('layouts.app')
@section('title', '库存预警')
@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">库存预警</h2>
    @if($items->isEmpty())
        <div class="text-gray-500 text-center py-8">暂无库存预警信息</div>
    @else
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2 text-left">物料编码</th>
                    <th class="border px-4 py-2 text-left">物料名称</th>
                    <th class="border px-4 py-2 text-right">当前库存</th>
                    <th class="border px-4 py-2 text-right">安全库存</th>
                    <th class="border px-4 py-2 text-right">短缺数量</th>
                    <th class="border px-4 py-2 text-left">仓库</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="border px-4 py-2">{{ $item->code }}</td>
                    <td class="border px-4 py-2">{{ $item->name }}</td>
                    <td class="border px-4 py-2 text-right text-red-600 font-bold">{{ $item->quantity }}</td>
                    <td class="border px-4 py-2 text-right">{{ $item->safety_stock }}</td>
                    <td class="border px-4 py-2 text-right text-red-600">{{ $item->safety_stock - $item->quantity }}</td>
                    <td class="border px-4 py-2">{{ $item->warehouse?->name ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
