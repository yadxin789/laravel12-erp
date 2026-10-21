@extends('layouts.business')

@section('title', '新建生产工单')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">新建生产工单</h2>
    <form method="POST" action="{{ route('app.production.orders.store') }}">
        @csrf
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2">产品</label>
                <select name="item_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择产品</option>
                    @foreach($items as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->spec }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">数量</label>
                <input type="number" name="quantity" class="w-full px-4 py-2 border rounded" required min="1">
            </div>
            <div>
                <label class="block text-gray-700 mb-2">开始日期</label>
                <input type="date" name="start_date" class="w-full px-4 py-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">结束日期</label>
                <input type="date" name="end_date" class="w-full px-4 py-2 border rounded" required>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">BOM</label>
                <select name="bom_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择BOM</option>
                    @foreach($boms as $bom)
                    <option value="{{ $bom->id }}">{{ $bom->item?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2">工艺路线</label>
                <select name="routing_id" class="w-full px-4 py-2 border rounded" required>
                    <option value="">选择工艺路线</option>
                    @foreach($routings as $routing)
                    <option value="{{ $routing->id }}">{{ $routing->item?->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">创建工单</button>
    </form>
</div>
@endsection
