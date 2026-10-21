<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\AIAnalysisService;
use Illuminate\Http\Request;

class AIAnalysisController extends Controller
{
    public function __construct(protected AIAnalysisService $analysis)
    {
    }

    /**
     * AI 分析中心首页
     */
    public function index()
    {
        return view('app.ai.analysis-center');
    }

    /**
     * 日报
     */
    public function dailyReport()
    {
        $report = $this->analysis->dailyReport();
        return view('app.ai.daily-report', compact('report'));
    }

    /**
     * 库存预警分析
     */
    public function stockAlerts()
    {
        $result = $this->analysis->stockAlertAnalysis();
        return view('app.ai.stock-alerts', $result);
    }

    /**
     * 客户信用评估
     */
    public function customerCredit(Request $request)
    {
        $customerId = $request->get('customer_id');
        $result = $customerId ? $this->analysis->customerCreditAssessment($customerId) : null;
        $customers = \App\Models\MasterData\Customer::where('active', true)->get();
        return view('app.ai.customer-credit', compact('result', 'customers'));
    }

    /**
     * 供应商质量评分
     */
    public function supplierQuality()
    {
        $suppliers = \App\Models\MasterData\Supplier::where('active', true)->get();
        $result = null;
        if (request('supplier_id')) {
            $result = $this->analysis->supplierQualityScore(request('supplier_id'));
        }
        return view('app.ai.supplier-quality', compact('suppliers', 'result'));
    }

    /**
     * 销售预测
     */
    public function salesForecast()
    {
        $result = $this->analysis->salesForecast();
        return view('app.ai.sales-forecast', $result);
    }

    /**
     * 生产成本分析
     */
    public function productionCost()
    {
        $orders = \App\Models\Production\ProductionOrder::where('status', 'completed')->latest()->limit(20)->get();
        $result = null;
        if (request('order_id')) {
            $result = $this->analysis->productionCostAnalysis(request('order_id'));
        }
        return view('app.ai.production-cost', compact('orders', 'result'));
    }

    /**
     * 异常订单检测
     */
    public function anomalyDetection()
    {
        $result = $this->analysis->anomalyDetection();
        return view('app.ai.anomaly-detection', $result);
    }

    /**
     * 智能排程
     */
    public function scheduling()
    {
        $result = $this->analysis->schedulingSuggestion();
        return view('app.ai.scheduling', $result);
    }

    /**
     * 电商订单合并
     */
    public function ecommerceMerge()
    {
        $result = $this->analysis->ecommerceMergeSuggestion();
        return view('app.ai.ecommerce-merge', $result);
    }

    /**
     * 邮件智能回复
     */
    public function emailReply(Request $request)
    {
        $draft = null;
        if ($request->isMethod('post')) {
            $draft = $this->analysis->emailReplyDraft($request->input('inquiry'), $request->input('customer_id'));
        }
        $customers = \App\Models\MasterData\Customer::where('active', true)->get();
        return view('app.ai.email-reply', compact('draft', 'customers'));
    }
}
