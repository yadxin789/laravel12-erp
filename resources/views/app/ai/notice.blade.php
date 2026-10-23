@extends('layouts.business')

@section('title', 'AI 智能助手')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center py-5">
                    <i class="fa-solid fa-robot fa-4x text-secondary mb-3 d-block"></i>
                    <h4 class="fw-bold mb-1">AI 智能助手</h4>
                    <div class="text-muted small mb-4">智能问答 · 数据洞察 · 文档生成</div>

                    <div class="alert alert-warning text-start mb-4">
                        <h6 class="alert-heading fw-bold mb-2">
                            <i class="fa-solid fa-circle-info me-2"></i>AI 暂未开通，请联系管理员
                        </h6>
                        <p class="mb-2">
                            AI 智能助手由<b>系统管理员</b>统一开通。开通后，您即可使用智能问答、经营数据洞察与自然语言生成文档等能力。
                        </p>
                        <p class="mb-0 text-muted small">
                            如需使用，请联系系统管理员在后台「AI 智能助手」中配置供应商与 API Key 并启用。
                        </p>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('app.dashboard') }}" class="btn btn-primary">
                            <i class="fa-solid fa-house me-1"></i>返回业务首页
                        </a>
                        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-chart-simple me-1"></i>浏览分析中心
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
