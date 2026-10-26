<?php

namespace App\Services;

use App\Enums\QualityInspectionStatus;
use App\Models\Sales\SalesOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Production\ProductionOrder;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryTransaction;
use App\Models\MasterData\Item;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Supplier;
use App\Models\Quality\QualityInspection;
use App\Models\Finance\CustomerInvoice;
use App\Models\Finance\Payment;
use App\Models\Crm\Lead;
use App\Models\Ecommerce\EcommerceOrder;
use Illuminate\Support\Facades\DB;

/**
 * AI 分析服务：承载所有智能分析类技能
 * 统一聚合数据 → 调用 AiService 生成分析结论
 */
class AIAnalysisService
{
    public function __construct(protected AiService $ai)
    {
    }

    // ========== P0-AI-1 日报自动生成 ==========

    public function dailyReport(): string
    {
        $today = now()->format('Y-m-d');

        // 聚合今日关键数据
        $salesToday = SalesOrder::whereDate('order_date', $today)
            ->select(DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total_amount) as total'))
            ->first();

        $purchaseToday = PurchaseOrder::whereDate('order_date', $today)
            ->select(DB::raw('COUNT(*) as cnt'), DB::raw('SUM(total_amount) as total'))
            ->first();

        $productionActive = ProductionOrder::whereIn('status', ['ready', 'in_progress'])->count();

        $lowStockItems = Item::where('active', true)
            ->where('safety_stock', '>', 0)
            ->withSum(['balances as qty' => fn($q) => $q->select(DB::raw('COALESCE(SUM(quantity),0)'))], 'quantity')
            ->havingRaw('qty < safety_stock')
            ->limit(10)
            ->get();

        $pendingQuality = QualityInspection::where('status', QualityInspectionStatus::Pending)->count();

        $prompt = "你是电子厂ERP系统的经营分析助手。请根据以下今日数据，生成一份简洁的中文经营日报（300字以内），包含：1)今日销售/采购概况 2)生产在制情况 3)低库存预警 4)待处理质检 5)明日建议。

今日销售：{$salesToday->cnt}笔，金额¥{$salesToday->total}
今日采购：{$purchaseToday->cnt}笔，金额¥{$purchaseToday->total}
在制工单：{$productionActive}个
低库存物料：{$lowStockItems->count()}种（" . $lowStockItems->pluck('name')->implode('、') . "）
待检批次：{$pendingQuality}批";

        return $this->ai->chat($prompt)['content'] ?? '（AI服务未配置，请先设置API Key）';
    }

    // ========== P0-AI-2 库存预警分析 ==========

