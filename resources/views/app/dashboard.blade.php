@extends('layouts.business')

@section('title', '仪表盘')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h2 class="page-title"><i class="fa-solid fa-gauge"></i>业务总览</h2>
        <a href="{{ route('app.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-chart-line me-1"></i>报表中心
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-primary-subtle h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">销售订单</div>
                        <div class="fs-3 fw-bold text-primary">{{ $salesOrders }}</div>
                    </div>
                    <span class="fs-2 text-primary opacity-75"><i class="fa-solid fa-bag-shopping"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-info-subtle h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">采购订单</div>
                        <div class="fs-3 fw-bold text-info">{{ $purchaseOrders }}</div>
                    </div>
                    <span class="fs-2 text-info opacity-75"><i class="fa-solid fa-cart-flatbed"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-warning-subtle h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">生产工单</div>
                        <div class="fs-3 fw-bold text-warning-emphasis">{{ $productionOrders }}</div>
                    </div>
                    <span class="fs-2 text-warning-emphasis opacity-75"><i class="fa-solid fa-industry"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-success-subtle h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">库存价值</div>
                        <div class="fs-3 fw-bold text-success">¥{{ number_format($inventoryValue, 2) }}</div>
                    </div>
                    <span class="fs-2 text-success opacity-75"><i class="fa-solid fa-boxes-stacked"></i></span>
                </div>
            </div>
        </div>
    </div>

    @php
        $roleChart = match ($role) {
            'warehouse_manager' => ['type' => 'pie', 'title' => '各仓库库存分布', 'data' => $stockByWarehouse],
            'quality_manager' => ['type' => 'pie', 'title' => '来料/过程检验合格情况', 'data' => [
                ['name' => '合格数', 'value' => $qualityPass],
                ['name' => '不合格数', 'value' => $qualityReject],
            ]],
            'accountant' => ['type' => 'bar', 'title' => '应收 / 应付余额', 'data' => [
                ['name' => '应收未收', 'value' => round($receivable, 2)],
                ['name' => '应付未付', 'value' => round($payable, 2)],
            ]],
            'purchase_manager' => ['type' => 'pie', 'title' => '采购订单状态分布', 'data' => $purchaseStatus],
            default => ['type' => 'pie', 'title' => '生产工单状态分布', 'data' => $productionStatus],
        };
        $mainStatus = in_array($role, ['purchase_manager'])
            ? ['title' => '采购订单状态分布', 'data' => $purchaseStatus]
            : ['title' => '销售订单状态分布', 'data' => $salesStatus];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-line me-2"></i>近 6 个月单据趋势</div>
                <div class="card-body">
                    <div id="chartTrend" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-pie me-2"></i>{{ $mainStatus['title'] }}</div>
                <div class="card-body">
                    <div id="chartStatus" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-boxes-stacked me-2"></i>库存数量 TOP 8</div>
                <div class="card-body">
                    <div id="chartStock" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-chart-column me-2"></i>{{ $roleChart['title'] }}</div>
                <div class="card-body">
                    <div id="chartRole" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-bag-shopping me-2"></i>最近销售订单</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>客户</th>
                                    <th class="text-end">金额</th>
                                    <th class="text-center">状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSalesOrders as $order)
                                    <tr>
                                        <td class="font-monospace small">{{ $order->order_no }}</td>
                                        <td>{{ $order->customer?->name ?? '-' }}</td>
                                        <td class="text-end">¥{{ number_format($order->total_amount ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="erp-empty">暂无销售订单</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-cart-flatbed me-2"></i>最近采购订单</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>订单号</th>
                                    <th>供应商</th>
                                    <th class="text-end">金额</th>
                                    <th class="text-center">状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPurchaseOrders as $order)
                                    <tr>
                                        <td class="font-monospace small">{{ $order->order_no }}</td>
                                        <td>{{ $order->supplier?->name ?? '-' }}</td>
                                        <td class="text-end">¥{{ number_format($order->total_amount ?? 0, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="erp-empty">暂无采购订单</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header"><i class="fa-solid fa-industry me-2"></i>最近生产工单</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-sm">
                            <thead>
                                <tr>
                                    <th>工单号</th>
                                    <th>产品</th>
                                    <th class="text-end">数量</th>
                                    <th class="text-center">状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProductionOrders as $order)
                                    <tr>
                                        <td class="font-monospace small">{{ $order->order_no }}</td>
                                        <td>{{ $order->item?->name ?? '-' }}</td>
                                        <td class="text-end">{{ $order->quantity ?? 0 }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border">{{ $order->status?->label() ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="erp-empty">暂无生产工单</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
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

            // 趋势：销售金额 + 采购金额 + 生产数量
            mount('chartTrend', {
                tooltip: { trigger: 'axis' },
                legend: { bottom: 0, data: ['销售金额', '采购金额', '生产数量'] },
                grid: { left: 60, right: 20, top: 30, bottom: 50 },
                xAxis: { type: 'category', data: @json($salesTrend['labels']) },
                yAxis: [
                    { type: 'value', name: '金额' },
                    { type: 'value', name: '数量' }
                ],
                series: [
                    { name: '销售金额', type: 'line', smooth: true, areaStyle: { opacity: 0.15 }, data: @json($salesTrend['amounts']) },
                    { name: '采购金额', type: 'line', smooth: true, areaStyle: { opacity: 0.15 }, data: @json($purchaseTrend['amounts']) },
                    { name: '生产数量', type: 'bar', yAxisIndex: 1, data: @json($productionTrend['amounts']) }
                ]
            });

            // 主状态分布
            mount('chartStatus', {
                tooltip: { trigger: 'item' },
                legend: { bottom: 0 },
                color: palette,
                series: [{
                    type: 'pie',
                    radius: ['42%', '68%'],
                    data: @json($mainStatus['data']),
                    label: { formatter: '{b}: {c}' }
                }]
            });

            // 库存 TOP 8
            mount('chartStock', {
                tooltip: { trigger: 'axis' },
                grid: { left: 110, right: 30, top: 20, bottom: 30 },
                xAxis: { type: 'value', name: '数量' },
                yAxis: {
                    type: 'category',
                    data: @json(array_reverse(array_column($stockTop, 'name')))
                },
                series: [{
                    type: 'bar',
                    barWidth: 16,
                    itemStyle: { color: '#4f46e5', borderRadius: [0, 4, 4, 0] },
                    data: @json(array_reverse(array_column($stockTop, 'qty')))
                }]
            });

            // 角色专属图表
            const roleChart = @json($roleChart);
            const roleData = (roleChart.data || []).map(function (i) {
                return { name: i.name, value: Number(i.value) };
            });
            if (roleChart.type === 'bar') {
                mount('chartRole', {
                    tooltip: { trigger: 'axis' },
                    grid: { left: 90, right: 30, top: 30, bottom: 40 },
                    xAxis: { type: 'category', data: roleData.map(i => i.name) },
                    yAxis: { type: 'value' },
                    series: [{
                        type: 'bar',
                        barWidth: 48,
                        itemStyle: { color: '#0ea5e9', borderRadius: [6, 6, 0, 0] },
                        label: { show: true, position: 'top' },
                        data: roleData.map(i => i.value)
                    }]
                });
            } else {
                mount('chartRole', {
                    tooltip: { trigger: 'item' },
                    legend: { bottom: 0 },
                    color: palette,
                    series: [{
                        type: 'pie',
                        radius: ['42%', '68%'],
                        data: roleData,
                        label: { formatter: '{b}: {c}' }
                    }]
                });
            }

            window.addEventListener('resize', function () {
                charts.forEach(function (c) { c.resize(); });
            });
        })();
    </script>
@endpush
