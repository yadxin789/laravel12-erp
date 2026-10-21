@extends('layouts.business')

@section('title', '库存查询')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">库存查询</h2>
    <form method="GET" action="" class="grid grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 mb-2">物料</label>
            <select name="item_id" class="w-full px-4 py-2 border rounded">
                <option value="">全部物料</option>
                @foreach($items as $item)
                <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-2">仓库</label>
            <select name="warehouse_id" class="w-full px-4 py-2 border rounded">
                <option value="">全部仓库</option>
                @foreach($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-gray-700 mb-2">批次号</label>
            <input type="text" name="batch_number" placeholder="批次号" class="w-full px-4 py-2 border rounded">
        </div>
    </form>
    <table class="w-full">
        <thead>
            <tr class="border-b">
                <th class="text-left py-3">物料编码</th>
                <th class="text-left py-3">物料名称</th>
                <th class="text-left py-3">规格</th>
                <th class="text-left py-3">仓库</th>
                <th class="text-right py-3">批次号</th>
                <th class="text-right py-3">数量</th>
                <th class="text-right py-3">预留</th>
                <th class="text-right py-3">冻结</th>
                <th class="text-right py-3">可用</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($balances) && $balances->count() > 0)
            @foreach($balances as $balance)
            <tr class="border-b">
                <td class="py-3">{{ $balance->item?->code }}</td>
                <td class="py-3">{{ $balance->item?->name }}</td>
                <td class="py-3">{{ $balance->item?->spec }}</td>
                <td class="py-3">{{ $balance->warehouse?->name }}</td>
                <td class="py-3 text-right">{{ $balance->batch_number ?? '-' }}</td>
                <td class="py-3 text-right">{{ $balance->quantity }}</td>
                <td class="py-3 text-right">{{ $balance->reserved_quantity }}</td>
                <td class="py-3 text-right">{{ $balance->frozen_quantity }}</td>
                <td class="py-3 text-right">{{ $balance->available_quantity }}</td>
            </tr>
            @endforeach
            @else
            <tr class="border-b">
                <td colspan="9" class="py-3 text-center">暂无数据</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
