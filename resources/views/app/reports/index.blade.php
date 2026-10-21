@extends('layouts.business')
@section('title', '报表中心')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-6">📊 报表中心</h2>
    <div class="grid grid-cols-2 gap-6">
        <a href="{{ route('app.reports.sales') }}" class="block border rounded-lg p-6 hover:shadow-lg transition">
            <div class="text-4xl mb-3">💰</div>
            <h3 class="font-bold text-lg">销售统计</h3>
            <p class="text-sm text-gray-500 mt-1">按月销售额、客户排行</p>
        </a>
        <a href="{{ route('app.reports.purchase') }}" class="block border rounded-lg p-6 hover:shadow-lg transition">
            <div class="text-4xl mb-3">🛒</div>
            <h3 class="font-bold text-lg">采购统计</h3>
            <p class="text-sm text-gray-500 mt-1">按月采购额、供应商排行</p>
        </a>
        <a href="{{ route('app.reports.inventory') }}" class="block border rounded-lg p-6 hover:shadow-lg transition">
            <div class="text-4xl mb-3">📦</div>
            <h3 class="font-bold text-lg">库存报表</h3>
            <p class="text-sm text-gray-500 mt-1">库存价值Top、低库存预警</p>
        </a>
        <a href="{{ route('app.reports.quality') }}" class="block border rounded-lg p-6 hover:shadow-lg transition">
            <div class="text-4xl mb-3">✅</div>
            <h3 class="font-bold text-lg">质量报表</h3>
            <p class="text-sm text-gray-500 mt-1">检验通过率、不合格分析</p>
        </a>
    </div>
</div>
@endsection
