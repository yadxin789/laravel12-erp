<?php

use Illuminate\Support\Facades\Route;

// 欢迎页面：仅展示统计数据图表/卡片与登录入口
Route::get('/', function () {
    $currentYear = date('Y');

    // —— 统计概览（4 张卡片） ——
    $salesOrderCount = \App\Models\Sales\SalesOrder::count();
    $purchaseOrderCount = \App\Models\Purchasing\PurchaseOrder::count();
    $productionOrderCount = \App\Models\Production\ProductionOrder::count();
    $itemCount = \App\Models\MasterData\Item::where('active', true)->count();

    // —— 销售订单状态分布（饼图） ——
    $salesStatusData = [
        '草稿' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Draft)->count(),
        '待审核' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Submitted)->count(),
        '已审核' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Approved)->count(),
        '部分发货' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::PartiallyDelivered)->count(),
        '已发货' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::FullyDelivered)->count(),
        '已关闭' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Closed)->count(),
        '已取消' => \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Cancelled)->count(),
    ];

    // —— 月度销售订单趋势（柱状图） ——
    $monthlySalesData = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlySalesData[$i] = \App\Models\Sales\SalesOrder::whereMonth('created_at', $i)
            ->whereYear('created_at', $currentYear)
            ->count();
    }

    // —— 效率指标 ——
    $salesClosed = \App\Models\Sales\SalesOrder::where('status', \App\Enums\SalesOrderStatus::Closed)->count();
    $purchaseClosed = \App\Models\Purchasing\PurchaseOrder::where('status', \App\Enums\PurchaseOrderStatus::Closed)->count();
    $productionCompleted = \App\Models\Production\ProductionOrder::where('status', \App\Enums\ProductionOrderStatus::Completed)->count();
    $lowStockCount = app(\App\Services\InventoryService::class)->getLowStockItems()->count();

    $efficiencyStats = [
        'sales_completion_rate' => $salesOrderCount > 0 ? round(($salesClosed / $salesOrderCount) * 100) : 0,
        'purchase_completion_rate' => $purchaseOrderCount > 0 ? round(($purchaseClosed / $purchaseOrderCount) * 100) : 0,
        'production_completion_rate' => $productionOrderCount > 0 ? round(($productionCompleted / $productionOrderCount) * 100) : 0,
        'low_stock_count' => $lowStockCount,
    ];

    return view('welcome', compact(
        'currentYear',
        'salesOrderCount',
        'purchaseOrderCount',
        'productionOrderCount',
        'itemCount',
        'salesStatusData',
        'monthlySalesData',
        'efficiencyStats'
    ));
})->name('home');

// ============================================================
// 通知（所有已认证用户可访问）
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    // 附件管理
    Route::post('attachments/upload', [\App\Http\Controllers\System\AttachmentController::class, 'upload'])->name('attachments.upload');
    Route::get('attachments/{attachment}/download', [\App\Http\Controllers\System\AttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('attachments/{attachment}', [\App\Http\Controllers\System\AttachmentController::class, 'destroy'])->name('attachments.destroy');
});

