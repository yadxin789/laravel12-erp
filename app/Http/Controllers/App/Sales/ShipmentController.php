<?php

namespace App\Http\Controllers\App\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\Shipment;
use App\Models\Sales\SalesOrder;
use App\Models\MasterData\Warehouse;
use App\Models\System\NumberSequence;
use App\Services\InventoryService;
use App\Services\SalesOrderService;
use App\Services\NotificationService;
use App\Events\ShipmentConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ShipmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('viewAny', \App\Models\Sales\SalesOrder::class);
        $shipments = Shipment::with('salesOrder')->paginate(20);
        return view('app.sales.shipments.index', compact('shipments'));
    }

    public function create()
    {
        Gate::authorize('create', \App\Models\Sales\SalesOrder::class);
        $orders = SalesOrder::where('status', 'approved')->get();
        $warehouses = Warehouse::all();
        return view('app.sales.shipments.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', \App\Models\Sales\SalesOrder::class);
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'shipment_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'lines' => 'required|array',
            'lines.*.sales_order_line_id' => 'required|exists:sales_order_lines,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        $shipment = Shipment::create([
            'sales_order_id' => $validated['sales_order_id'],
            'shipment_date' => $validated['shipment_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            $shipment->lines()->create([
                'sales_order_line_id' => $line['sales_order_line_id'],
                'quantity' => $line['quantity'],
            ]);
        }

        // Audit log
        $this->auditLog('create', 'Shipment', $shipment);

        return redirect()->route('app.sales.shipments.show', $shipment)->with('success', '发货单创建成功');
    }

    public function show(Shipment $shipment)
    {
        Gate::authorize('view', $shipment->salesOrder);
        return view('app.sales.shipments.show', compact('shipment'));
    }

    public function confirm(Shipment $shipment)
    {
        Gate::authorize('update', $shipment->salesOrder);

        if ($shipment->status !== 'draft') {
            return back()->with('error', '发货单状态不允许确认');
        }

        DB::transaction(function () use ($shipment) {
            $inventoryService = app(InventoryService::class);
            $orderService = app(SalesOrderService::class);
            $transactionNo = $this->generateTransactionNo('SH');

            $totalShipped = 0;
            foreach ($shipment->lines as $line) {
                $orderLine = $line->salesOrderLine;
                if (!$orderLine) continue;

                // 出库扣减库存 (FIFO)
                $inventoryService->issue([
                    'item_id' => $orderLine->item_id,
                    'warehouse_id' => $shipment->warehouse_id,
                    'quantity' => $line->quantity,
                    'transaction_no' => $transactionNo,
                    'source_document_id' => $shipment->id,
                    'source_document_type' => 'shipment',
                    'source_document_line_id' => $line->id,
                    'remark' => "销售发货: {$shipment->salesOrder->order_no}",
                    'created_by' => auth()->id(),
                ]);

                // 更新销售订单行已发数量
                $orderLine->delivered_quantity += $line->quantity;
                $orderLine->save();

                $totalShipped += $line->quantity;
            }

            // 更新发货单状态
            $shipment->update(['status' => 'confirmed']);

            // 更新销售订单交货状态
            if ($totalShipped > 0) {
                $orderService->updateDelivery($shipment->salesOrder, $totalShipped);
            }

            // Audit log
            $this->auditLog('confirm', 'Shipment', $shipment);
        });

        // 事件分发：发货确认（事务外）
        event(new ShipmentConfirmed($shipment));

        // 通知销售订单创建人
        if ($shipment->salesOrder?->created_by) {
            $this->notificationService->send(
                $shipment->salesOrder->created_by,
                '发货已确认',
                "销售订单 {$shipment->salesOrder->order_no} 的发货单已确认，库存已扣减。",
                'success',
                route('app.sales.shipments.show', $shipment)
            );
        }

        return redirect()->route('app.sales.shipments.show', $shipment)->with('success', '发货单已确认，库存已扣减');
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
