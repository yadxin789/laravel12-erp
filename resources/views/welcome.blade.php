<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP系统 - {{ $currentYear }}年度概览</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- ECharts 通过 CDN 加载，用于数据可视化图表 --}}
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>

    <style>
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }
        /* 渐变背景 Hero 区 */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0 100px;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        .hero-title { font-size: 2.5rem; font-weight: 600; margin-bottom: 16px; }
        .hero-subtitle { font-size: 1.1rem; opacity: 0.9; margin-bottom: 30px; }

        /* 导航栏：毛玻璃效果 */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            position: sticky; top: 0; z-index: 1000;
        }

        /* 统计卡片 */
        .stat-card {
            background: white; border-radius: 12px; padding: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1); }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
            color: white;
        }
        .stat-icon.sales   { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.purchase { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-icon.production { background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%); }
        .stat-icon.inventory { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-value { font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .stat-label { font-size: 0.9rem; color: #6b7280; }

        /* 图表卡片 */
        .chart-card {
            background: white; border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); overflow: hidden;
        }
        .chart-card .card-header {
            border-bottom: 1px solid #e5e7eb; padding: 20px 24px;
        }
        .chart-card .card-title { font-size: 1.1rem; font-weight: 600; color: #1f2937; margin: 0; }
        .chart-card .card-body { padding: 24px; }

        /* 效率指标卡片 */
        .efficiency-card {
            background: white; border-radius: 12px; padding: 20px; text-align: center;
            border: 1px solid #e5e7eb; transition: all 0.3s ease;
        }
        .efficiency-card:hover {
            border-color: #667eea; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        .efficiency-value { font-size: 1.75rem; font-weight: 700; margin-bottom: 8px; }
        .efficiency-label { font-size: 0.875rem; color: #6b7280; }

        /* 页脚 */
        .footer { background: #1f2937; color: #9ca3af; padding: 40px 0; margin-top: 60px; }

        @media (max-width: 768px) {
            .hero-title { font-size: 1.75rem; }
            .stat-card { margin-bottom: 16px; }
        }
    </style>
</head>
<body>
    {{-- 导航栏：品牌在左，登录入口右对齐 --}}
    <nav class="navbar">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-semibold text-[#667eea] flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                ERP企业资源管理系统
            </a>
            <div class="flex items-center gap-4">
                <a href="#overview" class="text-gray-700 font-medium hover:text-[#667eea] transition-colors">统计概览</a>
                <a href="#charts" class="text-gray-700 font-medium hover:text-[#667eea] transition-colors">数据图表</a>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-gray-700 font-medium hover:text-[#667eea] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    登录
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero 区：渐变背景 + 标题 + 登录入口 --}}
    <section class="hero-section">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="lg:w-2/3">
                    <h1 class="hero-title">ERP {{ $currentYear }}年度业务数据概览</h1>
                    <p class="hero-subtitle">
                        全面展示本年度销售、采购、生产与库存的核心统计数据，助力企业高效运营决策
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="#overview" class="inline-flex items-center gap-2 bg-white text-[#667eea] px-6 py-3 rounded-lg font-medium shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                            </svg>
                            查看数据
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm text-white border border-white/50 px-6 py-3 rounded-lg font-medium hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            登录系统
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/3 hidden lg:block">
                    <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6">
                        <div class="text-6xl mb-4">📊</div>
                        <div class="text-4xl font-bold mb-2">{{ $salesOrderCount + $purchaseOrderCount + $productionOrderCount }}</div>
                        <p class="opacity-80">本年度单据总数</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 统计概览：4 张统计卡片 --}}
    <section id="overview" class="py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">业务统计概览</h2>
                <p class="text-gray-600 mt-2">实时掌握企业核心业务数据</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- 销售订单 --}}
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="stat-value text-[#667eea]">{{ $salesOrderCount }}</div>
                    <div class="stat-label">销售订单</div>
                </div>

                {{-- 采购订单 --}}
                <div class="stat-card">
                    <div class="stat-icon purchase">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div class="stat-value text-emerald-600">{{ $purchaseOrderCount }}</div>
                    <div class="stat-label">采购订单</div>
                </div>

                {{-- 生产工单 --}}
                <div class="stat-card">
                    <div class="stat-icon production">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <div class="stat-value text-orange-600">{{ $productionOrderCount }}</div>
                    <div class="stat-label">生产工单</div>
                </div>

                {{-- 库存物料 --}}
                <div class="stat-card">
                    <div class="stat-icon inventory">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="stat-value text-sky-600">{{ $itemCount }}</div>
                    <div class="stat-label">库存物料品种</div>
                </div>
            </div>
        </div>
    </section>

    {{-- 数据可视化：图表区 --}}
    <section id="charts" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">数据可视化</h2>
                <p class="text-gray-600 mt-2">直观展示业务趋势与分布</p>
            </div>

            {{-- 柱状图 + 饼图 --}}
            <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
                <div class="lg:col-span-4">
                    <div class="chart-card">
                        <div class="card-header">
                            <h5 class="card-title flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#667eea]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                月度销售订单趋势
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="monthlyChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="chart-card">
                        <div class="card-header">
                            <h5 class="card-title flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#667eea]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                                销售订单状态分布
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="statusChart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 效率指标卡片 --}}
            <div class="mt-8">
                <div class="chart-card">
                    <div class="card-header">
                        <h5 class="card-title flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#667eea]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            运营效率指标
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="efficiency-card">
                                <div class="efficiency-value text-[#667eea]">{{ $efficiencyStats['sales_completion_rate'] }}%</div>
                                <div class="efficiency-label">销售订单完成率</div>
                            </div>
                            <div class="efficiency-card">
                                <div class="efficiency-value text-emerald-600">{{ $efficiencyStats['purchase_completion_rate'] }}%</div>
                                <div class="efficiency-label">采购订单完成率</div>
                            </div>
                            <div class="efficiency-card">
                                <div class="efficiency-value text-orange-600">{{ $efficiencyStats['production_completion_rate'] }}%</div>
                                <div class="efficiency-label">生产工单完成率</div>
                            </div>
                            <div class="efficiency-card {{ $efficiencyStats['low_stock_count'] > 0 ? 'border-red-400' : '' }}">
                                <div class="efficiency-value {{ $efficiencyStats['low_stock_count'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ $efficiencyStats['low_stock_count'] }}
                                </div>
                                <div class="efficiency-label {{ $efficiencyStats['low_stock_count'] > 0 ? 'text-red-600' : '' }}">低库存预警</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 页脚 --}}
    <footer class="footer">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-gray-400 text-sm">ERP企业资源管理系统</div>
            <div class="text-gray-400 text-sm">© {{ $currentYear }} ERP系统. 保留所有权利.</div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // —— 月度销售订单趋势（柱状图） ——
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
                grid: { left: '3%', right: '4%', bottom: '3%', top: '10%', containLabel: true },
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

            // —— 销售订单状态分布（饼图） ——
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

            // 响应式调整
            window.addEventListener('resize', function() {
                monthlyChart.resize();
                statusChart.resize();
            });
        });
    </script>
</body>
</html>