// ============================================================
// 公共仪表盘（所有已认证用户）
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('app/dashboard', [\App\Http\Controllers\App\DashboardController::class, 'index'])->name('app.dashboard');

    // AI 智能助手
    Route::get('app/ai/welcome', function () {
        $current = \App\Models\System\AiSetting::current();
        $providers = \App\Models\System\AiSetting::providers();
        return view('app.ai.welcome', compact('current', 'providers'));
    })->name('app.ai.welcome');
    Route::get('app/ai/chat', [\App\Http\Controllers\App\AiChatController::class, 'index'])->name('app.ai.chat');
    Route::post('app/ai/ask', [\App\Http\Controllers\App\AiChatController::class, 'ask'])->name('app.ai.ask');
    Route::post('app/ai/clear', [\App\Http\Controllers\App\AiChatController::class, 'clear'])->name('app.ai.clear');
    Route::post('app/ai/approval-suggestion', [\App\Http\Controllers\App\AiChatController::class, 'approvalSuggestion'])->name('app.ai.approval-suggestion');
    Route::get('app/ai/invoice-ocr', [\App\Http\Controllers\App\InvoiceOcrController::class, 'index'])->name('app.ai.invoice-ocr');
    Route::post('app/ai/invoice-ocr', [\App\Http\Controllers\App\InvoiceOcrController::class, 'recognize'])->name('app.ai.invoice-ocr.recognize');

    // 财务管理
    Route::get('app/finance/invoices', [\App\Http\Controllers\App\FinanceController::class, 'invoices'])->name('app.finance.invoices');
    Route::get('app/finance/invoices/create', [\App\Http\Controllers\App\FinanceController::class, 'createInvoice'])->name('app.finance.invoices.create');
    Route::post('app/finance/invoices', [\App\Http\Controllers\App\FinanceController::class, 'storeInvoice'])->name('app.finance.invoices.store');
    Route::get('app/finance/bills', [\App\Http\Controllers\App\FinanceController::class, 'bills'])->name('app.finance.bills');
    Route::get('app/finance/bills/create', [\App\Http\Controllers\App\FinanceController::class, 'createBill'])->name('app.finance.bills.create');
    Route::post('app/finance/bills', [\App\Http\Controllers\App\FinanceController::class, 'storeBill'])->name('app.finance.bills.store');
    Route::get('app/finance/payments', [\App\Http\Controllers\App\FinanceController::class, 'payments'])->name('app.finance.payments');
    Route::get('app/finance/payments/create', [\App\Http\Controllers\App\FinanceController::class, 'createPayment'])->name('app.finance.payments.create');
    Route::post('app/finance/payments', [\App\Http\Controllers\App\FinanceController::class, 'storePayment'])->name('app.finance.payments.store');
    Route::get('app/finance/statements', [\App\Http\Controllers\App\FinanceController::class, 'statements'])->name('app.finance.statements');

    // CRM 客户关系管理
    Route::get('app/crm/leads', [\App\Http\Controllers\App\CrmController::class, 'leads'])->name('app.crm.leads');
    Route::get('app/crm/leads/create', [\App\Http\Controllers\App\CrmController::class, 'createLead'])->name('app.crm.leads.create');
    Route::post('app/crm/leads', [\App\Http\Controllers\App\CrmController::class, 'storeLead'])->name('app.crm.leads.store');
    Route::get('app/crm/leads/{lead}', [\App\Http\Controllers\App\CrmController::class, 'showLead'])->name('app.crm.leads.show');
    Route::post('app/crm/leads/{lead}/follow-up', [\App\Http\Controllers\App\CrmController::class, 'addFollowUp'])->name('app.crm.leads.follow-up');
    Route::get('app/crm/opportunities', [\App\Http\Controllers\App\CrmController::class, 'opportunities'])->name('app.crm.opportunities');
    Route::get('app/crm/opportunities/create', [\App\Http\Controllers\App\CrmController::class, 'createOpportunity'])->name('app.crm.opportunities.create');
    Route::post('app/crm/opportunities', [\App\Http\Controllers\App\CrmController::class, 'storeOpportunity'])->name('app.crm.opportunities.store');
    Route::get('app/crm/pipeline', [\App\Http\Controllers\App\CrmController::class, 'pipeline'])->name('app.crm.pipeline');

    // 我的审批待办
    Route::get('app/approvals/my-tasks', [\App\Http\Controllers\Admin\ApprovalFlowController::class, 'myTasks'])->name('app.approvals.my-tasks');
    Route::post('app/approvals/{task}/handle', [\App\Http\Controllers\Admin\ApprovalFlowController::class, 'handleTask'])->name('app.approvals.handle');

    // 报表中心
    Route::get('app/reports', [\App\Http\Controllers\App\ReportController::class, 'index'])->name('app.reports.index');
    Route::get('app/reports/sales', [\App\Http\Controllers\App\ReportController::class, 'sales'])->name('app.reports.sales');
    Route::get('app/reports/purchase', [\App\Http\Controllers\App\ReportController::class, 'purchase'])->name('app.reports.purchase');
    Route::get('app/reports/inventory', [\App\Http\Controllers\App\ReportController::class, 'inventory'])->name('app.reports.inventory');
    Route::get('app/reports/quality', [\App\Http\Controllers\App\ReportController::class, 'quality'])->name('app.reports.quality');

    // AI 智能分析中心
    Route::get('app/ai/analysis', [\App\Http\Controllers\App\AIAnalysisController::class, 'index'])->name('app.ai.analysis-center');
    Route::get('app/ai/daily-report', [\App\Http\Controllers\App\AIAnalysisController::class, 'dailyReport'])->name('app.ai.daily-report');
    Route::get('app/ai/stock-alerts', [\App\Http\Controllers\App\AIAnalysisController::class, 'stockAlerts'])->name('app.ai.stock-alerts');
    Route::get('app/ai/customer-credit', [\App\Http\Controllers\App\AIAnalysisController::class, 'customerCredit'])->name('app.ai.customer-credit');
    Route::get('app/ai/supplier-quality', [\App\Http\Controllers\App\AIAnalysisController::class, 'supplierQuality'])->name('app.ai.supplier-quality');
    Route::get('app/ai/sales-forecast', [\App\Http\Controllers\App\AIAnalysisController::class, 'salesForecast'])->name('app.ai.sales-forecast');
    Route::get('app/ai/production-cost', [\App\Http\Controllers\App\AIAnalysisController::class, 'productionCost'])->name('app.ai.production-cost');
    Route::get('app/ai/anomaly-detection', [\App\Http\Controllers\App\AIAnalysisController::class, 'anomalyDetection'])->name('app.ai.anomaly-detection');
    Route::get('app/ai/scheduling', [\App\Http\Controllers\App\AIAnalysisController::class, 'scheduling'])->name('app.ai.scheduling');
    Route::get('app/ai/ecommerce-merge', [\App\Http\Controllers\App\AIAnalysisController::class, 'ecommerceMerge'])->name('app.ai.ecommerce-merge');
    Route::match(['get','post'], 'app/ai/email-reply', [\App\Http\Controllers\App\AIAnalysisController::class, 'emailReply'])->name('app.ai.email-reply');

    // P3 扩展模块
    Route::get('app/hr/employees', [\App\Http\Controllers\App\P3Controller::class, 'employees'])->name('app.hr.employees');
    Route::post('app/hr/employees', [\App\Http\Controllers\App\P3Controller::class, 'storeEmployee'])->name('app.hr.employees.store');
    Route::get('app/hr/attendance', [\App\Http\Controllers\App\P3Controller::class, 'attendance'])->name('app.hr.attendance');
    Route::get('app/projects', [\App\Http\Controllers\App\P3Controller::class, 'projects'])->name('app.projects');
    Route::post('app/projects', [\App\Http\Controllers\App\P3Controller::class, 'storeProject'])->name('app.projects.store');
    Route::get('app/assets', [\App\Http\Controllers\App\P3Controller::class, 'assets'])->name('app.assets');
    Route::post('app/assets', [\App\Http\Controllers\App\P3Controller::class, 'storeAsset'])->name('app.assets.store');
    Route::get('app/ecommerce', [\App\Http\Controllers\App\P3Controller::class, 'ecommerce'])->name('app.ecommerce');
    Route::post('app/ecommerce/sync', [\App\Http\Controllers\App\P3Controller::class, 'syncEcommerce'])->name('app.ecommerce.sync');
});

