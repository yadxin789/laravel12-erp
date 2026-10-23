@extends('layouts.admin')

@section('title', '物流快递设置')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-truck-fast"></i>国内物流快递设置</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.logistics.guide') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-compass me-1"></i>开通引导
            </a>
            <a href="{{ route('admin.logistics.shipments') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-list me-1"></i>运单（{{ $stats['shipments'] }}）
            </a>
        </div>
    </div>

    @foreach (['success', 'error'] as $flash)
        @if (session($flash))
            <div class="alert alert-{{ $flash === 'error' ? 'danger' : 'success' }}">{{ session($flash) }}</div>
        @endif
    @endforeach

    {{-- 概览 --}}
    <div class="card mb-4">
        <div class="card-header"><i class="fa-solid fa-circle-info me-2"></i>当前状态</div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">默认通道</div>
                    <div class="fw-semibold">
                        {{ $default?->name ?? '未设置' }}
                        @if ($default?->hasRealKey())
                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">已配置密钥</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">公共查询</span>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">运输中</div>
                    <div class="fw-semibold text-primary">{{ $stats['in_transit'] }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">已签收</div>
                    <div class="fw-semibold text-success">{{ $stats['signed'] }}</div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="text-muted small">异常件</div>
                    <div class="fw-semibold text-danger">{{ $stats['exception'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ① 查询通道 --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-plug me-2"></i>① 查询通道（聚合服务商）</span>
            <form method="POST" action="{{ route('admin.logistics.providers.sync') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-rotate me-1"></i>按目录补齐</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 16%;">通道</th>
                            <th style="width: 20%;">AppKey</th>
                            <th style="width: 20%;">AppSecret / Customer</th>
                            <th style="width: 12%;">API 地址</th>
                            <th class="text-center" style="width: 8%;">状态</th>
                            <th class="text-end" style="width: 16%;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($channels as $key => $c)
                            @php $row = $channelRows->get($key); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $c['name'] }}</div>
                                    <div class="small text-muted">{{ $c['price'] }}</div>
                                    <a href="{{ $c['docs'] }}" target="_blank" rel="noopener" class="small text-primary">申请密钥 →</a>
                                </td>
                                <form method="POST" action="{{ route('admin.logistics.providers.save', $key) }}">
                                    @csrf
                                    <td>
                                        <input type="password" name="app_key" class="form-control form-control-sm font-monospace"
                                               autocomplete="new-password"
                                               placeholder="{{ $row?->maskedKey() ?? '未设置，填写后自动启用' }}">
                                        @if ($row?->maskedKey())
                                            <div class="small text-muted mt-1">当前：{{ $row->maskedKey() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="password" name="app_secret" class="form-control font-monospace" autocomplete="new-password">
                                            <button class="btn btn-outline-primary" type="submit">保存</button>
                                        </div>
                                    </td>
                                </form>
                                <td class="small text-muted">{{ $row?->api_base ?? $c['api_base'] }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('admin.logistics.providers.toggle', $key) }}" class="mb-1">
                                        @csrf
                                        <div class="form-check form-switch d-inline-block mb-0">
                                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()"
                                                   {{ $row?->enabled ? 'checked' : '' }}>
                                        </div>
                                    </form>
                                    <div>
                                        @if ($row?->enabled && $row?->hasRealKey())
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">已开通</span>
                                        @elseif ($row?->enabled)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">缺密钥</span>
                                        @else
                                            <span class="badge bg-light text-secondary border">已停用</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 flex-wrap justify-content-end">
                                        <form method="POST" action="{{ route('admin.logistics.providers.default', $key) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" {{ $row?->enabled ? '' : 'disabled' }}>
                                                {{ $row?->is_default ? '默认' : '设默认' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.logistics.providers.test', $key) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success">测试</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- ② 快递公司 --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-boxes-packing me-2"></i>② 快递公司（启用后可在查询页选择）</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.logistics.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-2">
                            @foreach ($carriers as $key => $c)
                                @php $row = $carrierRows->get($key); @endphp
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="carriers[]"
                                               value="{{ $key }}" id="carrier-{{ $key }}"
                                               {{ $row?->enabled ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="carrier-{{ $key }}">
                                            {{ $c['name'] }}
                                            <span class="text-muted">（{{ $c['code'] }}）</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>保存快递公司
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ③ 高级参数 --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-sliders me-2"></i>③ 高级参数</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.logistics.settings.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="adv-channel">通道</label>
                            <select id="adv-channel" name="channel" class="form-select">
                                @foreach ($channels as $key => $c)
                                    <option value="{{ $key }}" {{ $default?->key === $key ? 'selected' : '' }}>{{ $c['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="adv-api-base">API 地址</label>
                            <input type="text" id="adv-api-base" name="api_base" class="form-control font-monospace"
                                   value="{{ $default?->api_base }}" placeholder="https://poll.kuaidi100.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="adv-quota">每月额度（次）</label>
                            <input type="number" id="adv-quota" name="monthly_quota" class="form-control"
                                   value="{{ $default?->monthly_quota ?? 1000 }}" min="0" max="1000000">
                            <div class="form-text">仅用于展示与提醒，不做硬性拦截</div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="notify_enabled" value="1"
                                       id="notify-enabled" checked>
                                <label class="form-check-label fw-semibold" for="notify-enabled">状态变化时发送站内消息</label>
                            </div>
                            <div class="form-text">签收 / 异常 / 退回会同时通知创建人与仓储角色</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i>保存</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
