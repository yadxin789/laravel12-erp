@extends('layouts.business')

@section('title', '生产工单详情')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">生产工单: {{ $order->order_no }}</h2>
        <div>
            @if($order->status === 'draft')
            <form action="{{ route('app.production.orders.release', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">下达工单</button>
            </form>
            @elseif($order->status === 'material_shortage')
            <form action="{{ route('app.production.orders.release', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 mr-2">重新下达（已补料）</button>
            </form>
            @elseif($order->status === 'ready')
            <form action="{{ route('app.production.orders.start', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mr-2">开工生产</button>
            </form>
            @elseif($order->status === 'in_progress')
            <form action="{{ route('app.production.orders.complete', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 mr-2">完工入库</button>
            </form>
            @elseif($order->status === 'completed')
            <form action="{{ route('app.production.orders.close', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 mr-2">关闭工单</button>
            </form>
            @endif
            @if($order->status !== 'closed' && $order->status !== 'cancelled')
            <form action="{{ route('app.production.orders.cancel', $order) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">取消工单</button>
            </form>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div>
            <p class="text-gray-500 text-sm">产品</p>
            <p class="font-medium">{{ $order->item?->name }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">数量</p>
            <p class="font-medium">{{ $order->quantity }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">已完成</p>
            <p class="font-medium">{{ $order->completed_quantity }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">状态</p>
            <p class="font-medium">{{ $order->status }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">开始日期</p>
            <p class="font-medium">{{ $order->start_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">结束日期</p>
            <p class="font-medium">{{ $order->end_date }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">BOM</p>
            <p class="font-medium">{{ $order->bom?->item?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-sm">工艺路线</p>
            <p class="font-medium">{{ $order->routing?->item?->name ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
