<?php

namespace App\Http\Controllers\App\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\Receipt;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\MasterData\Warehouse;
use App\Models\System\NumberSequence;
use App\Services\InventoryService;
use App\Services\PurchaseOrderService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReceiptController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('viewAny', PurchaseOrder::class);
        $receipts = Receipt::with('purchaseOrder')->paginate(20);
        return view('app.purchase.receipts.index', compact('receipts'));
    }

    public function create()
    {
        Gate::authorize('create', PurchaseOrder::class);
        $orders = PurchaseOrder::whereIn('status', ['approved', 'partially_received'])->get();
        $warehouses = Warehouse::all();
        return view('app.purchase.receipts.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', PurchaseOrder::class);
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'receipt_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'lines' => 'required|array',
            'lines.*.purchase_order_line_id' => 'required|exists:purchase_order_lines,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.batch_number' => 'nullable|string',
        ]);

        $receipt = Receipt::create([
            'purchase_order_id' => $validated['purchase_order_id'],
            'receipt_date' => $validated['receipt_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            $receipt->lines()->create([
                'purchase_order_line_id' => $line['purchase_order_line_id'],
                'quantity' => $line['quantity'],
                'batch_number' => $line['batch_number'] ?? null,
            ]);
        }

        $this->auditLog('create', 'Receipt', $receipt);

        return redirect()->route('app.purchase.receipts.show', $receipt)->with('success', '收货单创建成功');
    }

    public function show(Receipt $receipt)
    {
        Gate::authorize('view', $receipt->purchaseOrder);
        return view('app.purchase.receipts.show', compact('receipt'));
    }

    public function confirm(Receipt $receipt)
    {
        Gate::authorize('update', $receipt->purchaseOrder);

        if ($receipt->status !== 'draft') {
            return back()->with('error', '收货单状态不允许确认');
        }

        DB::transaction(function () use ($receipt) {
            $inventoryService = app(InventoryService::class);
            $orderService = app(PurchaseOrderService::class);
            $transactionNo = $this->generateTransactionNo('RC');

            $totalReceived = 0;
            foreach ($receipt->lines as $line) {
                $orderLine = $line->purchaseOrderLine;
                if (!$orderLine) continue;

                // 入库增加库存
                $inventoryService->receive([
                    'item_id' => $orderLine->item_id,
                    'warehouse_id' => $receipt->warehouse_id,
                    'quantity' => $line->quantity,
                    'batch_number' => $line->batch_number,
                    'transaction_no' => $transactionNo,
                    'source_document_id' => $receipt->id,
                    'source_document_type' => 'receipt',
                    'source_document_line_id' => $line->id,
                    'supplier_id' => $receipt->purchaseOrder->supplier_id,
                    'remark' => "采购收货: {$receipt->purchaseOrder->order_no}",
                    'created_by' => auth()->id(),
                ]);

                // 更新采购订单行已收数量
                $orderLine->received_quantity += $line->quantity;
                $orderLine->save();

                $totalReceived += $line->quantity;
            }

            // 更新收货单状态
            $receipt->update(['status' => 'confirmed']);

            // 更新采购订单收货状态
            if ($totalReceived > 0) {
                $orderService->updateReceiving($receipt->purchaseOrder, $totalReceived);
            }

            $this->auditLog('confirm', 'Receipt', $receipt);
        });

        // 通知采购订单创建人
        if ($receipt->purchaseOrder?->created_by) {
            $this->notificationService->send(
                $receipt->purchaseOrder->created_by,
                '收货已确认',
                "采购订单 {$receipt->purchaseOrder->order_no} 的收货单已确认，库存已增加。",
                'success',
                route('app.purchase.receipts.show', $receipt)
            );
        }

        return redirect()->route('app.purchase.receipts.show', $receipt)->with('success', '收货单已确认，库存已增加');
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
