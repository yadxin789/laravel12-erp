@extends('layouts.business')

@section('title', 'AI 智能分析中心')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-robot"></i>AI 智能分析中心</h2>
        <a href="{{ route('app.ai.chat') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-comments me-1"></i>AI 智能助手
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.daily-report') }}" class="text-decoration-none">
                <div class="card h-100 border-primary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-primary"><i class="fa-solid fa-newspaper"></i></span>
                            <div>
                                <div class="fw-semibold text-body">经营日报</div>
                                <div class="text-muted small">每日销售/采购/生产/库存摘要</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.stock-alerts') }}" class="text-decoration-none">
                <div class="card h-100 border-warning-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-warning-emphasis"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <div>
                                <div class="fw-semibold text-body">库存预警分析</div>
                                <div class="text-muted small">AI 补货建议</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.customer-credit') }}" class="text-decoration-none">
                <div class="card h-100 border-info-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-info"><i class="fa-solid fa-user-shield"></i></span>
                            <div>
                                <div class="fw-semibold text-body">客户信用评估</div>
                                <div class="text-muted small">信用等级建议</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.supplier-quality') }}" class="text-decoration-none">
                <div class="card h-100 border-success-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-success"><i class="fa-solid fa-industry"></i></span>
                            <div>
                                <div class="fw-semibold text-body">供应商质量评分</div>
                                <div class="text-muted small">月度合格率分析</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.sales-forecast') }}" class="text-decoration-none">
                <div class="card h-100 border-primary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-primary"><i class="fa-solid fa-chart-line"></i></span>
                            <div>
                                <div class="fw-semibold text-body">销售预测</div>
                                <div class="text-muted small">未来 3 个月预测</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.production-cost') }}" class="text-decoration-none">
                <div class="card h-100 border-secondary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-secondary"><i class="fa-solid fa-coins"></i></span>
                            <div>
                                <div class="fw-semibold text-body">生产成本分析</div>
                                <div class="text-muted small">计划 vs 实际差异</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.anomaly-detection') }}" class="text-decoration-none">
                <div class="card h-100 border-danger-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-danger"><i class="fa-solid fa-triangle-exclamation"></i></span>
                            <div>
                                <div class="fw-semibold text-body">异常订单检测</div>
                                <div class="text-muted small">高风险订单标记</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.scheduling') }}" class="text-decoration-none">
                <div class="card h-100 border-info-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-info"><i class="fa-solid fa-calendar-days"></i></span>
                            <div>
                                <div class="fw-semibold text-body">智能排程</div>
                                <div class="text-muted small">工单优先顺序建议</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.ecommerce-merge') }}" class="text-decoration-none">
                <div class="card h-100 border-success-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-success"><i class="fa-solid fa-cart-flatbed"></i></span>
                            <div>
                                <div class="fw-semibold text-body">电商订单合并</div>
                                <div class="text-muted small">同买家多平台合并建议</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.email-reply') }}" class="text-decoration-none">
                <div class="card h-100 border-secondary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-secondary"><i class="fa-solid fa-envelope-open-text"></i></span>
                            <div>
                                <div class="fw-semibold text-body">邮件智能回复</div>
                                <div class="text-muted small">询盘报价草稿</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.invoice-ocr') }}" class="text-decoration-none">
                <div class="card h-100 border-warning-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-warning-emphasis"><i class="fa-solid fa-file-invoice"></i></span>
                            <div>
                                <div class="fw-semibold text-body">发票智能识别</div>
                                <div class="text-muted small">OCR 提取生成采购草稿</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ route('app.ai.notice') }}" class="text-decoration-none">
                <div class="card h-100 border-primary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-3 text-primary"><i class="fa-solid fa-plug-circle-bolt"></i></span>
                            <div>
                                <div class="fw-semibold text-body">AI 未开通？</div>
                                <div class="text-muted small">由系统管理员统一开通并配置 API Key</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
