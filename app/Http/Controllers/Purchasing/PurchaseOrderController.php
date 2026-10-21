<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\Purchasing\PurchaseOrder;
use App\Services\PurchaseOrderService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

/**
 * 采购订单 JSON API（Web 端页面由 App\Purchase\PurchaseOrderController 负责）
 */
class PurchaseOrderController extends Controller
{
    protected $service;

    public function __construct(PurchaseOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        $query = PurchaseOrder::with(['supplier', 'lines.item']);

        if ($request->filled('order_no')) {
            $query->where('order_no', 'like', "%{$request->order_no}%");
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function store(PurchaseOrderRequest $request)
    {
        $this->authorize('create', PurchaseOrder::class);

        $order = $this->service->create($request->validated());

        AuditLogger::create('purchase_order', $order);

        return response()->json($order->load('lines'), 201);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('view', $purchaseOrder);

        return response()->json($purchaseOrder->load(['supplier', 'lines.item']));
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('update', $purchaseOrder);

        $oldValues = $purchaseOrder->toArray();
        $order = $this->service->update($purchaseOrder, $request->validated());

        AuditLogger::update('purchase_order', $order, $oldValues);

        return response()->json($order->load('lines'));
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('submit', $purchaseOrder);

        $order = $this->service->submit($purchaseOrder);

        AuditLogger::log('submit', 'purchase_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('approve', $purchaseOrder);

        $order = $this->service->approve($purchaseOrder);

        AuditLogger::approve('purchase_order', $order);

        return response()->json($order);
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('cancel', $purchaseOrder);

        $order = $this->service->cancel($purchaseOrder);

        AuditLogger::log('cancel', 'purchase_order', ['record_id' => $order->id]);

        return response()->json($order);
    }
}