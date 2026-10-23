<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP企业资源管理系统 - {{ $currentYear }}年度概览</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/erp.css') }}">
    <link rel="stylesheet" href="{{ asset('css/erp-utilities.css') }}">
    <script src="{{ asset('js/echarts.min.js') }}"></script>

    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #5a6fd6;
            --secondary-color: #f093fb;
            --accent-color: #f5576c;
            --success-color: #52c41a;
            --warning-color: #faad14;
            --info-color: #1890ff;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            color: white;
            padding: 80px 0 100px;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .btn-primary-custom {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            color: var(--primary-dark);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .nav-link {
            color: #374151;
            font-weight: 500;
            margin-left: 20px;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-icon.sales {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon.purchase {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon.production {
            background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);
        }

        .stat-icon.inventory {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid #e5e7eb;
            padding: 20px 24px;
        }

        .chart-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0;
        }

        .chart-card .card-body {
            padding: 24px;
        }

        .efficiency-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .efficiency-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .efficiency-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .efficiency-label {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .footer {
            background: #1f2937;
            color: #9ca3af;
            padding: 40px 0;
            margin-top: 60px;
        }

        .footer-links a {
            color: #9ca3af;
            margin-right: 20px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.75rem;
            }
            .stat-card {
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fa fa-bar-chart me-2"></i>
                ERP企业资源管理系统
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="#overview">统计概览</a>
                    <a class="nav-link" href="#charts">数据图表</a>
                    @auth
                        <a class="nav-link" href="{{ route('app.dashboard') }}">
                            <i class="fa fa-sign-in me-1"></i>进入系统
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link" style="border: none; background: none; cursor: pointer;">
                                <i class="fa fa-sign-out me-1"></i>退出登录
                            </button>
                        </form>
                    @else
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="fa fa-sign-in me-1"></i>登录
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">
                        ERP {{ $currentYear }}年度业务数据概览
                    </h1>
                    <p class="hero-subtitle">
                        全面展示本年度销售、采购、生产与库存的核心统计数据，助力企业高效运营决策
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#overview" class="btn btn-primary-custom">
                            <i class="fa fa-pie-chart me-2"></i>查看数据
                        </a>
                        @auth
                            <a href="{{ route('app.dashboard') }}" class="btn btn-outline-light btn-lg" style="border-color: rgba(255,255,255,0.5);">
                                <i class="fa fa-sign-in me-2"></i>进入仪表盘
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg" style="border-color: rgba(255,255,255,0.5);">
                                <i class="fa fa-user-circle me-2"></i>登录系统
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="text-center">
                        <div class="bg-white bg-opacity-10 rounded-3 p-4">
                            <div class="fs-1 mb-3"><i class="fa-solid fa-chart-simple"></i></div>
                            <h3 class="h5 fw-semibold mb-2">{{ $salesOrderCount + $purchaseOrderCount + $productionOrderCount }}</h3>
                            <p class="opacity-75 mb-0">本年度单据总数</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="overview" class="py-5 bg-body-tertiary">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12 text-center">
                    <h2 class="h4 fw-bold">业务统计概览</h2>
                    <p class="text-muted mt-2">实时掌握企业核心业务数据</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fa fa-shopping-bag text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-primary">{{ $salesOrderCount }}</div>
                        <div class="stat-label">销售订单</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon purchase">
                            <i class="fa fa-clipboard text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-success">{{ $purchaseOrderCount }}</div>
                        <div class="stat-label">采购订单</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon production">
                            <i class="fa fa-flask text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-warning">{{ $productionOrderCount }}</div>
                        <div class="stat-label">生产工单</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon inventory">
                            <i class="fa fa-cubes text-white fa-xl"></i>
                        </div>
                        <div class="stat-value text-info">{{ $itemCount }}</div>
                        <div class="stat-label">库存物料品种</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="charts" class="py-5 bg-white">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12 text-center">
                    <h2 class="h4 fw-bold">数据可视化</h2>
                    <p class="text-muted mt-2">直观展示业务趋势与分布</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="chart-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fa fa-bar-chart me-2 text-primary"></i>月度销售订单趋势
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="monthlyChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="chart-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fa fa-pie-chart me-2 text-primary"></i>销售订单状态分布
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="statusChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="chart-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fa fa-bolt me-2 text-primary"></i>运营效率指标
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="efficiency-card">
                                        <div class="efficiency-value text-primary">{{ $efficiencyStats['sales_completion_rate'] }}%</div>
                                        <div class="efficiency-label">销售订单完成率</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="efficiency-card">
                                        <div class="efficiency-value text-success">{{ $efficiencyStats['purchase_completion_rate'] }}%</div>
                                        <div class="efficiency-label">采购订单完成率</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="efficiency-card">
                                        <div class="efficiency-value text-warning">{{ $efficiencyStats['production_completion_rate'] }}%</div>
                                        <div class="efficiency-label">生产工单完成率</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="efficiency-card {{ $efficiencyStats['low_stock_count'] > 0 ? 'border-danger' : '' }}">
                                        <div class="efficiency-value {{ $efficiencyStats['low_stock_count'] > 0 ? 'text-danger' : 'text-muted' }}">
                                            {{ $efficiencyStats['low_stock_count'] }}
                                        </div>
                                        <div class="efficiency-label {{ $efficiencyStats['low_stock_count'] > 0 ? 'text-danger' : '' }}">低库存预警</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="footer-links">
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <p>ERP企业资源管理系统 © {{ $currentYear }}. 保留所有权利.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var monthlyChart = echarts.init(document.getElementById('monthlyChart'));
            var months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
            var monthlyData = JSON.parse('{!! json_encode($monthlySalesData) !!}');

            monthlyChart.setOption({
                tooltip: {
                    trigger: 'axis',
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#374151' },
                    formatter: function(params) {
                        return '<strong>' + params[0].axisValue + '</strong><br/>销售订单: <strong>' + params[0].value + '</strong>';
                    }
                },
                grid: {
                    left: '3%', right: '4%', bottom: '3%', top: '10%', containLabel: true
                },
                xAxis: {
                    type: 'category', data: months,
                    axisLine: { lineStyle: { color: '#e5e7eb' } },
                    axisLabel: { color: '#6b7280' }
                },
                yAxis: {
                    type: 'value',
                    axisLine: { show: false },
                    axisTick: { show: false },
                    splitLine: { lineStyle: { color: '#f3f4f6' } },
                    axisLabel: { color: '#6b7280' }
                },
                series: [{
                    name: '销售订单', type: 'bar',
                    data: Object.values(monthlyData),
                    barWidth: '50%',
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {offset: 0, color: '#667eea'},
                            {offset: 1, color: '#764ba2'}
                        ]),
                        borderRadius: [6, 6, 0, 0]
                    },
                    emphasis: {
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                {offset: 0, color: '#5a6fd6'},
                                {offset: 1, color: '#6b42a0'}
                            ])
                        }
                    }
                }]
            });

            var statusChart = echarts.init(document.getElementById('statusChart'));
            var statusData = JSON.parse('{!! json_encode($salesStatusData) !!}');

            statusChart.setOption({
                tooltip: {
                    trigger: 'item',
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    textStyle: { color: '#374151' },
                    formatter: '{b}: {c} 个 ({d}%)'
                },
                legend: {
                    orient: 'horizontal', bottom: 0, itemGap: 15,
                    textStyle: { color: '#6b7280', fontSize: 11 }
                },
                series: [{
                    name: '状态', type: 'pie',
                    radius: ['45%', '75%'], center: ['50%', '45%'],
                    avoidLabelOverlap: true,
                    itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 3 },
                    label: { show: true, formatter: '{b}: {c}', position: 'outside', distance: 8, fontSize: 11 },
                    emphasis: {
                        label: { show: true, fontSize: 16, fontWeight: 'bold', formatter: '{b}\n{d}%' }
                    },
                    labelLine: { show: true, length: 12, length2: 8 },
                    data: [
                        {value: statusData['草稿'] || 0, name: '草稿', itemStyle: {color: '#9ca3af'}},
                        {value: statusData['待审核'] || 0, name: '待审核', itemStyle: {color: '#faad14'}},
                        {value: statusData['已审核'] || 0, name: '已审核', itemStyle: {color: '#1890ff'}},
                        {value: statusData['部分发货'] || 0, name: '部分发货', itemStyle: {color: '#13c2c2'}},
                        {value: statusData['已发货'] || 0, name: '已发货', itemStyle: {color: '#52c41a'}},
                        {value: statusData['已关闭'] || 0, name: '已关闭', itemStyle: {color: '#52c41a'}},
                        {value: statusData['已取消'] || 0, name: '已取消', itemStyle: {color: '#f5222d'}}
                    ]
                }]
            });

            window.addEventListener('resize', function() {
                monthlyChart.resize();
                statusChart.resize();
            });
        });
    </script>
</body>
</html>