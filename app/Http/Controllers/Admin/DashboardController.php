<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductionOrderStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\Production\ProductionOrder;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Sales\SalesOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $salesTrend = $this->monthlyTrend(SalesOrder::class, 'total_amount');
        $purchaseTrend = $this->monthlyTrend(PurchaseOrder::class, 'total_amount');
        $productionTrend = $this->monthlyTrend(ProductionOrder::class, 'quantity');

        $salesStatus = $this->statusDistribution(SalesOrder::class, SalesOrderStatus::class);
        $purchaseStatus = $this->statusDistribution(PurchaseOrder::class, PurchaseOrderStatus::class);
        $productionStatus = $this->statusDistribution(ProductionOrder::class, ProductionOrderStatus::class);

        $stockByWarehouse = InventoryBalance::with('warehouse')
            ->selectRaw('warehouse_id, SUM(quantity) as qty')
            ->groupBy('warehouse_id')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->warehouse?->name ?? ('仓库 #' . $row->warehouse_id),
                'value' => (float) $row->qty,
            ])->values()->all();

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

        return view('admin.dashboard', compact(
            'salesTrend',
            'purchaseTrend',
            'productionTrend',
            'salesStatus',
            'purchaseStatus',
            'productionStatus',
            'stockByWarehouse',
            'stockTop'
        ));
    }

    private function monthlyTrend(string $model, string $amountColumn): array
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

        return ['labels' => $months->all(), 'counts' => $counts, 'amounts' => $amounts];
    }

    private function statusDistribution(string $model, string $enumClass): array
    {
        return $model::query()
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->get()
            ->map(function ($row) use ($enumClass) {
                $status = $row->status;

                if ($status instanceof \BackedEnum) {
                    $label = method_exists($status, 'label') ? $status->label() : (string) $status->value;
                } else {
                    $raw = (string) ($status ?? '');
                    $enum = $enumClass::tryFrom($raw);
                    $label = $enum && method_exists($enum, 'label') ? $enum->label() : ($raw ?: '未设置');
                }

                return ['name' => $label, 'value' => (int) $row->cnt];
            })->values()->all();
    }
}
