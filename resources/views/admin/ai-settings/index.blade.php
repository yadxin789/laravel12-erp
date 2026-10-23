@extends('layouts.admin')

@section('title', 'AI 智能助手设置')

@section('content')
    @php
        $active = $setting->enabled && !empty($setting->api_key) && !str_starts_with((string) $setting->api_key, 'sk-xxxx');
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-robot"></i>AI 智能助手设置</h2>
        <span class="badge {{ $active ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-light text-secondary border' }}">
            {{ $active ? '已启用' : '未启用' }}
        </span>
    </div>

    @foreach (['success', 'error', 'warning'] as $flash)
        @if (session($flash))
            <div class="alert alert-{{ $flash === 'error' ? 'danger' : ($flash === 'warning' ? 'warning' : 'success') }}">
                {{ session($flash) }}
            </div>
        @endif
    @endforeach

    {{-- 当前生效配置 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>当前生效配置</div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">默认供应商</div>
                    <div class="fw-semibold">{{ $activeProvider?->name ?? '未设置' }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">对话模型</div>
                    <div class="fw-semibold">{{ $defaultModel?->name ?? ($setting->model ?: '-') }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">视觉能力</div>
                    <div class="fw-semibold">
                        @if ($setting->vision_enabled)
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">已开启</span>
                        @else
                            <span class="badge bg-light text-secondary border">未开启</span>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">限流</div>
                    <div class="fw-semibold">{{ $setting->rate_limit_minute }} 次/分 · {{ $setting->rate_limit_daily }} 次/天</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ① 供应商管理 --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-plug me-2"></i>① 供应商管理</span>
            <small class="text-muted">可同时配置多家，密钥留空表示保持不变</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 18%;">供应商</th>
                            <th style="width: 24%;">API Key</th>
                            <th style="width: 22%;">Base URL</th>
                            <th class="text-center" style="width: 8%;">模型</th>
                            <th class="text-center" style="width: 10%;">状态</th>
                            <th class="text-end" style="width: 18%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($catalog as $key => $p)
                            @php $row = $providerRows->get($key); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $p['name'] }}</div>
                                    <div class="small text-muted">{{ $p['price'] ?? '' }}</div>
                                    <a href="{{ $p['docs'] ?? '#' }}" target="_blank" rel="noopener" class="small text-primary">获取 Key →</a>
                                </td>
                                <form method="POST" action="{{ route('admin.ai-settings.providers.save', $key) }}">
                                    @csrf
                                    <td>
                                        <input type="password" name="api_key" class="form-control form-control-sm font-monospace"
                                               autocomplete="new-password"
                                               placeholder="{{ $row?->maskedApiKey() ?? '未设置，填写后自动启用' }}">
                                        @if ($row?->maskedApiKey())
                                            <div class="small text-muted mt-1">当前：{{ $row->maskedApiKey() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="base_url" class="form-control font-monospace"
                                                   value="{{ $row?->base_url ?? $p['base_url'] ?? '' }}">
                                            <button class="btn btn-outline-primary" type="submit">保存</button>
                                        </div>
                                    </td>
                                </form>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border">{{ $row?->models->count() ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.ai-settings.providers.toggle', $key) }}" class="mb-1">
                                        @csrf
                                        <div class="form-check form-switch d-inline-block mb-0">
                                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()"
                                                   {{ $row?->enabled ? 'checked' : '' }} title="启用/停用">
                                        </div>
                                    </form>
                                    <div>
                                        @if ($row?->enabled && $row?->hasRealApiKey())
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">已开通</span>
                                        @elseif ($row?->enabled)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">缺 Key</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">已停用</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 flex-wrap justify-content-end">
                                        <form method="POST" action="{{ route('admin.ai-settings.providers.default', $key) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" {{ $row?->enabled ? '' : 'disabled' }}>
                                                {{ $row?->is_default ? '默认' : '设默认' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.ai-settings.providers.test', $key) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">测试</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.ai-settings.providers.sync', $key) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">补模型</button>
                                        </form>
                                        @if ($row?->hasRealApiKey())
                                            <form method="POST" action="{{ route('admin.ai-settings.providers.remove', $key) }}"
                                                  onsubmit="return confirm('确定清除该供应商的密钥并停用？')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">清 Key</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ② 模型管理 --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-microchip me-2"></i>② 模型管理</span>
            <small class="text-muted">仅「开放」的模型会出现在用户聊天页</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 16%;">供应商</th>
                            <th style="width: 20%;">模型标识</th>
                            <th>展示名称</th>
                            <th class="text-center" style="width: 8%;">看图</th>
                            <th class="text-center" style="width: 12%;">状态</th>
                            <th class="text-end" style="width: 20%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($models as $model)
                            <tr>
                                <td>{{ $model->provider?->name ?? '-' }}</td>
                                <td class="font-monospace small">{{ $model->model_id }}</td>
                                <td>
                                    {{ $model->name }}
                                    @if ($model->is_default)
                                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">默认</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($model->is_vision)
                                        <i class="fa-solid fa-eye text-success"></i>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($model->enabled && $model->provider?->enabled)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">用户可选</span>
                                    @elseif (! $model->provider?->enabled)
                                        <span class="badge bg-light text-secondary border">供应商未开通</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">未开放</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 flex-wrap justify-content-end">
                                        <form method="POST" action="{{ route('admin.ai-settings.models.default', $model) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" {{ $model->enabled ? '' : 'disabled' }}>设默认</button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-primary" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#model-edit-{{ $model->id }}">编辑</button>
                                        <form method="POST" action="{{ route('admin.ai-settings.models.destroy', $model) }}"
                                              onsubmit="return confirm('确定删除该模型？')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">删除</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr class="collapse" id="model-edit-{{ $model->id }}">
                                <td colspan="6" class="bg-body-tertiary">
                                    <form method="POST" action="{{ route('admin.ai-settings.models.update', $model) }}" class="row g-2 align-items-end">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">模型标识</label>
                                            <input type="text" name="model_id" class="form-control form-control-sm font-monospace" value="{{ $model->model_id }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small mb-1">展示名称</label>
                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $model->name }}">
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="en-{{ $model->id }}" {{ $model->enabled ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="en-{{ $model->id }}">开放给用户</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="is_vision" value="1" id="vi-{{ $model->id }}" {{ $model->is_vision ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="vi-{{ $model->id }}">看图</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-sm btn-primary w-100">保存修改</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="erp-empty">尚未录入模型，可在下方「补模型」或手工新增</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ③ 新增自定义模型 --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-plus me-2"></i>③ 新增自定义模型</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ai-settings.models.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="provider_id">所属供应商</label>
                            <select id="provider_id" name="provider_id" class="form-select" required>
                                @foreach ($providerRows as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="model_id">模型标识</label>
                            <input type="text" id="model_id" name="model_id" class="form-control font-monospace" placeholder="如 gpt-4o-mini" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="model_name">展示名称</label>
                            <input type="text" id="model_name" name="name" class="form-control" placeholder="如 GPT-4o-mini（通用）" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="new-enabled" checked>
                                <label class="form-check-label" for="new-enabled">开放给用户选择</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_vision" value="1" id="new-vision">
                                <label class="form-check-label" for="new-vision">支持图片理解</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i>添加模型</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ④ 生成参数与限流 --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-sliders me-2"></i>④ 生成参数与限流</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ai-settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="temperature">温度（Temperature）</label>
                                <input type="number" id="temperature" name="temperature" step="0.1" min="0" max="2"
                                       class="form-control" value="{{ $setting->temperature }}">
                                <div class="form-text">0 = 严谨，1 = 均衡，2 = 创意</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="max_tokens">最大输出 Token</label>
                                <input type="number" id="max_tokens" name="max_tokens" min="100" max="8000"
                                       class="form-control" value="{{ $setting->max_tokens }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="rate_limit_minute">每分钟调用上限</label>
                                <input type="number" id="rate_limit_minute" name="rate_limit_minute" min="1" max="1000"
                                       class="form-control" value="{{ $setting->rate_limit_minute }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="rate_limit_daily">每天调用上限</label>
                                <input type="number" id="rate_limit_daily" name="rate_limit_daily" min="1" max="100000"
                                       class="form-control" value="{{ $setting->rate_limit_daily }}">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enabled" value="1" id="ai-enabled" {{ $setting->enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="ai-enabled">启用 AI 功能（总开关）</label>
                                </div>
                                <div class="form-text">
                                    关闭后所有业务用户不可用，其「AI 智能助手」页显示「AI 暂未开通，请联系管理员」；管理员账号本身不参与聊天。
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="vision_enabled" value="1" id="ai-vision" {{ $setting->vision_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="ai-vision">启用视觉 / OCR 能力</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存设置</button>
                            <a href="{{ route('admin.ai-settings.test') }}" class="btn btn-outline-success"
                               onclick="event.preventDefault(); document.getElementById('test-form').submit();">
                                <i class="fa-solid fa-plug-circle-check me-1"></i>测试当前通道
                            </a>
                        </div>
                    </form>

                    <form id="test-form" method="POST" action="{{ route('admin.ai-settings.test') }}" class="d-none">@csrf</form>
                </div>
            </div>
        </div>
    </div>
@endsection