// ============================================================
// 系统管理 —— 仅 admin
// ============================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['show']);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->except(['show']);

    // 主数据管理
    Route::get('items', [\App\Http\Controllers\Admin\MasterDataController::class, 'items'])->name('items.index');
    Route::get('items/barcode-labels', [\App\Http\Controllers\MasterData\ItemController::class, 'barcodeLabels'])->name('items.barcode-labels');
    Route::get('items/create', [\App\Http\Controllers\MasterData\ItemController::class, 'create'])->name('items.create');
    Route::post('items', [\App\Http\Controllers\MasterData\ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/barcode-label', [\App\Http\Controllers\MasterData\ItemController::class, 'barcodeLabel'])->name('items.barcode-label');
    Route::get('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'show'])->name('items.show');
    Route::get('items/{item}/edit', [\App\Http\Controllers\MasterData\ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'update'])->name('items.update');
    Route::delete('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('customers', [\App\Http\Controllers\Admin\MasterDataController::class, 'customers'])->name('customers.index');
    Route::get('customers/create', [\App\Http\Controllers\MasterData\CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [\App\Http\Controllers\MasterData\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [\App\Http\Controllers\MasterData\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('suppliers', [\App\Http\Controllers\Admin\MasterDataController::class, 'suppliers'])->name('suppliers.index');
    Route::get('suppliers/create', [\App\Http\Controllers\MasterData\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('suppliers', [\App\Http\Controllers\MasterData\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('suppliers/{supplier}/edit', [\App\Http\Controllers\MasterData\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('warehouses', [\App\Http\Controllers\Admin\MasterDataController::class, 'warehouses'])->name('warehouses.index');
    Route::get('warehouses/create', [\App\Http\Controllers\MasterData\WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [\App\Http\Controllers\MasterData\WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}', [\App\Http\Controllers\MasterData\WarehouseController::class, 'show'])->name('warehouses.show');
    Route::get('warehouses/{warehouse}/edit', [\App\Http\Controllers\MasterData\WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('warehouses/{warehouse}', [\App\Http\Controllers\MasterData\WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('warehouses/{warehouse}', [\App\Http\Controllers\MasterData\WarehouseController::class, 'destroy'])->name('warehouses.destroy');

    Route::get('boms', [\App\Http\Controllers\Admin\MasterDataController::class, 'boms'])->name('boms.index');
    Route::get('routings', [\App\Http\Controllers\Admin\MasterDataController::class, 'routings'])->name('routings.index');
    Route::get('routings/{routing}', [\App\Http\Controllers\Admin\MasterDataController::class, 'routingShow'])->name('routings.show');
    Route::get('settings', function () { return view('admin.settings.index'); })->name('settings.index');

    // AI 设置
    Route::get('ai-settings', [\App\Http\Controllers\Admin\AiSettingController::class, 'index'])->name('ai-settings.index');
    Route::put('ai-settings', [\App\Http\Controllers\Admin\AiSettingController::class, 'update'])->name('ai-settings.update');
    Route::post('ai-settings/test', [\App\Http\Controllers\Admin\AiSettingController::class, 'test'])->name('ai-settings.test');

    // 审批流配置
    Route::get('approval-flows', [\App\Http\Controllers\Admin\ApprovalFlowController::class, 'index'])->name('approval-flows.index');
    Route::post('approval-flows', [\App\Http\Controllers\Admin\ApprovalFlowController::class, 'store'])->name('approval-flows.store');

    Route::get('audit-logs', function () {
        $logs = \App\Models\System\AuditLog::latest()->paginate(20);
        return view('admin.audit-logs.index', compact('logs'));
    })->name('audit-logs.index');
});

// ============================================================
// 销售管理
// 操作者: admin, sales_manager
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|sales_manager|accountant|viewer'])->prefix('app/sales')->name('app.sales.')->group(function () {
    Route::resource('orders', \App\Http\Controllers\App\Sales\SalesOrderController::class)->except(['destroy']);
    Route::post('orders/{order}/submit', [\App\Http\Controllers\App\Sales\SalesOrderController::class, 'submit'])->name('orders.submit');
    Route::post('orders/{order}/approve', [\App\Http\Controllers\App\Sales\SalesOrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\App\Sales\SalesOrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/close', [\App\Http\Controllers\App\Sales\SalesOrderController::class, 'close'])->name('orders.close');
    Route::resource('quotations', \App\Http\Controllers\App\Sales\QuotationController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('quotations/{quotation}/submit', [\App\Http\Controllers\App\Sales\QuotationController::class, 'submit'])->name('quotations.submit');
    Route::post('quotations/{quotation}/convert', [\App\Http\Controllers\App\Sales\QuotationController::class, 'convert'])->name('quotations.convert');
    Route::resource('shipments', \App\Http\Controllers\App\Sales\ShipmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('shipments/{shipment}/confirm', [\App\Http\Controllers\App\Sales\ShipmentController::class, 'confirm'])->name('shipments.confirm');
    Route::resource('returns', \App\Http\Controllers\App\Sales\SalesReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('returns/{return}/approve', [\App\Http\Controllers\App\Sales\SalesReturnController::class, 'approve'])->name('returns.approve');
});

// ============================================================
// 采购管理
// 操作者: admin, purchase_manager
// 跨模块: warehouse_manager (收货), quality_manager (来料检验参考)
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|purchase_manager|warehouse_manager|quality_manager|accountant|viewer'])->prefix('app/purchase')->name('app.purchase.')->group(function () {
    Route::resource('orders', \App\Http\Controllers\App\Purchase\PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('orders/{order}/submit', [\App\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'submit'])->name('orders.submit');
    Route::post('orders/{order}/approve', [\App\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\App\Purchase\PurchaseOrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('requisitions', \App\Http\Controllers\App\Purchase\PurchaseRequisitionController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('requisitions/{requisition}/submit', [\App\Http\Controllers\App\Purchase\PurchaseRequisitionController::class, 'submit'])->name('requisitions.submit');
    Route::post('requisitions/{requisition}/approve', [\App\Http\Controllers\App\Purchase\PurchaseRequisitionController::class, 'approve'])->name('requisitions.approve');
    Route::resource('receipts', \App\Http\Controllers\App\Purchase\ReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('receipts/{receipt}/confirm', [\App\Http\Controllers\App\Purchase\ReceiptController::class, 'confirm'])->name('receipts.confirm');
    Route::resource('returns', \App\Http\Controllers\App\Purchase\PurchaseReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('returns/{return}/approve', [\App\Http\Controllers\App\Purchase\PurchaseReturnController::class, 'approve'])->name('returns.approve');
});

// ============================================================
// 生产管理
// 操作者: admin, production_manager
// 跨模块: warehouse_manager (发料出库, 成品入库确认)
//          quality_manager (过程检验/出货检验参考)
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|production_manager|warehouse_manager|quality_manager|accountant|viewer'])->prefix('app/production')->name('app.production.')->group(function () {
    Route::resource('orders', \App\Http\Controllers\App\Production\ProductionOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('orders/{order}/release', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'release'])->name('orders.release');
    Route::post('orders/{order}/start', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'start'])->name('orders.start');
    Route::post('orders/{order}/report', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'report'])->name('orders.report');
    Route::post('orders/{order}/complete', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'complete'])->name('orders.complete');
    Route::post('orders/{order}/close', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'close'])->name('orders.close');
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\App\Production\ProductionOrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('plans', \App\Http\Controllers\App\Production\ProductionPlanController::class);
    Route::post('plans/mrp', [\App\Http\Controllers\App\Production\ProductionPlanController::class, 'runMrp'])->name('plans.mrp');
    Route::post('plans/{plan}/approve', [\App\Http\Controllers\App\Production\ProductionPlanController::class, 'approve'])->name('plans.approve');
    Route::post('plans/{plan}/convert', [\App\Http\Controllers\App\Production\ProductionPlanController::class, 'convertToOrders'])->name('plans.convert');
    Route::resource('issues', \App\Http\Controllers\App\Production\MaterialIssueController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('issues/{issue}/confirm', [\App\Http\Controllers\App\Production\MaterialIssueController::class, 'confirm'])->name('issues.confirm');
    Route::resource('reports', \App\Http\Controllers\App\Production\ProductionReportController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('receipts', \App\Http\Controllers\App\Production\ProductionReceiptController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('receipts/{receipt}/confirm', [\App\Http\Controllers\App\Production\ProductionReceiptController::class, 'confirm'])->name('receipts.confirm');
});

// ============================================================
// 库存管理
// 操作者: admin, warehouse_manager
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|warehouse_manager|accountant|viewer'])->prefix('app/inventory')->name('app.inventory.')->group(function () {
    Route::get('/', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'index'])->name('index');
    Route::get('/transfer', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'transfer'])->name('transfer');
    Route::post('/transfer', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeTransfer'])->name('transfer.store');
    Route::get('/adjust', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'adjust'])->name('adjust');
    Route::post('/adjust', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeAdjust'])->name('adjust.store');
    Route::get('/freeze', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'freeze'])->name('freeze');
    Route::post('/freeze', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeFreeze'])->name('freeze.store');
    Route::get('/stocktake', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'stocktake'])->name('stocktake');
    Route::post('/stocktake', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeStocktake'])->name('stocktake.store');
    Route::get('/trace', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'trace'])->name('trace');
    Route::post('/trace', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'searchTrace'])->name('trace.search');
});

// ============================================================
// 质检管理
// 操作者: admin, quality_manager
// 只读:   accountant, viewer
// ============================================================
Route::middleware(['auth', 'role:admin|quality_manager|accountant|viewer'])->prefix('app/quality')->name('app.quality.')->group(function () {
    Route::resource('inspections', \App\Http\Controllers\App\Quality\QualityController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('defects', function () {
        $defectCodes = \App\Models\Quality\DefectCode::orderBy('category')->paginate(20);
        $defectRecords = \App\Models\Quality\DefectRecord::with(['defectCode', 'item'])->latest()->take(10)->get();
        return view('app.quality.defects.index', compact('defectCodes', 'defectRecords'));
    })->name('defects.index');
    Route::get('reports', [\App\Http\Controllers\App\Quality\QualityController::class, 'reports'])->name('reports');
});

require __DIR__.'/auth.php';
