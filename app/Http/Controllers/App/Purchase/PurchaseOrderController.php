<?php

namespace App\Http\Controllers\App\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use App\Services\NotificationService;
use App\Services\PurchaseOrderService;
use App\Events\OrderSubmitted;
use App\Events\OrderApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderController extends Controller
{
    protected $notificationService;
    protected $orderService;

    public function __construct(NotificationService $notificationService, PurchaseOrderService $orderService)
    {
        $this->notificationService = $notificationService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        Gate::authorize('viewAny', PurchaseOrder::class);
        $orders = PurchaseOrder::with('supplier')->paginate(20);
        return view('app.purchase.orders.index', compact('orders'));
    }

    public function create()
    {
        Gate::authorize('create', PurchaseOrder::class);
        $suppliers = Supplier::all();
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.purchase.orders.create', compact('suppliers', 'items', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', PurchaseOrder::class);
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $order = $this->orderService->create($validated);

        $this->auditLog('create', 'PurchaseOrder', $order);

        return redirect()->route('app.purchase.orders.show', $order)->with('success', '采购订单创建成功');
    }

    public function show(PurchaseOrder $order)
    {
        Gate::authorize('view', $order);
        return view('app.purchase.orders.show', compact('order'));
    }

    public function submit(PurchaseOrder $order)
    {
        Gate::authorize('submit', $order);

        if (!$order->canSubmit()) {
            return back()->with('error', '订单状态不允许提交');
        }

        $this->orderService->submit($order);

        // 通知有审核权限的用户
        $this->notificationService->sendToPermission(
            'approve purchase orders',
            '采购订单待审核',
            "采购订单 {$order->order_no} 已提交，请及时审核。供应商：{$order->supplier?->name}",
            'info',
            route('app.purchase.orders.show', $order)
        );

        $this->auditLog('submit', 'PurchaseOrder', $order);

        event(new OrderSubmitted($order, 'purchase'));

        return redirect()->route('app.purchase.orders.show', $order)->with('success', '采购订单已提交审核');
    }

    public function approve(PurchaseOrder $order)
    {
        Gate::authorize('approve', $order);

        if (!$order->canApprove()) {
            return back()->with('error', '订单状态不允许审核');
        }

        $this->orderService->approve($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '采购订单已审核',
                "采购订单 {$order->order_no} 已审核通过。",
                'success',
                route('app.purchase.orders.show', $order)
            );
        }

        $this->auditLog('approve', 'PurchaseOrder', $order);

        event(new OrderApproved($order, 'purchase'));

        return redirect()->route('app.purchase.orders.show', $order)->with('success', '采购订单已审核通过');
    }

    public function cancel(PurchaseOrder $order)
    {
        Gate::authorize('cancel', $order);

        if (!$order->canCancel()) {
            return back()->with('error', '订单状态不允许取消');
        }

        $this->orderService->cancel($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '采购订单已取消',
                "采购订单 {$order->order_no} 已被取消。",
                'warning',
                route('app.purchase.orders.show', $order)
            );
        }

        $this->auditLog('cancel', 'PurchaseOrder', $order);

        return redirect()->route('app.purchase.orders.show', $order)->with('success', '采购订单已取消');
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
