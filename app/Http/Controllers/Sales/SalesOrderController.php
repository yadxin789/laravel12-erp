<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Models\Sales\SalesOrder;
use App\Services\SalesOrderService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

/**
 * 销售订单 JSON API（Web 端页面由 App\Sales\SalesOrderController 负责）
 */
class SalesOrderController extends Controller
{
    protected $service;

    public function __construct(SalesOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', SalesOrder::class);

        $query = SalesOrder::with(['customer', 'lines.item']);

        if ($request->filled('order_no')) {
            $query->where('order_no', 'like', "%{$request->order_no}%");
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->where('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('order_date', '<=', $request->end_date);
        }

        return response()->json($query->paginate(20));
    }

    public function store(SalesOrderRequest $request)
    {
        $this->authorize('create', SalesOrder::class);

        $order = $this->service->create($request->validated());

        AuditLogger::create('sales_order', $order);

        return response()->json($order->load('lines'), 201);
    }

    public function show(SalesOrder $salesOrder)
    {
        $this->authorize('view', $salesOrder);

        return response()->json($salesOrder->load(['customer', 'lines.item']));
    }

    public function update(SalesOrderRequest $request, SalesOrder $salesOrder)
    {
        $this->authorize('update', $salesOrder);

        $oldValues = $salesOrder->toArray();
        $order = $this->service->update($salesOrder, $request->validated());

        AuditLogger::update('sales_order', $order, $oldValues);

        return response()->json($order->load('lines'));
    }

    public function submit(SalesOrder $salesOrder)
    {
        $this->authorize('submit', $salesOrder);

        $order = $this->service->submit($salesOrder);

        AuditLogger::log('submit', 'sales_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function approve(SalesOrder $salesOrder)
    {
        $this->authorize('approve', $salesOrder);

        $order = $this->service->approve($salesOrder);

        AuditLogger::approve('sales_order', $order);

        return response()->json($order);
    }

    public function cancel(SalesOrder $salesOrder)
    {
        $this->authorize('cancel', $salesOrder);

        $order = $this->service->cancel($salesOrder);

        AuditLogger::log('cancel', 'sales_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function close(SalesOrder $salesOrder)
    {
        $this->authorize('update', $salesOrder);

        $order = $this->service->close($salesOrder);

        AuditLogger::log('close', 'sales_order', ['record_id' => $order->id]);

        return response()->json($order);
    }
}