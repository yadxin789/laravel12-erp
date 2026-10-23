@extends('layouts.business')

@section('title', 'AI 开通引导')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="text-center mb-4">
                <h1 class="h3 fw-bold"><i class="fa-solid fa-robot me-2"></i>欢迎使用 AI 智能助手</h1>
                <p class="text-muted mt-2">只需 3 步，即可启用发票识别、智能问答、审批建议等 AI 能力</p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 small">
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-primary">1</span> 选择提供商
                        </span>
                        <span class="text-muted">→</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-secondary">2</span> 获取 API Key
                        </span>
                        <span class="text-muted">→</span>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-secondary">3</span> 粘贴并测试
                        </span>
                    </div>
                </div>
            </div>

            <h2 class="h5 fw-bold mb-3">第 1 步：选择 AI 提供商</h2>
            <div class="row g-3 mb-4">
                @foreach($providers as $key => $p)
                    <div class="col-12 col-md-6">
                        <div class="card h-100 {{ ($current['provider'] ?? '') === $key ? 'border-primary' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="fw-semibold">{{ $p['name'] }}</div>
                                    @if($key === 'deepseek')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">推荐</span>
                                    @endif
                                </div>
                                <div class="small text-muted mb-1"><i class="fa-solid fa-tag me-1"></i>{{ $p['price'] }}</div>
                                <div class="small text-success mb-2"><i class="fa-solid fa-gift me-1"></i>{{ $p['free_quota'] }}</div>
                                <div class="small text-muted mb-3"><i class="fa-regular fa-lightbulb me-1"></i>{{ $p['best_for'] }}</div>
                                <a href="{{ $p['docs'] }}" target="_blank" rel="noopener" class="small">
                                    前往 {{ explode('（', $p['name'])[0] }} 注册并获取 Key <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h2 class="h5 fw-bold mb-3">第 2-3 步：粘贴 API Key 并测试</h2>
            <div class="card mb-4">
                <div class="card-body">
                    @if(($current['enabled'] ?? false) && !str_starts_with($current['api_key'] ?? '', 'sk-xxxx') && !empty($current['api_key'] ?? ''))
                        <div class="alert alert-success">
                            <i class="fa-solid fa-circle-check me-2"></i>AI 功能已启用！当前使用：{{ $providers[$current['provider']]['name'] ?? $current['provider'] }}（{{ $current['model'] ?? '' }}）
                        </div>
                        <a href="{{ route('admin.ai-settings.index') }}" class="btn btn-primary">
                            <i class="fa-solid fa-gear me-1"></i>返回 AI 设置
                        </a>
                    @else
                        <form method="POST" action="{{ route('admin.ai-settings.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label" for="provider">选择提供商</label>
                                <select id="provider" name="provider" class="form-select">
                                    @foreach($providers as $key => $p)
                                        <option value="{{ $key }}" {{ ($current['provider'] ?? '') === $key ? 'selected' : '' }}>{{ $p['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="api_key">API Key</label>
                                <input type="password" id="api_key" name="api_key" required class="form-control font-monospace" placeholder="sk-xxxxxxxxxxxxxxxx">
                                <div class="form-text">在上方第 1 步点击对应链接即可获取</div>
                            </div>
                            <input type="hidden" name="enabled" value="1">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存并启用 AI</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="fw-semibold mb-2"><i class="fa-regular fa-lightbulb me-2"></i>使用提示</div>
                    <ul class="mb-0 ps-4 text-muted small">
                        <li>所有 AI 调用直接发送到对应厂商，ERP 系统不存储你的对话内容</li>
                        <li>发票识别、智能问答、审批建议等功能会消耗 API 额度</li>
                        <li>可随时在 <a href="{{ route('admin.ai-settings.index') }}">AI 设置</a> 中更换提供商或调整模型</li>
                        <li>新用户建议先用 DeepSeek，性价比最高且送 ¥10 额度</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
