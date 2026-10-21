@extends('layouts.app')

@section('title', '库存查询')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">库存查询</h2>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" class="flex flex-wrap gap-4 mb-6">
            <input type="text" name="item_id" placeholder="物料ID" value="{{ request('item_id') }}" class="border border-gray-300 rounded px-3 py-2">
            <select name="warehouse_id" class="border border-gray-300 rounded px-3 py-2">
                <option value="">全部仓库</option>
            </select>
            <select name="quality_status" class="border border-gray-300 rounded px-3 py-2">
                <option value="">全部质检状态</option>
                <option value="pending">待检验</option>
                <option value="qualified">合格</option>
                <option value="unqualified">不合格</option>
            </select>
            <input type="text" name="batch_number" placeholder="批次号" value="{{ request('batch_number') }}" class="border border-gray-300 rounded px-3 py-2">
            <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">搜索</button>
        </form>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">物料编码</th>
                    <th class="px-4 py-2 text-left">物料名称</th>
                    <th class="px-4 py-2 text-left">仓库</th>
                    <th class="px-4 py-2 text-left">批次号</th>
                    <th class="px-4 py-2 text-center">数量</th>
                    <th class="px-4 py-2 text-center">预留</th>
                    <th class="px-4 py-2 text-center">冻结</th>
                    <th class="px-4 py-2 text-center">可用</th>
                    <th class="px-4 py-2 text-left">质检状态</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balances as $balance)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $balance->item?->code }}</td>
                        <td class="px-4 py-2">{{ $balance->item?->name }}</td>
                        <td class="px-4 py-2">{{ $balance->warehouse?->name }}</td>
                        <td class="px-4 py-2">{{ $balance->batch_number }}</td>
                        <td class="px-4 py-2 text-center">{{ $balance->quantity }}</td>
                        <td class="px-4 py-2 text-center">{{ $balance->reserved_quantity }}</td>
                        <td class="px-4 py-2 text-center">{{ $balance->frozen_quantity }}</td>
                        <td class="px-4 py-2 text-center">{{ $balance->available_quantity }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-xs font-medium"
                                @class([
                                    'bg-yellow-200 text-yellow-800' => $balance->quality_status === 'pending',
                                    'bg-green-200 text-green-800' => $balance->quality_status === 'qualified',
                                    'bg-red-200 text-red-800' => $balance->quality_status === 'unqualified',
                                ])>
                                {{ $balance->quality_status_label }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $balances->links() }}
    </div>
@endsection
