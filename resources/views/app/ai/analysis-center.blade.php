@extends('layouts.business')
@section('title', 'AI 智能分析中心')
@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold mb-6">🤖 AI 智能分析中心</h2>

    <div class="grid grid-cols-3 gap-4">
        <a href="{{ route('app.ai.daily-report') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">📰</div>
            <h3 class="font-bold">经营日报</h3>
            <p class="text-xs text-gray-500 mt-1">每日销售/采购/生产/库存摘要</p>
        </a>
        <a href="{{ route('app.ai.stock-alerts') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">📦</div>
            <h3 class="font-bold">库存预警分析</h3>
            <p class="text-xs text-gray-500 mt-1">AI补货建议</p>
        </a>
        <a href="{{ route('app.ai.customer-credit') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">👤</div>
            <h3 class="font-bold">客户信用评估</h3>
            <p class="text-xs text-gray-500 mt-1">信用等级建议</p>
        </a>
        <a href="{{ route('app.ai.supplier-quality') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">🏭</div>
            <h3 class="font-bold">供应商质量评分</h3>
            <p class="text-xs text-gray-500 mt-1">月度合格率分析</p>
        </a>
        <a href="{{ route('app.ai.sales-forecast') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">📈</div>
            <h3 class="font-bold">销售预测</h3>
            <p class="text-xs text-gray-500 mt-1">未来3个月预测</p>
        </a>
        <a href="{{ route('app.ai.production-cost') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">💰</div>
            <h3 class="font-bold">生产成本分析</h3>
            <p class="text-xs text-gray-500 mt-1">计划vs实际差异</p>
        </a>
        <a href="{{ route('app.ai.anomaly-detection') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">⚠️</div>
            <h3 class="font-bold">异常订单检测</h3>
            <p class="text-xs text-gray-500 mt-1">高风险订单标记</p>
        </a>
        <a href="{{ route('app.ai.scheduling') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">📅</div>
            <h3 class="font-bold">智能排程</h3>
            <p class="text-xs text-gray-500 mt-1">工单优先顺序建议</p>
        </a>
        <a href="{{ route('app.ai.ecommerce-merge') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">🛒</div>
            <h3 class="font-bold">电商订单合并</h3>
            <p class="text-xs text-gray-500 mt-1">同买家多平台合并建议</p>
        </a>
        <a href="{{ route('app.ai.email-reply') }}" class="border rounded-lg p-4 hover:shadow-lg">
            <div class="text-2xl mb-2">📧</div>
            <h3 class="font-bold">邮件智能回复</h3>
            <p class="text-xs text-gray-500 mt-1">询盘报价草稿</p>
        </a>
    </div>
</div>
@endsection
