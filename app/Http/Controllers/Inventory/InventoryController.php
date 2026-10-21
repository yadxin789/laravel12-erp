<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;

/**
 * 库存 JSON API（Web 端页面由 App\Inventory\InventoryController 负责）
 */
class InventoryController extends Controller
{
    protected $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryBalance::class);

        $query = InventoryBalance::with('item', 'warehouse', 'location')
            ->where('quantity', '>', 0);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('quality_status')) {
            $query->where('quality_status', $request->quality_status);
        }
        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', "%{$request->batch_number}%");
        }

        return response()->json($query->paginate(20));
    }

    public function transactions(Request $request)
    {
        $this->authorize('viewAny', InventoryBalance::class);

        $query = InventoryTransaction::with('item', 'warehouse');

        if ($request->filled('transaction_no')) {
            $query->where('transaction_no', 'like', "%{$request->transaction_no}%");
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function lowStock()
    {
        $this->authorize('viewAny', InventoryBalance::class);

        return response()->json($this->service->getLowStockItems());
    }

    public function trace(Request $request)
    {
        $this->authorize('viewAny', InventoryBalance::class);

        return response()->json(
            $this->service->traceBatch($request->batch_number, $request->item_id)
        );
    }

    public function available(Request $request)
    {
        $available = $this->service->getAvailable(
            $request->item_id,
            $request->warehouse_id,
            $request->batch_number ?? null
        );

        return response()->json(['available' => $available]);
    }
}