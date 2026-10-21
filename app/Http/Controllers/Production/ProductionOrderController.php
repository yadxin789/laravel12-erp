<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductionOrderRequest;
use App\Models\Production\ProductionOrder;
use App\Services\ProductionOrderService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

/**
 * 生产工单 JSON API（Web 端页面由 App\Production\ProductionOrderController 负责）
 */
class ProductionOrderController extends Controller
{
    protected $service;

    public function __construct(ProductionOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ProductionOrder::class);

        $query = ProductionOrder::with(['item', 'workCenter']);

        if ($request->filled('order_no')) {
            $query->where('order_no', 'like', "%{$request->order_no}%");
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(20));
    }

    public function store(ProductionOrderRequest $request)
    {
        $this->authorize('create', ProductionOrder::class);

        $order = $this->service->create($request->validated());

        AuditLogger::create('production_order', $order);

        return response()->json($order->load('item'), 201);
    }

    public function show(ProductionOrder $productionOrder)
    {
        $this->authorize('view', $productionOrder);

        return response()->json($productionOrder->load([
            'item', 'bomHeader', 'routingHeader', 'workCenter', 'productionLine', 'lines.item',
        ]));
    }

    public function release(ProductionOrder $productionOrder)
    {
        $this->authorize('release', $productionOrder);

        $order = $this->service->release($productionOrder);

        AuditLogger::log('release', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function start(ProductionOrder $productionOrder)
    {
        $this->authorize('start', $productionOrder);

        $order = $this->service->start($productionOrder);

        AuditLogger::log('start', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function report(Request $request, ProductionOrder $productionOrder)
    {
        $this->authorize('update', $productionOrder);

        $order = $this->service->report($productionOrder, $request->all());

        AuditLogger::log('report', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function complete(ProductionOrder $productionOrder)
    {
        $this->authorize('complete', $productionOrder);

        $order = $this->service->complete($productionOrder);

        AuditLogger::log('complete', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function close(ProductionOrder $productionOrder)
    {
        $this->authorize('close', $productionOrder);

        $order = $this->service->close($productionOrder);

        AuditLogger::log('close', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function cancel(ProductionOrder $productionOrder)
    {
        $this->authorize('cancel', $productionOrder);

        $order = $this->service->cancel($productionOrder);

        AuditLogger::log('cancel', 'production_order', ['record_id' => $order->id]);

        return response()->json($order);
    }

    public function shortage(ProductionOrder $productionOrder)
    {
        $this->authorize('view', $productionOrder);

        return response()->json($this->service->checkMaterialShortage($productionOrder));
    }
}