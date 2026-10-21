@extends('layouts.business')
@section('title', 'AI 开通引导')
@section('content')
<div class="max-w-4xl mx-auto">
    {{-- 头部 --}}
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold">🤖 欢迎使用 AI 智能助手</h1>
        <p class="text-gray-500 mt-2">只需 3 步，即可启用发票识别、智能问答、审批建议等 AI 能力</p>
    </div>

    {{-- 步骤指示器 --}}
    <div class="flex justify-center mb-8">
        <div class="flex items-center gap-4 text-sm">
            <span class="flex items-center gap-2"><span class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs">1</span> 选择提供商</span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs">2</span> 获取 API Key</span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-2"><span class="w-6 h-6 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs">3</span> 粘贴并测试</span>
        </div>
    </div>

    {{-- 第一步：选择提供商 --}}
    <h2 class="text-lg font-bold mb-4">第 1 步：选择 AI 提供商</h2>
    <div class="grid grid-cols-2 gap-4 mb-8">
        @foreach($providers as $key => $p)
        <div class="border-2 rounded-lg p-5 {{ $current['provider'] === $key ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
            <div class="flex justify-between items-start mb-2">
                <h3 class="font-bold text-lg">{{ $p['name'] }}</h3>
                @if($key === 'deepseek')
                <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs">⭐ 推荐</span>
                @endif
            </div>
            <p class="text-sm text-gray-600 mb-1">{{ $p['price'] }}</p>
            <p class="text-sm text-green-600 mb-2">🎁 {{ $p['free_quota'] }}</p>
            <p class="text-xs text-gray-400 mb-3">💡 {{ $p['best_for'] }}</p>
            <a href="{{ $p['docs'] }}" target="_blank" class="text-blue-600 text-sm hover:underline">
                前往 {{ explode('（', $p['name'])[0] }} 注册并获取 Key →
            </a>
        </div>
        @endforeach
    </div>

    {{-- 第二步：粘贴 Key --}}
    <h2 class="text-lg font-bold mb-4">第 2-3 步：粘贴 API Key 并测试</h2>
    <div class="bg-white rounded-lg shadow p-6">
        @if($current['enabled'] && !str_starts_with($current['api_key'] ?? '', 'sk-xxxx') && !empty($current['api_key']))
        <div class="bg-green-100 text-green-800 rounded p-4 mb-4">
            ✅ AI 功能已启用！当前使用：{{ $providers[$current['provider']]['name'] ?? $current['provider'] }}（{{ $current['model'] }}）
        </div>
        <a href="{{ route('app.ai.chat') }}" class="bg-blue-600 text-white px-6 py-2 rounded">开始使用 AI 助手 →</a>
        @else
        <form method="POST" action="{{ route('admin.ai-settings.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm mb-1">选择提供商</label>
                <select name="provider" class="w-full border rounded px-3 py-2">
                    @foreach($providers as $key => $p)
                    <option value="{{ $key }}" {{ $current['provider'] === $key ? 'selected' : '' }}>{{ $p['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm mb-1">API Key</label>
                <input type="password" name="api_key" required class="w-full border rounded px-3 py-2 font-mono" placeholder="sk-xxxxxxxxxxxxxxxx">
                <p class="text-xs text-gray-500 mt-1">在上方第1步点击对应链接即可获取</p>
            </div>
            <input type="hidden" name="enabled" value="1">
            <button class="bg-blue-600 text-white px-6 py-2 rounded">保存并启用 AI</button>
        </form>
        @endif
    </div>

    {{-- 底部提示 --}}
    <div class="mt-8 bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
        <h3 class="font-bold mb-2">💡 使用提示</h3>
        <ul class="list-disc list-inside space-y-1">
            <li>所有 AI 调用直接发送到对应厂商，ERP 系统不存储你的对话内容</li>
            <li>发票识别、智能问答、审批建议等功能会消耗 API 额度</li>
            <li>可随时在 <a href="{{ route('admin.ai-settings.index') }}" class="text-blue-600">AI 设置</a> 中更换提供商或调整模型</li>
            <li>新用户建议先用 DeepSeek，性价比最高且送 ¥10 额度</li>
        </ul>
    </div>
</div>
@endsection
