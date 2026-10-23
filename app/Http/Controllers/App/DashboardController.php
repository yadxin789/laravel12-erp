<?php

namespace App\Http\Controllers\App;

use App\Enums\ProductionOrderStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Finance\CustomerInvoice;
use App\Models\Finance\SupplierBill;
use App\Models\Inventory\InventoryBalance;
use App\Models\Production\ProductionOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Quality\QualityInspection;
use App\Models\Sales\SalesOrder;
use App\Support\Menu;

class DashboardController extends Controller
{
    public function index()
    {
        $role = Menu::role();

        $salesOrders = SalesOrder::count();
        $purchaseOrders = PurchaseOrder::count();
        $productionOrders = ProductionOrder::count();

        $inventoryValue = (float) InventoryBalance::sum('quantity') * 100;

        $recentSalesOrders = SalesOrder::latest()->take(5)->get();
        $recentPurchaseOrders = PurchaseOrder::latest()->take(5)->get();
        $recentProductionOrders = ProductionOrder::latest()->take(5)->get();

        // ---------- 图表数据 ----------
        $salesTrend = $this->monthlyTrend(SalesOrder::class, 'total_amount');
        $purchaseTrend = $this->monthlyTrend(PurchaseOrder::class, 'total_amount');
        $productionTrend = $this->monthlyTrend(ProductionOrder::class, 'quantity');

        $salesStatus = $this->statusDistribution(SalesOrder::class, SalesOrderStatus::class);
        $purchaseStatus = $this->statusDistribution(PurchaseOrder::class, PurchaseOrderStatus::class);
        $productionStatus = $this->statusDistribution(ProductionOrder::class, ProductionOrderStatus::class);

        $stockTop = InventoryBalance::with('item')
            ->selectRaw('item_id, SUM(quantity) as qty')
            ->groupBy('item_id')
            ->orderByDesc('qty')
            ->take(8)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->item?->name ?? ('物料 #' . $row->item_id),
                'qty' => (float) $row->qty,
            ])->values()->all();

        $stockByWarehouse = InventoryBalance::with('warehouse')
            ->selectRaw('warehouse_id, SUM(quantity) as qty')
            ->groupBy('warehouse_id')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->warehouse?->name ?? ('仓库 #' . $row->warehouse_id),
                'qty' => (float) $row->qty,
            ])->values()->all();

        $receivable = (float) CustomerInvoice::sum('total_amount') - (float) CustomerInvoice::sum('paid_amount');
        $payable = (float) SupplierBill::sum('total_amount') - (float) SupplierBill::sum('paid_amount');

        $qualityPass = (float) QualityInspection::sum('qualified_size');
        $qualityReject = (float) QualityInspection::sum('rejected_size');

        return view('app.dashboard', compact(
            'role',
            'salesOrders',
            'purchaseOrders',
            'productionOrders',
            'inventoryValue',
            'recentSalesOrders',
            'recentPurchaseOrders',
            'recentProductionOrders',
            'salesTrend',
            'purchaseTrend',
            'productionTrend',
            'salesStatus',
            'purchaseStatus',
            'productionStatus',
            'stockTop',
            'stockByWarehouse',
            'receivable',
            'payable',
            'qualityPass',
            'qualityReject'
        ));
    }

    /**
     * 近 6 个月趋势（PHP 侧聚合，兼容 MySQL / SQLite）
     */
    private function monthlyTrend(string $model, ?string $amountColumn): array
    {
        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(5, 0))
            ->map(fn ($i) => now()->startOfMonth()->subMonths($i)->format('Y-m'));

        $rows = $model::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at', $amountColumn]);

        $counts = [];
        $amounts = [];
        foreach ($months as $ym) {
            $inMonth = $rows->filter(fn ($r) => optional($r->created_at)->format('Y-m') === $ym);
            $counts[] = $inMonth->count();
            $amounts[] = round((float) $inMonth->sum($amountColumn), 2);
        }

        return [
            'labels' => $months->all(),
            'counts' => $counts,
            'amounts' => $amounts,
        ];
    }

    /**
     * 单据状态分布（枚举转中文标签）
     */
    private function statusDistribution(string $model, string $enumClass): array
    {
        $rows = $model::query()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->get();

        return $rows->map(function ($row) use ($enumClass) {
            // status 可能已被 cast 成枚举，也可能是原始字符串
            $status = $row->status;

            if ($status instanceof \BackedEnum) {
                $label = method_exists($status, 'label') ? $status->label() : (string) $status->value;
            } else {
                $raw = (string) ($status ?? '');
                $enum = $enumClass::tryFrom($raw);
                $label = $enum && method_exists($enum, 'label') ? $enum->label() : ($raw ?: '未设置');
            }

            return [
                'name' => $label,
                'value' => (int) $row->cnt,
            ];
        })->values()->all();
    }
}
