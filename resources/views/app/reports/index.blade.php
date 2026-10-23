@extends('layouts.business')

@section('title', '报表中心')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-chart-line"></i>报表中心</h2>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <a href="{{ route('app.reports.sales') }}" class="text-decoration-none">
                <div class="card h-100 border-primary-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-2 text-primary"><i class="fa-solid fa-money-bill-trend-up"></i></span>
                            <div>
                                <div class="fw-bold fs-5 text-body">销售统计</div>
                                <div class="text-muted small">按月销售额、客户排行</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('app.reports.purchase') }}" class="text-decoration-none">
                <div class="card h-100 border-success-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-2 text-success"><i class="fa-solid fa-cart-shopping"></i></span>
                            <div>
                                <div class="fw-bold fs-5 text-body">采购统计</div>
                                <div class="text-muted small">按月采购额、供应商排行</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('app.reports.inventory') }}" class="text-decoration-none">
                <div class="card h-100 border-warning-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-2 text-warning-emphasis"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <div>
                                <div class="fw-bold fs-5 text-body">库存报表</div>
                                <div class="text-muted small">库存数量 Top、低库存预警</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6">
            <a href="{{ route('app.reports.quality') }}" class="text-decoration-none">
                <div class="card h-100 border-info-subtle">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-2 text-info"><i class="fa-solid fa-clipboard-check"></i></span>
                            <div>
                                <div class="fw-bold fs-5 text-body">质量报表</div>
                                <div class="text-muted small">检验通过率、不合格分析</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
@endsection
