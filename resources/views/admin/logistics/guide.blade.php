@extends('layouts.admin')

@section('title', '物流快递开通引导')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title mb-0"><i class="fa-solid fa-truck-fast"></i>国内物流快递 · 开通引导</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.logistics.settings') }}" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-gear me-1"></i>去设置
            </a>
            <a href="{{ route('admin.logistics.shipments') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-list me-1"></i>运单列表
            </a>
        </div>
    </div>

    <div class="alert {{ $configured ? 'alert-success' : 'alert-info' }}">
        @if ($configured)
            <i class="fa-solid fa-circle-check me-2"></i>已有通道完成配置，可直接开始查询运单。
        @else
            <i class="fa-solid fa-circle-info me-2"></i>
            尚未配置任何查询通道。未配置时系统会自动使用快递100 的<b>免费公共查询接口</b>（无需密钥，够日常试用）；
            正式使用建议按下面三步开通聚合通道，稳定性与额度更有保障。
        @endif
    </div>

    {{-- 第一步 --}}
    <div class="card mb-4">
        <div class="card-header"><span class="badge bg-primary rounded-pill me-2">1</span>选择查询通道</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($channels as $c)
                    <div class="col-md-4">
                        <div class="card h-100 {{ $c['row']?->enabled ? 'border-success' : '' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ $c['name'] }}</h6>
                                    @if ($c['row']?->enabled)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">已启用</span>
                                    @endif
                                </div>
                                <ul class="small text-muted mb-2 ps-3">
                                    <li>{{ $c['best_for'] }}</li>
                                    <li>{{ $c['price'] }}</li>
                                    <li>{{ $c['free_quota'] }}</li>
                                </ul>
                                <a href="{{ $c['docs'] }}" target="_blank" rel="noopener" class="small">
                                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>前往申请
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 第二步 --}}
    <div class="card mb-4">
        <div class="card-header"><span class="badge bg-primary rounded-pill me-2">2</span>申请密钥</div>
        <div class="card-body">
            <ol class="mb-0">
                <li>在所选通道官网注册企业账号，完成实名认证。</li>
                <li>进入「密钥管理 / API 设置」，创建应用，拿到 <code>AppKey</code>（或 customer 编码）与 <code>AppSecret</code>。</li>
                <li>若只在内网试运行，可跳过本步——系统会走免费公共查询，界面会提示「公共查询」来源。</li>
            </ol>
        </div>
    </div>

    {{-- 第三步 --}}
    <div class="card">
        <div class="card-header"><span class="badge bg-primary rounded-pill me-2">3</span>粘贴到设置页并测试</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.logistics.settings.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="channel">通道</label>
                        <select id="channel" name="channel" class="form-select">
                            @foreach ($channels as $key => $c)
                                <option value="{{ $key }}">{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="app_key">AppKey</label>
                        <input type="password" id="app_key" name="app_key" class="form-control font-monospace" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="app_secret">AppSecret / Customer</label>
                        <input type="password" id="app_secret" name="app_secret" class="form-control font-monospace" autocomplete="new-password">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>保存并启用</button>
                    <a href="{{ route('admin.logistics.settings') }}" class="btn btn-light">高级设置</a>
                </div>
            </form>
        </div>
    </div>
@endsection
