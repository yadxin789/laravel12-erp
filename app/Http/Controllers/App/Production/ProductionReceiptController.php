<?php

namespace App\Http\Controllers\App\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionReceipt;
use App\Models\Production\ProductionOrder;
use App\Models\MasterData\Warehouse;
use App\Models\System\NumberSequence;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductionReceiptController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', ProductionOrder::class);
        $receipts = ProductionReceipt::with('productionOrder')->paginate(20);
        return view('app.production.receipts.index', compact('receipts'));
    }

    public function create()
    {
        Gate::authorize('create', ProductionOrder::class);
        $orders = ProductionOrder::whereIn('status', ['completed'])->get();
        $warehouses = \App\Models\MasterData\Warehouse::all();
        return view('app.production.receipts.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ProductionOrder::class);
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'receipt_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'batch_number' => 'nullable|string',
        ]);

        $receipt = ProductionReceipt::create([
            'production_order_id' => $validated['production_order_id'],
            'receipt_date' => $validated['receipt_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'quantity' => $validated['quantity'],
            'batch_number' => $validated['batch_number'] ?? null,
            'status' => 'draft',
        ]);

        $this->auditLog('create', 'ProductionReceipt', $receipt);

        return redirect()->route('app.production.receipts.index')->with('success', '成品入库单创建成功');
    }

    public function show(ProductionReceipt $receipt)
    {
        Gate::authorize('view', $receipt->productionOrder);
        return view('app.production.receipts.show', compact('receipt'));
    }

    public function confirm(ProductionReceipt $receipt)
    {
        Gate::authorize('update', $receipt->productionOrder);

        if ($receipt->status !== 'draft') {
            return back()->with('error', '入库单状态不允许确认');
        }

        DB::transaction(function () use ($receipt) {
            $inventoryService = app(InventoryService::class);
            $transactionNo = $this->generateTransactionNo('PR');

            $order = $receipt->productionOrder;

            // 成品入库增加库存
            $inventoryService->receive([
                'item_id' => $order->item_id,
                'warehouse_id' => $receipt->warehouse_id,
                'quantity' => $receipt->quantity,
                'batch_number' => $receipt->batch_number,
                'transaction_no' => $transactionNo,
                'source_document_id' => $receipt->id,
                'source_document_type' => 'production_receipt',
                'production_order_id' => $order->id,
                'remark' => "生产入库: {$order->order_no}",
                'created_by' => auth()->id(),
            ]);

            // 更新入库单状态
            $receipt->update(['status' => 'confirmed']);

            // 更新工单实际成本 (简化版：按物料成本估算)
            $this->calculateActualCost($order);

            $this->auditLog('confirm', 'ProductionReceipt', $receipt);
        });

        return redirect()->route('app.production.receipts.show', $receipt)->with('success', '成品入库单已确认，库存已增加');
    }

    protected function calculateActualCost(ProductionOrder $order): void
    {
        // 简化成本核算：基于已领用物料金额汇总
        $totalCost = 0;
        foreach ($order->lines as $line) {
            if ($line->issued_quantity > 0 && $line->item) {
                // 假设物料单位成本从 Item 的 unit_cost 获取
                $unitCost = $line->item->unit_cost ?? 0;
                $totalCost += $line->issued_quantity * $unitCost;
            }
        }
        if ($totalCost > 0) {
            $order->actual_cost = $totalCost;
            $order->save();
        }
    }

    protected function generateTransactionNo(string $prefix): string
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => $prefix],
            ['name' => '库存事务', 'prefix' => $prefix, 'number_length' => 8, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $transactionNo = "{$prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $transactionNo;
    }

    protected function auditLog(string $action, string $module, $record): void
    {
        \App\Models\System\AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $record->id,
            'record_type' => get_class($record),
            'remark' => "{$action} {$module} #{$record->id}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