    public function stockAlertAnalysis(): array
    {
        $lowStockItems = Item::where('active', true)
            ->where('safety_stock', '>', 0)
            ->withSum(['balances as qty' => fn($q) => $q->select(DB::raw('COALESCE(SUM(quantity),0)'))], 'quantity')
            ->havingRaw('qty < safety_stock')
            ->limit(20)
            ->get();

        if ($lowStockItems->isEmpty()) {
            return ['summary' => '当前无低库存物料。', 'suggestions' => []];
        }

        $itemList = $lowStockItems->map(fn($i) => "{$i->name}({$i->code})：现有{$i->qty}，安全库存{$i->safety_stock}，单价¥{$i->unit_cost}")->implode("\n");

        $prompt = "你是电子厂库存管理专家。以下是低库存物料列表，请为每种物料给出补货建议：建议采购数量、紧急程度（高/中/低）、建议到货日期。

{$itemList}

请以JSON数组格式返回：[{\"name\":\"物料名\",\"suggest_qty\":数量,\"urgency\":\"高/中/低\",\"note\":\"说明\"}]";

        $result = $this->ai->chat($prompt);
        $suggestions = json_decode($result['content'] ?? '[]', true) ?? [];

        return [
            'summary' => "共 {$lowStockItems->count()} 种物料低于安全库存",
            'items' => $lowStockItems,
            'suggestions' => $suggestions,
        ];
    }

    // ========== P0-AI-3 客户信用评估 ==========

    public function customerCreditAssessment(int $customerId): array
    {
        $customer = Customer::findOrFail($customerId);

        // 应收数据
        $invoices = CustomerInvoice::where('customer_id', $customerId)->get();
        $totalReceivable = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $outstanding = $totalReceivable - $totalPaid;
        $overdueCount = $invoices->where('status', '!=', 'paid')
            ->where('due_date', '<', now())->count();

        // 历史订单
        $orderCount = SalesOrder::where('customer_id', $customerId)->count();
        $orderTotal = SalesOrder::where('customer_id', $customerId)->sum('total_amount');

        $prompt = "你是信用评估专家。请根据以下客户数据，给出信用等级建议（A/B/C/D）和建议信用额度（万元）：

客户：{$customer->name}
历史订单数：{$orderCount}笔，累计金额¥{$orderTotal}
应收总额：¥{$totalReceivable}，已收¥{$totalPaid}，未收¥{$outstanding}
逾期未结：{$overdueCount}笔
当前信用额度：¥{$customer->credit_limit}

请以JSON返回：{\"grade\":\"A/B/C/D\",\"suggest_limit\":数字,\"reason\":\"理由（50字内）\"}";

        $result = $this->ai->chat($prompt);
        $assessment = json_decode($result['content'] ?? '{}', true) ?? [];

        return [
            'customer' => $customer,
            'stats' => compact('totalReceivable', 'totalPaid', 'outstanding', 'overdueCount', 'orderCount', 'orderTotal'),
            'assessment' => $assessment,
        ];
    }

    // ========== P1-AI-4 供应商质量评分 ==========

    public function supplierQualityScore(int $supplierId): array
    {
        $monthly = QualityInspection::where('inspection_type', 'incoming')
            ->whereHas('purchaseOrder', fn($q) => $q->where('supplier_id', $supplierId))
            ->whereMonth('created_at', now()->month)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN result = 'passed' THEN 1 ELSE 0 END) as passed")
            )->first();

        $prompt = "你是质量管理专家。本月该供应商来料检验数据：总{$monthly->total}批，合格{$monthly->passed}批。合格率" .
            ($monthly->total > 0 ? round($monthly->passed / $monthly->total * 100, 1) : 0) . "%。请给出A/B/C/D评级和改进建议（100字内）。";

        $result = $this->ai->chat($prompt);

        return [
            'supplier' => Supplier::find($supplierId),
            'total' => $monthly->total,
            'passed' => $monthly->passed,
            'passRate' => $monthly->total > 0 ? round($monthly->passed / $monthly->total * 100, 1) : 0,
            'aiAnalysis' => $result['content'] ?? '',
        ];
    }

    // ========== P1-AI-5 销售预测 ==========

    public function salesForecast(int $months = 3): array
    {
        $history = SalesOrder::select(
            DB::raw('DATE_FORMAT(order_date, "%Y-%m") as month'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('order_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $prompt = "你是销售预测分析师。以下是近12个月销售数据：\n" .
            $history->map(fn($h) => "{$h->month}: ¥{$h->total}")->implode("\n") .
            "\n\n请预测未来{$months}个月的销售金额（万元），并简要说明趋势判断。以JSON返回：{\"forecast\":[{\"month\":\"2026-11\",\"amount\":数字}],\"trend\":\"上升/平稳/下降\",\"note\":\"说明\"}";

        $result = $this->ai->chat($prompt);

        return [
            'history' => $history,
            'forecast' => json_decode($result['content'] ?? '{}', true),
        ];
    }

    // ========== P1-AI-6 生产成本分析 ==========

    public function productionCostAnalysis(int $orderId): array
    {
        $order = ProductionOrder::with(['lines.item'])->findOrFail($orderId);

        $prompt = "你是成本分析专家。生产工单 {$order->order_no} 计划成本¥{$order->planned_cost}，实际成本¥{$order->actual_cost}。差异¥" .
            ($order->actual_cost - $order->planned_cost) . "（" .
            ($order->planned_cost > 0 ? round(($order->actual_cost - $order->planned_cost) / $order->planned_cost * 100, 1) : 0) .
            "%）。请简要分析差异原因和改进建议（100字内）。";

        $result = $this->ai->chat($prompt);

        return [
            'order' => $order,
            'variance' => $order->actual_cost - $order->planned_cost,
            'variancePct' => $order->planned_cost > 0 ? round(($order->actual_cost - $order->planned_cost) / $order->planned_cost * 100, 1) : 0,
            'aiAnalysis' => $result['content'] ?? '',
        ];
    }

    // ========== P1-AI-7 异常订单检测 ==========

    public function anomalyDetection(): array
    {
        // 金额异常大（超过平均3倍）
        $avgAmount = SalesOrder::whereDate('created_at', '>=', now()->subMonth())->avg('total_amount') ?? 0;
        $bigOrders = SalesOrder::whereDate('created_at', '>=', now()->subWeek())
            ->where('total_amount', '>', $avgAmount * 3)
            ->with('customer')
            ->get();

        // 新客户首单
        $newCustomerOrders = SalesOrder::whereHas('customer', fn($q) => $q->where('created_at', '>=', now()->subMonth()))
            ->whereDate('created_at', '>=', now()->subWeek())
            ->with('customer')
            ->get();

        $flags = [];
        foreach ($bigOrders as $o) {
            $flags[] = ['type' => '金额异常', 'order_no' => $o->order_no, 'detail' => "¥{$o->total_amount}（平均¥" . round($avgAmount) . "）"];
        }
        foreach ($newCustomerOrders as $o) {
            $flags[] = ['type' => '新客户首单', 'order_no' => $o->order_no, 'detail' => $o->customer?->name];
        }

        return [
            'flags' => $flags,
            'total' => count($flags),
        ];
    }

    // ========== P2-AI-8 智能排程建议 ==========

    public function schedulingSuggestion(): array
    {
        $readyOrders = ProductionOrder::where('status', 'ready')
            ->with(['item', 'salesOrder'])
            ->get();

        $prompt = "你是生产排程专家。以下待开工工单：\n" .
            $readyOrders->map(fn($o) => "工单{$o->order_no}：产品{$o->item?->name}，数量{$o->quantity}，交期" . ($o->plan_end_date ?? '未定'))->implode("\n") .
            "\n\n请给出优先排序建议（按交期紧急度和物料齐套率综合）。以JSON返回：[{\"order_no\":\"...\",\"priority\":1,\"reason\":\"...\"}]";

        $result = $this->ai->chat($prompt);

        return [
            'orders' => $readyOrders,
            'suggestion' => json_decode($result['content'] ?? '[]', true),
        ];
    }

    // ========== P2-AI-9 电商订单智能合并 ==========

    public function ecommerceMergeSuggestion(): array
    {
        $pendingOrders = EcommerceOrder::where('status', 'pending')->get();

        $groups = $pendingOrders->groupBy('buyer_phone');

        $suggestions = $groups->filter(fn($g) => $g->count() > 1)
            ->map(fn($g) => [
                'buyer' => $g->first()->buyer_name,
                'phone' => $g->first()->buyer_phone,
                'orders' => $g->pluck('platform_order_no')->toArray(),
                'total' => $g->sum('total_amount'),
            ])->values();

        return ['groups' => $suggestions, 'total' => $suggestions->count()];
    }

    // ========== P2-AI-11 邮件智能回复 ==========

    public function emailReplyDraft(string $inquiryContent, ?int $customerId = null): string
    {
        $customer = $customerId ? Customer::find($customerId) : null;

        $prompt = "你是电子厂销售。请根据以下客户询盘，生成一封专业的中文报价回复邮件草稿（200字内），语气礼貌、专业。

客户：" . ($customer?->name ?: '未知') . "
询盘内容：{$inquiryContent}

请直接输出邮件正文，不要加标题。";

        return $this->ai->chat($prompt)['content'] ?? '';
    }
}
