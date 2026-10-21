@extends('layouts.admin')

@section('title', 'AI 设置')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">AI 智能助手设置</h2>
        <span class="px-3 py-1 rounded-full text-sm {{ $setting->enabled && $setting->api_key && !str_starts_with($setting->api_key, 'sk-xxxx') ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
            {{ $setting->enabled && $setting->api_key && !str_starts_with($setting->api_key, 'sk-xxxx') ? '● 已启用' : '○ 未启用' }}
        </span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.ai-settings.update') }}">
        @csrf
        @method('PUT')

        {{-- 提供商选择 --}}
        <div class="mb-6">
            <label class="block text-gray-700 mb-2 font-medium">AI 提供商</label>
            <select name="provider" id="provider-select" class="w-full px-4 py-2 border rounded max-w-lg">
                @foreach($providers as $key => $p)
                    <option value="{{ $key }}" {{ $setting->provider === $key ? 'selected' : '' }}>
                        {{ $p['name'] }}
                    </option>
                @endforeach
            </select>
            <p class="text-sm text-gray-500 mt-1">
                各平台 API Key 获取地址：
                @foreach($providers as $key => $p)
                    <a href="{{ $p['docs'] }}" target="_blank" class="text-blue-600 hover:underline">{{ $p['name'] }}</a>
                    {{ !$loop->last ? ' · ' : '' }}
                @endforeach
            </p>
        </div>

        {{-- API Key --}}
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">API Key</label>
            <input type="password" name="api_key" class="w-full px-4 py-2 border rounded max-w-lg"
                   placeholder="{{ $setting->api_key && !str_starts_with($setting->api_key, 'sk-xxxx') ? '已设置（留空保持不变）' : '请输入 API Key' }}"
                   value="">
            <p class="text-xs text-gray-500 mt-1">留空则保持当前 Key 不变</p>
        </div>

        {{-- Base URL --}}
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-medium">API 地址（Base URL）</label>
            <input type="text" name="base_url" class="w-full px-4 py-2 border rounded max-w-lg font-mono text-sm"
                   value="{{ old('base_url', $setting->base_url) }}">
        </div>

        {{-- 模型选择 --}}
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 mb-2 font-medium">对话模型</label>
                <select name="model" id="model-select" class="w-full px-4 py-2 border rounded">
                    @foreach($providers[$setting->provider]['models'] ?? [] as $k => $v)
                        <option value="{{ $k }}" {{ $setting->model === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-700 mb-2 font-medium">温度（Temperature）</label>
                <input type="number" name="temperature" step="0.1" min="0" max="2"
                       class="w-full px-4 py-2 border rounded" value="{{ $setting->temperature }}">
                <p class="text-xs text-gray-500 mt-1">0=严谨，1=均衡，2=创意</p>
            </div>
        </div>

        {{-- 视觉模型 --}}
        <div class="mb-4 p-4 bg-blue-50 rounded">
            <label class="flex items-center gap-2 mb-2">
                <input type="checkbox" name="vision_enabled" value="1" {{ $setting->vision_enabled ? 'checked' : '' }}>
                <span class="font-medium">启用视觉/OCR 能力（用于发票识别、图片理解）</span>
            </label>
            <select name="vision_model" class="w-full px-4 py-2 border rounded max-w-lg">
                @foreach($providers[$setting->provider]['vision_models'] ?? [] as $k => $v)
                    <option value="{{ $k }}" {{ $setting->vision_model === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        {{-- 高级参数 --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-gray-700 mb-2 font-medium">最大输出 Token</label>
                <input type="number" name="max_tokens" min="100" max="8000"
                       class="w-full px-4 py-2 border rounded" value="{{ $setting->max_tokens }}">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 mb-2">
                    <input type="checkbox" name="enabled" value="1" {{ $setting->enabled ? 'checked' : '' }}>
                    <span class="font-medium">启用 AI 功能</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">保存设置</button>
        </div>
    </form>

    {{-- 测试连接 --}}
    <div class="mt-8 pt-6 border-t">
        <h3 class="text-lg font-bold mb-2">连接测试</h3>
        <p class="text-sm text-gray-600 mb-3">保存设置后，点击测试按钮验证 API 连通性</p>
        <form method="POST" action="{{ route('admin.ai-settings.test') }}">
            @csrf
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">发送测试消息</button>
        </form>
    </div>
</div>

<script>
// 切换提供商时自动更新模型列表
const providerSelect = document.getElementById('provider-select');
const modelSelect = document.getElementById('model-select');
const baseUrlInput = document.querySelector('input[name="base_url"]');

const providers = @json($providers);

providerSelect.addEventListener('change', function() {
    const p = providers[this.value];
    if (!p) return;

    // 更新 Base URL
    baseUrlInput.value = p.base_url;

    // 更新对话模型列表
    modelSelect.innerHTML = '';
    for (const [k, v] of Object.entries(p.models)) {
        const opt = document.createElement('option');
        opt.value = k;
        opt.textContent = v;
        modelSelect.appendChild(opt);
    }
});
</script>
@endsection
