@extends('layouts.admin')

@section('title', '系统概览')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-gauge"></i>系统概览</h2>
        <a href="{{ route('admin.ai-settings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-robot me-1"></i>AI 开通与设置
        </a>
    </div>

    <div class="row g-3">
        <div class="col-6 col-xl-3">
            <div class="card border-primary-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">用户总数</div>
                    <div class="fs-3 fw-bold text-primary">{{ \App\Models\User::count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-success-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">角色总数</div>
                    <div class="fs-3 fw-bold text-success">{{ \Spatie\Permission\Models\Role::count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-info-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">权限总数</div>
                    <div class="fs-3 fw-bold text-info">{{ \Spatie\Permission\Models\Permission::count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-warning-subtle h-100">
                <div class="card-body">
                    <div class="text-muted small">物料总数</div>
                    <div class="fs-3 fw-bold text-warning-emphasis">{{ \App\Models\MasterData\Item::count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-line me-2"></i>近 6 个月销售 / 采购趋势</div>
                <div class="card-body">
                    <div id="adminChartTrend" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-pie me-2"></i>销售订单状态分布</div>
                <div class="card-body">
                    <div id="adminChartStatus" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-warehouse me-2"></i>各仓库库存分布</div>
                <div class="card-body">
                    <div id="adminChartWarehouse" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-boxes-stacked me-2"></i>库存数量 TOP 8</div>
                <div class="card-body">
                    <div id="adminChartStock" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/echarts.min.js') }}"></script>
    <script>
        (function () {
            if (typeof echarts === 'undefined') { return; }
            const palette = ['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#14b8a6', '#f97316'];
            const charts = [];

            function mount(id, option) {
                const el = document.getElementById(id);
                if (!el) { return; }
                const chart = echarts.init(el);
                chart.setOption(option);
                charts.push(chart);
            }

            mount('adminChartTrend', {
                tooltip: { trigger: 'axis' },
                legend: { bottom: 0, data: ['销售金额', '采购金额'] },
                grid: { left: 70, right: 20, top: 30, bottom: 50 },
                xAxis: { type: 'category', data: @json($salesTrend['labels']) },
                yAxis: { type: 'value', name: '金额' },
                series: [
                    { name: '销售金额', type: 'line', smooth: true, areaStyle: { opacity: 0.15 }, data: @json($salesTrend['amounts']) },
                    { name: '采购金额', type: 'line', smooth: true, areaStyle: { opacity: 0.15 }, data: @json($purchaseTrend['amounts']) }
                ]
            });

            mount('adminChartStatus', {
                tooltip: { trigger: 'item' },
                legend: { bottom: 0 },
                color: palette,
                series: [{ type: 'pie', radius: ['42%', '68%'], data: @json($salesStatus), label: { formatter: '{b}: {c}' } }]
            });

            mount('adminChartWarehouse', {
                tooltip: { trigger: 'item' },
                legend: { bottom: 0 },
                color: palette,
                series: [{ type: 'pie', radius: '65%', data: @json($stockByWarehouse), label: { formatter: '{b}: {c}' } }]
            });

            mount('adminChartStock', {
                tooltip: { trigger: 'axis' },
                grid: { left: 110, right: 30, top: 20, bottom: 30 },
                xAxis: { type: 'value', name: '数量' },
                yAxis: { type: 'category', data: @json(array_reverse(array_column($stockTop, 'name'))) },
                series: [{
                    type: 'bar',
                    barWidth: 16,
                    itemStyle: { color: '#0ea5e9', borderRadius: [0, 4, 4, 0] },
                    data: @json(array_reverse(array_column($stockTop, 'qty')))
                }]
            });

            window.addEventListener('resize', function () { charts.forEach(function (c) { c.resize(); }); });
        })();
    </script>
@endpush
