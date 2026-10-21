@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">仪表盘</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">销售订单</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $salesOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-sm mt-2">实时数据</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">采购订单</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $purchaseOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-sm mt-2">实时数据</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">生产工单</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $productionOrders }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-sm mt-2">实时数据</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">库存价值</p>
                    <p class="text-2xl font-bold text-green-600">¥ {{ number_format($inventoryValue, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-gray-400 text-sm mt-2">实时数据</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">最近订单</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">订单号</th>
                            <th class="text-left py-3 px-4">类型</th>
                            <th class="text-left py-3 px-4">客户/供应商</th>
                            <th class="text-right py-3 px-4">金额</th>
                            <th class="text-center py-3 px-4">状态</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSalesOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 text-blue-600">{{ $order->order_no }}</td>
                            <td class="py-3 px-4">销售订单</td>
                            <td class="py-3 px-4">{{ $order->customer?->name }}</td>
                            <td class="py-3 px-4 text-right">¥ {{ number_format($order->total_amount, 2) }}</td>
                            <td class="py-3 px-4 text-center"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $order->status }}</span></td>
                        </tr>
                        @endforeach
                        @foreach($recentProductionOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4 text-blue-600">{{ $order->order_no }}</td>
                            <td class="py-3 px-4">生产工单</td>
                            <td class="py-3 px-4">{{ $order->item?->name }}</td>
                            <td class="py-3 px-4 text-right">Qty: {{ $order->quantity }}</td>
                            <td class="py-3 px-4 text-center"><span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs">{{ $order->status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">快速导航</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('app.sales.orders.create') }}" class="bg-blue-50 hover:bg-blue-100 p-4 rounded-lg text-center transition-colors">
                    <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">新建销售订单</p>
                </a>
                <a href="{{ route('app.purchase.orders.create') }}" class="bg-purple-50 hover:bg-purple-100 p-4 rounded-lg text-center transition-colors">
                    <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <p class="text-sm font-medium text-purple-800">新建采购订单</p>
                </a>
                <a href="{{ route('app.production.orders.create') }}" class="bg-orange-50 hover:bg-orange-100 p-4 rounded-lg text-center transition-colors">
                    <svg class="w-8 h-8 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <p class="text-sm font-medium text-orange-800">新建生产工单</p>
                </a>
                <a href="{{ route('app.inventory.index') }}" class="bg-green-50 hover:bg-green-100 p-4 rounded-lg text-center transition-colors">
                    <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-sm font-medium text-green-800">库存查询</p>
                </a>
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mt-6 mb-4">低库存预警</h2>
            <div class="space-y-3">
                @forelse($lowStockItems as $item)
                <div class="flex items-center justify-between p-3 {{ $item->quantity < $item->safety_stock * 0.5 ? 'bg-red-50' : 'bg-yellow-50' }} rounded-lg">
                    <div>
                        <p class="font-medium text-gray-800">{{ $item->name }}</p>
                        <p class="text-sm text-gray-500">库存: {{ $item->quantity }} / 安全库存: {{ $item->safety_stock }}</p>
                    </div>
                    <span class="{{ $item->quantity < $item->safety_stock * 0.5 ? 'text-red-600' : 'text-yellow-600' }} text-sm font-medium">
                        {{ $item->quantity < $item->safety_stock * 0.5 ? '严重不足' : '即将不足' }}
                    </span>
                </div>
                @empty
                <div class="text-gray-400 text-center py-4">暂无低库存物料</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
