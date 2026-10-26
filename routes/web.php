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

    // 首页始终展示欢迎页（不因登录状态跳转仪表盘），并禁止浏览器缓存，
    // 避免登录后再次访问站点时看到过期的仪表盘页面。
    return response()
        ->view('welcome', compact(
            'currentYear',
            'salesOrderCount',
            'purchaseOrderCount',
            'productionOrderCount',
            'itemCount',
            'salesStatusData',
            'monthlySalesData',
            'efficiencyStats'
        ))
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('home');

// ============================================================
// 通知（所有已认证用户可访问）
// ============================================================
Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
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

    // 主数据只读：业务角色在侧边栏查看物料/客户/供应商/仓库/BOM/工艺
    // 写操作（创建/编辑/删除）仍然只走后台 admin.* 路由
    Route::prefix('app/masterdata')->name('app.masterdata.')->group(function () {
        Route::get('items', [\App\Http\Controllers\Admin\MasterDataController::class, 'items'])->name('items.index');
        Route::get('customers', [\App\Http\Controllers\Admin\MasterDataController::class, 'customers'])->name('customers.index');
        Route::get('suppliers', [\App\Http\Controllers\Admin\MasterDataController::class, 'suppliers'])->name('suppliers.index');
        Route::get('warehouses', [\App\Http\Controllers\Admin\MasterDataController::class, 'warehouses'])->name('warehouses.index');
        Route::get('boms', [\App\Http\Controllers\Admin\MasterDataController::class, 'boms'])->name('boms.index');
        Route::get('routings', [\App\Http\Controllers\Admin\MasterDataController::class, 'routings'])->name('routings.index');
    });

    // 主数据详情只读（路由名沿用 admin.*，视图无需改动即可对所有角色可用）
    Route::prefix('app/masterdata')->name('admin.')->group(function () {
        Route::get('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'show'])->name('items.show');
        // 物料规格书（仅 PDF）：上传 / 预览 / 下载 / 删除
        Route::get('items/{item}/spec-sheet', [\App\Http\Controllers\MasterData\ItemController::class, 'specSheet'])->name('items.spec-sheet');
        Route::post('items/{item}/spec-sheet', [\App\Http\Controllers\MasterData\ItemController::class, 'uploadSpecSheet'])->name('items.spec-sheet.upload');
        Route::get('items/{item}/spec-sheet/preview', [\App\Http\Controllers\MasterData\ItemController::class, 'previewSpecSheet'])->name('items.spec-sheet.preview');
        Route::get('items/{item}/spec-sheet/download', [\App\Http\Controllers\MasterData\ItemController::class, 'downloadSpecSheet'])->name('items.spec-sheet.download');
        Route::delete('items/{item}/spec-sheet', [\App\Http\Controllers\MasterData\ItemController::class, 'deleteSpecSheet'])->name('items.spec-sheet.destroy');
        Route::get('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'show'])->name('customers.show');
        Route::get('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('warehouses/{warehouse}', [\App\Http\Controllers\MasterData\WarehouseController::class, 'show'])->name('warehouses.show');
    });

    // AI 智能助手
    // 开通引导页：仅管理员可见（管理员负责开通与维护）；其它角色跳「AI 暂未开通」提示页
    Route::get('app/ai/welcome', function () {
        if (! auth()->user()?->hasRole('admin')) {
            return redirect()->route('app.ai.notice');
        }
        $current = \App\Models\System\AiSetting::current();
        $providers = \App\Models\System\AiSetting::providers();
        return view('app.ai.welcome', compact('current', 'providers'));
    })->name('app.ai.welcome');

    // AI 暂未开通提示页
    Route::get('app/ai/notice', [\App\Http\Controllers\App\AiChatController::class, 'notice'])->name('app.ai.notice');
    Route::get('app/ai/chat', [\App\Http\Controllers\App\AiChatController::class, 'index'])->name('app.ai.chat');
    Route::post('app/ai/chat/ask', [\App\Http\Controllers\App\AiChatController::class, 'ask'])->name('app.ai.chat.ask');

    // 会话管理
    Route::post('app/ai/chat/conversations', [\App\Http\Controllers\App\AiChatController::class, 'storeConversation'])->name('app.ai.chat.conversations.store');
    Route::put('app/ai/chat/conversations/{conversation}/rename', [\App\Http\Controllers\App\AiChatController::class, 'renameConversation'])->name('app.ai.chat.conversations.rename');
    Route::post('app/ai/chat/conversations/{conversation}/clear', [\App\Http\Controllers\App\AiChatController::class, 'clearConversation'])->name('app.ai.chat.conversations.clear');
    Route::delete('app/ai/chat/conversations/{conversation}', [\App\Http\Controllers\App\AiChatController::class, 'destroyConversation'])->name('app.ai.chat.conversations.destroy');
    Route::get('app/ai/chat/conversations/{conversation}/export', [\App\Http\Controllers\App\AiChatController::class, 'exportConversation'])->name('app.ai.chat.conversations.export');

    // 附件
    Route::get('app/ai/chat/attachments/{attachment}/preview', [\App\Http\Controllers\App\AiChatController::class, 'previewAttachment'])->name('app.ai.chat.attachments.preview');
    Route::get('app/ai/chat/attachments/{attachment}/download', [\App\Http\Controllers\App\AiChatController::class, 'downloadAttachment'])->name('app.ai.chat.attachments.download');
    Route::post('app/ai/approval-suggestion', [\App\Http\Controllers\App\AiChatController::class, 'approvalSuggestion'])->name('app.ai.approval-suggestion');
    Route::get('app/ai/invoice-ocr', [\App\Http\Controllers\App\InvoiceOcrController::class, 'index'])->name('app.ai.invoice-ocr');
    Route::post('app/ai/invoice-ocr', [\App\Http\Controllers\App\InvoiceOcrController::class, 'recognize'])->name('app.ai.invoice-ocr.recognize');

    // 异步识别（走队列）：提交后返回任务 ID，前端轮询结果
    Route::post('app/ai/ocr', [\App\Http\Controllers\App\InvoiceOcrController::class, 'store'])->name('app.ai.ocr.store');
    Route::get('app/ai/ocr/{ocr}', [\App\Http\Controllers\App\InvoiceOcrController::class, 'show'])->name('app.ai.ocr.show');

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

    // 即时消息：群聊 + 私聊（所有登录用户）
    // 个人资料与头像
    Route::get('app/profile', [\App\Http\Controllers\App\ProfileController::class, 'edit'])->name('app.profile.edit');
    Route::put('app/profile', [\App\Http\Controllers\App\ProfileController::class, 'update'])->name('app.profile.update');
    Route::get('app/profile/avatar', [\App\Http\Controllers\App\ProfileController::class, 'avatar'])->name('app.profile.avatar');
    Route::post('app/profile/avatar', [\App\Http\Controllers\App\ProfileController::class, 'uploadAvatar'])->name('app.profile.avatar.upload');
    Route::delete('app/profile/avatar', [\App\Http\Controllers\App\ProfileController::class, 'resetAvatar'])->name('app.profile.avatar.reset');

    Route::get('app/chat', [\App\Http\Controllers\App\ChatController::class, 'index'])->name('app.chat');
    Route::post('app/chat/send', [\App\Http\Controllers\App\ChatController::class, 'send'])->name('app.chat.send');
    Route::get('app/chat/messages', [\App\Http\Controllers\App\ChatController::class, 'messages'])->name('app.chat.messages');
    Route::post('app/chat/clear', [\App\Http\Controllers\App\ChatController::class, 'clearMine'])->name('app.chat.clear');
    Route::get('app/chat/files/{message}', [\App\Http\Controllers\App\ChatController::class, 'file'])->name('app.chat.file');
    Route::delete('app/chat/messages/{message}', [\App\Http\Controllers\App\ChatController::class, 'destroy'])->name('app.chat.destroy');

    Route::get('app/chat/members', [\App\Http\Controllers\App\PrivateChatController::class, 'members'])->name('app.chat.members');
    Route::get('app/chat/private/{user}', [\App\Http\Controllers\App\PrivateChatController::class, 'show'])->name('app.chat.private');
    Route::post('app/chat/private/{user}/send', [\App\Http\Controllers\App\PrivateChatController::class, 'send'])->name('app.chat.private.send');
    Route::get('app/chat/private/{user}/history', [\App\Http\Controllers\App\PrivateChatController::class, 'history'])->name('app.chat.private.history');
    Route::post('app/chat/private/{user}/clear', [\App\Http\Controllers\App\PrivateChatController::class, 'clearMine'])->name('app.chat.private.clear');

    // ============ 人力资源模块（人事专员 / 只读访客）============
    Route::middleware('role:hr_specialist|viewer')->prefix('app/hr')->name('app.hr.')->group(function () {
        Route::get('employees', [\App\Http\Controllers\App\HrController::class, 'employees'])->name('employees.index');
        Route::get('employees/create', [\App\Http\Controllers\App\HrController::class, 'create'])->name('employees.create');
        Route::post('employees', [\App\Http\Controllers\App\HrController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}', [\App\Http\Controllers\App\HrController::class, 'show'])->name('employees.show');
        Route::get('employees/{employee}/edit', [\App\Http\Controllers\App\HrController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [\App\Http\Controllers\App\HrController::class, 'update'])->name('employees.update');
        Route::post('employees/sync-roles', [\App\Http\Controllers\App\HrController::class, 'syncRoles'])->name('employees.sync-roles');
        Route::post('employees/{employee}/leave', [\App\Http\Controllers\App\HrController::class, 'leave'])->name('employees.leave');
        Route::delete('employees/{employee}', [\App\Http\Controllers\App\HrController::class, 'destroy'])->name('employees.destroy');

        Route::get('attendances', [\App\Http\Controllers\App\HrController::class, 'attendance'])->name('attendances.index');
        Route::get('attendances/create', [\App\Http\Controllers\App\HrController::class, 'attendanceCreate'])->name('attendances.create');
        Route::post('attendances', [\App\Http\Controllers\App\HrController::class, 'attendanceStore'])->name('attendances.store');
        Route::get('attendances/{attendance}/edit', [\App\Http\Controllers\App\HrController::class, 'attendanceEdit'])->name('attendances.edit');
        Route::put('attendances/{attendance}', [\App\Http\Controllers\App\HrController::class, 'attendanceUpdate'])->name('attendances.update');
        Route::delete('attendances/{attendance}', [\App\Http\Controllers\App\HrController::class, 'attendanceDestroy'])->name('attendances.destroy');

        // 薪资管理
        Route::get('payroll', [\App\Http\Controllers\App\HrController::class, 'payroll'])->name('payroll.index');
        Route::get('payroll/create', [\App\Http\Controllers\App\HrController::class, 'payrollCreate'])->name('payroll.create');
        Route::post('payroll', [\App\Http\Controllers\App\HrController::class, 'payrollStore'])->name('payroll.store');
        Route::get('payroll/{salary}/edit', [\App\Http\Controllers\App\HrController::class, 'payrollEdit'])->name('payroll.edit');
        Route::put('payroll/{salary}', [\App\Http\Controllers\App\HrController::class, 'payrollUpdate'])->name('payroll.update');
        Route::post('payroll/{salary}/status', [\App\Http\Controllers\App\HrController::class, 'payrollStatus'])->name('payroll.status');
        Route::delete('payroll/{salary}', [\App\Http\Controllers\App\HrController::class, 'payrollDestroy'])->name('payroll.destroy');

        // 绩效考核
        Route::get('performances', [\App\Http\Controllers\App\HrController::class, 'performance'])->name('performances.index');
        Route::get('performances/create', [\App\Http\Controllers\App\HrController::class, 'performanceCreate'])->name('performances.create');
        Route::post('performances', [\App\Http\Controllers\App\HrController::class, 'performanceStore'])->name('performances.store');
        Route::get('performances/{review}/edit', [\App\Http\Controllers\App\HrController::class, 'performanceEdit'])->name('performances.edit');
        Route::put('performances/{review}', [\App\Http\Controllers\App\HrController::class, 'performanceUpdate'])->name('performances.update');
        Route::delete('performances/{review}', [\App\Http\Controllers\App\HrController::class, 'performanceDestroy'])->name('performances.destroy');

        // 招聘需求
        Route::get('recruitments', [\App\Http\Controllers\App\HrController::class, 'recruitment'])->name('recruitments.index');
        Route::get('recruitments/create', [\App\Http\Controllers\App\HrController::class, 'recruitmentCreate'])->name('recruitments.create');
        Route::post('recruitments', [\App\Http\Controllers\App\HrController::class, 'recruitmentStore'])->name('recruitments.store');
        Route::get('recruitments/{recruitment}/edit', [\App\Http\Controllers\App\HrController::class, 'recruitmentEdit'])->name('recruitments.edit');
        Route::put('recruitments/{recruitment}', [\App\Http\Controllers\App\HrController::class, 'recruitmentUpdate'])->name('recruitments.update');
        Route::delete('recruitments/{recruitment}', [\App\Http\Controllers\App\HrController::class, 'recruitmentDestroy'])->name('recruitments.destroy');

        // 应聘者
        Route::get('applications', [\App\Http\Controllers\App\HrController::class, 'applications'])->name('applications.index');
        Route::get('applications/create', [\App\Http\Controllers\App\HrController::class, 'applicationCreate'])->name('applications.create');
        Route::post('applications', [\App\Http\Controllers\App\HrController::class, 'applicationStore'])->name('applications.store');
        Route::get('applications/{application}/edit', [\App\Http\Controllers\App\HrController::class, 'applicationEdit'])->name('applications.edit');
        Route::put('applications/{application}', [\App\Http\Controllers\App\HrController::class, 'applicationUpdate'])->name('applications.update');
        Route::post('applications/{application}/status', [\App\Http\Controllers\App\HrController::class, 'applicationStatus'])->name('applications.status');
        Route::post('applications/{application}/to-employee', [\App\Http\Controllers\App\HrController::class, 'applicationToEmployee'])->name('applications.to-employee');
        Route::delete('applications/{application}', [\App\Http\Controllers\App\HrController::class, 'applicationDestroy'])->name('applications.destroy');
    });

    // ============ 项目管理模块（项目经理 / 只读访客）============
    Route::middleware('role:project_manager|viewer')->prefix('app/projects')->name('app.projects.')->group(function () {
        Route::get('/', [\App\Http\Controllers\App\ProjectController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\App\ProjectController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\App\ProjectController::class, 'store'])->name('store');
        Route::get('{project}', [\App\Http\Controllers\App\ProjectController::class, 'show'])->name('show');
        Route::get('{project}/edit', [\App\Http\Controllers\App\ProjectController::class, 'edit'])->name('edit');
        Route::put('{project}', [\App\Http\Controllers\App\ProjectController::class, 'update'])->name('update');
        Route::delete('{project}', [\App\Http\Controllers\App\ProjectController::class, 'destroy'])->name('destroy');

        Route::post('{project}/tasks', [\App\Http\Controllers\App\ProjectController::class, 'storeTask'])->name('tasks.store');
        Route::put('tasks/{task}', [\App\Http\Controllers\App\ProjectController::class, 'updateTask'])->name('tasks.update');
        Route::delete('tasks/{task}', [\App\Http\Controllers\App\ProjectController::class, 'destroyTask'])->name('tasks.destroy');
        Route::post('tasks/{task}/time', [\App\Http\Controllers\App\ProjectController::class, 'storeTime'])->name('tasks.time.store');
        Route::delete('time/{entry}', [\App\Http\Controllers\App\ProjectController::class, 'destroyTime'])->name('time.destroy');
    });

    // ============ 固定资产模块（资产管理员 / 只读访客）============
    Route::middleware('role:asset_manager|viewer')->prefix('app/assets')->name('app.assets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\App\AssetController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\App\AssetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\App\AssetController::class, 'store'])->name('store');
        Route::get('{asset}', [\App\Http\Controllers\App\AssetController::class, 'show'])->name('show');
        Route::get('{asset}/edit', [\App\Http\Controllers\App\AssetController::class, 'edit'])->name('edit');
        Route::put('{asset}', [\App\Http\Controllers\App\AssetController::class, 'update'])->name('update');
        Route::post('{asset}/status', [\App\Http\Controllers\App\AssetController::class, 'changeStatus'])->name('status');
        Route::delete('{asset}', [\App\Http\Controllers\App\AssetController::class, 'destroy'])->name('destroy');
    });

    // ============ 电商订单模块（电商运营 / 只读访客）============
    Route::middleware('role:ecommerce_operator|viewer')->prefix('app/ecommerce')->name('app.ecommerce.')->group(function () {
        Route::get('/', [\App\Http\Controllers\App\EcommerceController::class, 'index'])->name('index');
        Route::post('sync', [\App\Http\Controllers\App\EcommerceController::class, 'sync'])->name('sync');
        Route::get('create', [\App\Http\Controllers\App\EcommerceController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\App\EcommerceController::class, 'store'])->name('store');
        Route::get('{order}', [\App\Http\Controllers\App\EcommerceController::class, 'show'])->name('show');
        Route::get('{order}/edit', [\App\Http\Controllers\App\EcommerceController::class, 'edit'])->name('edit');
        Route::put('{order}', [\App\Http\Controllers\App\EcommerceController::class, 'update'])->name('update');
        Route::post('{order}/status', [\App\Http\Controllers\App\EcommerceController::class, 'changeStatus'])->name('status');
        Route::delete('{order}', [\App\Http\Controllers\App\EcommerceController::class, 'destroy'])->name('destroy');
    });
});

// ============================================================
// 系统管理 —— 仅 admin
// ============================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // 系统监控：健康检查 / 告警 / 备份台账
    Route::get('system-health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index'])->name('system-health');
    Route::post('system-health/run', [\App\Http\Controllers\Admin\SystemHealthController::class, 'run'])->name('system-health.run');
    Route::post('system-health/alerts/{alert}/resolve', [\App\Http\Controllers\Admin\SystemHealthController::class, 'resolveAlert'])->name('system-health.alerts.resolve');

    // 运维监控：系统工作状态 / 定时任务 / 队列 / 自启动程序
    Route::prefix('monitor')->name('monitor.')->group(function () {
        $status = \App\Http\Controllers\Admin\SystemStatusController::class;
        Route::get('system-status', [$status, 'index'])->name('system-status');
        Route::post('system-status/run-health', [$status, 'runHealth'])->name('system-status.run-health');
        Route::post('system-status/clear-cache', [$status, 'clearCache'])->name('system-status.clear-cache');

        $tasks = \App\Http\Controllers\Admin\ScheduledTaskController::class;
        Route::get('scheduled-tasks', [$tasks, 'index'])->name('scheduled-tasks');
        Route::post('scheduled-tasks/run', [$tasks, 'run'])->name('scheduled-tasks.run');

        $queue = \App\Http\Controllers\Admin\QueueMonitorController::class;
        Route::get('queue', [$queue, 'index'])->name('queue');
        Route::post('queue/failures/retry-all', [$queue, 'retryAll'])->name('queue.retry-all');
        Route::post('queue/failures/flush', [$queue, 'flush'])->name('queue.flush');
        Route::post('queue/failures/{id}/retry', [$queue, 'retry'])->name('queue.retry');
        Route::post('queue/failures/{id}/forget', [$queue, 'forget'])->name('queue.forget');
        Route::post('queue/restart', [$queue, 'restart'])->name('queue.restart');

        Route::get('auto-start', [\App\Http\Controllers\Admin\AutoStartController::class, 'index'])->name('auto-start');
    });

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show']);
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->except(['show']);
    Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class)->except(['show']);

    // 组织架构主数据（部门 / 岗位）—— 只描述组织归属，不参与授权
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['show']);
    Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class)->except(['show']);

    // 主数据管理
    Route::get('items', [\App\Http\Controllers\Admin\MasterDataController::class, 'items'])->name('items.index');
    Route::get('items/barcode-labels', [\App\Http\Controllers\MasterData\ItemController::class, 'barcodeLabels'])->name('items.barcode-labels');
    Route::get('items/create', [\App\Http\Controllers\MasterData\ItemController::class, 'create'])->name('items.create');
    Route::post('items', [\App\Http\Controllers\MasterData\ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/barcode-label', [\App\Http\Controllers\MasterData\ItemController::class, 'barcodeLabel'])->name('items.barcode-label');
    Route::get('items/{item}/edit', [\App\Http\Controllers\MasterData\ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'update'])->name('items.update');
    Route::delete('items/{item}', [\App\Http\Controllers\MasterData\ItemController::class, 'destroy'])->name('items.destroy');

    Route::get('customers', [\App\Http\Controllers\Admin\MasterDataController::class, 'customers'])->name('customers.index');
    Route::get('customers/create', [\App\Http\Controllers\MasterData\CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [\App\Http\Controllers\MasterData\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{customer}/edit', [\App\Http\Controllers\MasterData\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('customers/{customer}', [\App\Http\Controllers\MasterData\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('suppliers', [\App\Http\Controllers\Admin\MasterDataController::class, 'suppliers'])->name('suppliers.index');
    Route::get('suppliers/create', [\App\Http\Controllers\MasterData\SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('suppliers', [\App\Http\Controllers\MasterData\SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('suppliers/{supplier}/edit', [\App\Http\Controllers\MasterData\SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('suppliers/{supplier}', [\App\Http\Controllers\MasterData\SupplierController::class, 'destroy'])->name('suppliers.destroy');

    Route::get('warehouses', [\App\Http\Controllers\Admin\MasterDataController::class, 'warehouses'])->name('warehouses.index');
    Route::get('warehouses/create', [\App\Http\Controllers\MasterData\WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [\App\Http\Controllers\MasterData\WarehouseController::class, 'store'])->name('warehouses.store');
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

    Route::prefix('ai-settings/providers/{provider}')->name('ai-settings.providers.')->group(function () {
        Route::post('save', [\App\Http\Controllers\Admin\AiSettingController::class, 'saveProvider'])->name('save');
        Route::post('toggle', [\App\Http\Controllers\Admin\AiSettingController::class, 'toggleProvider'])->name('toggle');
        Route::post('default', [\App\Http\Controllers\Admin\AiSettingController::class, 'setDefaultProvider'])->name('default');
        Route::post('test', [\App\Http\Controllers\Admin\AiSettingController::class, 'testProvider'])->name('test');
        Route::post('sync', [\App\Http\Controllers\Admin\AiSettingController::class, 'syncProviderModels'])->name('sync');
        Route::delete('remove', [\App\Http\Controllers\Admin\AiSettingController::class, 'removeProvider'])->name('remove');
    });

    Route::prefix('ai-settings/models')->name('ai-settings.models.')->group(function () {
        Route::post('store', [\App\Http\Controllers\Admin\AiSettingController::class, 'storeModel'])->name('store');
        Route::put('{model}', [\App\Http\Controllers\Admin\AiSettingController::class, 'updateModel'])->name('update');
        Route::post('{model}/default', [\App\Http\Controllers\Admin\AiSettingController::class, 'setDefaultModel'])->name('default');
        Route::delete('{model}', [\App\Http\Controllers\Admin\AiSettingController::class, 'destroyModel'])->name('destroy');
    });

    // 物流快递设置（通道开通引导 + 配置 + 运单）
    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('guide', [\App\Http\Controllers\Admin\LogisticsController::class, 'guide'])->name('guide');
        Route::get('settings', [\App\Http\Controllers\Admin\LogisticsController::class, 'settings'])->name('settings');
        Route::put('settings', [\App\Http\Controllers\Admin\LogisticsController::class, 'updateSettings'])->name('settings.update');
        Route::get('shipments', [\App\Http\Controllers\Admin\LogisticsController::class, 'shipments'])->name('shipments');
        Route::post('providers/{provider}/save', [\App\Http\Controllers\Admin\LogisticsController::class, 'saveProvider'])->name('providers.save');
        Route::post('providers/{provider}/toggle', [\App\Http\Controllers\Admin\LogisticsController::class, 'toggleProvider'])->name('providers.toggle');
        Route::post('providers/{provider}/default', [\App\Http\Controllers\Admin\LogisticsController::class, 'setDefaultProvider'])->name('providers.default');
        Route::post('providers/{provider}/test', [\App\Http\Controllers\Admin\LogisticsController::class, 'testProvider'])->name('providers.test');
        Route::post('providers/sync', [\App\Http\Controllers\Admin\LogisticsController::class, 'syncCatalog'])->name('providers.sync');
        Route::post('shipments/{shipment}/trace', [\App\Http\Controllers\Admin\LogisticsController::class, 'trace'])->name('shipments.trace');
    });

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
// 只读：库存查询 / 流水 / 批次追溯 / 物流查询（生产、质量也需要查看）
Route::middleware(['auth', 'role:admin|warehouse_manager|production_manager|quality_manager|accountant|viewer'])->prefix('app/inventory')->name('app.inventory.')->group(function () {
    Route::get('/', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'index'])->name('index');
    Route::get('/trace', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'trace'])->name('trace');
    Route::post('/trace', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'searchTrace'])->name('trace.search');
    Route::get('/transactions', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'transactions'])->name('transactions');

    // 物流查询（仓库模块）
    Route::get('/logistics', [\App\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'index'])->name('logistics');
    Route::post('/logistics/query', [\App\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'query'])->name('logistics.query');
    Route::post('/logistics', [\App\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'store'])->name('logistics.store');
    Route::get('/logistics/{shipment}', [\App\Http\Controllers\App\Inventory\LogisticsQueryController::class, 'show'])->name('logistics.show');
});

// 写操作：调拨 / 调整 / 冻结 / 盘点（仅仓库与管理员）
Route::middleware(['auth', 'role:admin|warehouse_manager'])->prefix('app/inventory')->name('app.inventory.')->group(function () {
    Route::get('/transfer', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'transfer'])->name('transfer');
    Route::post('/transfer', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeTransfer'])->name('transfer.store');
    Route::get('/adjust', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'adjust'])->name('adjust');
    Route::post('/adjust', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeAdjust'])->name('adjust.store');
    Route::get('/freeze', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'freeze'])->name('freeze');
    Route::post('/freeze', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeFreeze'])->name('freeze.store');
    Route::get('/stocktake', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'stocktake'])->name('stocktake');
    Route::post('/stocktake', [\App\Http\Controllers\App\Inventory\InventoryController::class, 'storeStocktake'])->name('stocktake.store');
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
