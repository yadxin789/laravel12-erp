<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Inventory\InventoryBalance;
use App\Models\Quality\QualityInspection;
use App\Models\MasterData\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasAnyRole(['admin', 'viewer', 'accountant', 'sales_manager', 'purchase_manager'])) {
                abort(403, '无报表访问权限');
            }
            return $next($request);
        });
    }

    /**
     * 报表中心首页
     */
    public function index()
    {
        return view('app.reports.index');
    }

    /**
     * 销售统计报表
     */
    public function sales(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // 按月统计销售额
        $monthly = SalesOrder::whereYear('order_date', $year)
            ->select(
                DB::raw('MONTH(order_date) as month'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 按客户统计
        $byCustomer = SalesOrder::whereYear('order_date', $year)
            ->select('customer_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('app.reports.sales', compact('monthly', 'byCustomer', 'year'));
    }

    /**
     * 采购统计报表
     */
    public function purchase(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $monthly = PurchaseOrder::whereYear('order_date', $year)
            ->select(
                DB::raw('MONTH(order_date) as month'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $bySupplier = PurchaseOrder::whereYear('order_date', $year)
            ->select('supplier_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->with('supplier')
            ->groupBy('supplier_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('app.reports.purchase', compact('monthly', 'bySupplier', 'year'));
    }

    /**
     * 库存报表
     */
    public function inventory()
    {
        // 库存价值 Top 20
        $topItems = InventoryBalance::select('item_id', DB::raw('SUM(quantity) as qty'))
            ->with('item')
            ->groupBy('item_id')
            ->orderByDesc('qty')
            ->limit(20)
            ->get();

        // 低库存物料
        $lowStock = Item::where('active', true)
            ->where('safety_stock', '>', 0)
            ->withSum(['balances as total_qty' => fn($q) => $q->select(DB::raw('COALESCE(SUM(quantity),0)'))], 'quantity')
            ->havingRaw('total_qty < safety_stock')
            ->get();

        return view('app.reports.inventory', compact('topItems', 'lowStock'));
    }

    /**
     * 质量报表
     */
    public function quality(Request $request)
    {
        $year = $request->get('year', date('Y'));

        // 按月统计检验结果
        $monthly = QualityInspection::whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN result = 'passed' THEN 1 ELSE 0 END) as passed"),
                DB::raw("SUM(CASE WHEN result = 'failed' THEN 1 ELSE 0 END) as failed")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // 按检验类型统计
        $byType = QualityInspection::whereYear('created_at', $year)
            ->select('inspection_type', DB::raw('COUNT(*) as total'))
            ->groupBy('inspection_type')
            ->get();

        return view('app.reports.quality', compact('monthly', 'byType', 'year'));
    }
}
