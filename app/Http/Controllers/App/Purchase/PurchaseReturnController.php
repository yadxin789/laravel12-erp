<?php

namespace App\Http\Controllers\App\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseReturn;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\MasterData\Warehouse;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseReturnController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view purchase returns');
        $returns = PurchaseReturn::with('purchaseOrder')->paginate(20);
        return view('app.purchase.returns.index', compact('returns'));
    }

    public function create()
    {
        Gate::authorize('create purchase returns');
        $orders = PurchaseOrder::whereIn('status', ['approved', 'partially_received', 'fully_received'])->get();
        $warehouses = Warehouse::all();
        return view('app.purchase.returns.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create purchase returns');
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'return_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'lines' => 'required|array',
            'lines.*.purchase_order_line_id' => 'required|exists:purchase_order_lines,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        $return = PurchaseReturn::create([
            'purchase_order_id' => $validated['purchase_order_id'],
            'return_date' => $validated['return_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'reason' => $validated['reason'],
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            $return->lines()->create([
                'purchase_order_line_id' => $line['purchase_order_line_id'],
                'quantity' => $line['quantity'],
            ]);
        }

        $this->auditLog('create', 'PurchaseReturn', $return);

        return redirect()->route('app.purchase.returns.show', $return)->with('success', '采购退货单创建成功');
    }

    public function show(PurchaseReturn $return)
    {
        Gate::authorize('view purchase returns');
        return view('app.purchase.returns.show', compact('return'));
    }

    public function approve(PurchaseReturn $return)
    {
        Gate::authorize('edit purchase returns');
        $return->update(['status' => 'approved']);
        $this->auditLog('approve', 'PurchaseReturn', $return);

        if ($return->purchaseOrder?->created_by) {
            $this->notificationService->send(
                $return->purchaseOrder->created_by,
                '采购退货已审核',
                "采购退货单 #{$return->id} 已审核通过。",
                'success',
                route('app.purchase.returns.show', $return)
            );
        }

        return redirect()->route('app.purchase.returns.show', $return)->with('success', '采购退货已审核通过');
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
            'remark' => "{$module} #{$record->id} {$action}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
