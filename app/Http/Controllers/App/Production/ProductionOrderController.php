<?php

namespace App\Http\Controllers\App\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionOrder;
use App\Models\MasterData\Item;
use App\Models\Production\BomHeader;
use App\Models\Production\RoutingHeader;
use App\Services\NotificationService;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductionOrderController extends Controller
{
    protected $notificationService;
    protected $orderService;

    public function __construct(NotificationService $notificationService, ProductionOrderService $orderService)
    {
        $this->notificationService = $notificationService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        Gate::authorize('viewAny', ProductionOrder::class);
        $orders = ProductionOrder::with('item')->paginate(20);
        return view('app.production.orders.index', compact('orders'));
    }

    public function create()
    {
        Gate::authorize('create', ProductionOrder::class);
        $items = Item::all();
        $boms = BomHeader::all();
        $routings = RoutingHeader::all();
        return view('app.production.orders.create', compact('items', 'boms', 'routings'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ProductionOrder::class);
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'bom_id' => 'required|exists:bom_headers,id',
            'routing_id' => 'required|exists:routing_headers,id',
        ]);

        try {
            $order = $this->orderService->create([
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'plan_start_date' => $validated['start_date'],
                'plan_end_date' => $validated['end_date'],
                'bom_header_id' => $validated['bom_id'],
                'routing_header_id' => $validated['routing_id'],
            ]);

            $this->auditLog('create', 'ProductionOrder', $order);

            return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单创建成功');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(ProductionOrder $order)
    {
        Gate::authorize('view', $order);
        return view('app.production.orders.show', compact('order'));
    }

    public function release(ProductionOrder $order)
    {
        Gate::authorize('release', $order);

        if (!$order->canRelease()) {
            return back()->with('error', '工单状态不允许下达');
        }

        try {
            $this->orderService->release($order);

            // 通知有开工权限的用户
            $this->notificationService->sendToPermission(
                'start production orders',
                '生产工单已下达',
                "生产工单 {$order->order_no} 已下达，请安排生产。物料：{$order->item?->name}，数量：{$order->quantity}",
                'info',
                route('app.production.orders.show', $order)
            );

            $this->auditLog('release', 'ProductionOrder', $order);

            return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单已下达');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function start(ProductionOrder $order)
    {
        Gate::authorize('start', $order);

        if (!$order->canStart()) {
            return back()->with('error', '工单状态不允许开工');
        }

        $this->orderService->start($order);

        $this->auditLog('start', 'ProductionOrder', $order);

        return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单已开工');
    }

    public function report(Request $request, ProductionOrder $order)
    {
        Gate::authorize('update', $order);
        $validated = $request->validate([
            'operation_id' => 'required',
            'quantity' => 'required|integer|min:1',
        ]);

        $order->reports()->create([
            'operation_id' => $validated['operation_id'],
            'reported_quantity' => $validated['quantity'],
            'report_date' => now(),
        ]);

        $order->completed_quantity += $validated['quantity'];
        $order->save();

        // 如果达成目标数量，自动完成
        if ($order->completed_quantity >= $order->quantity) {
            $this->orderService->complete($order);
        }

        // 通知创建人
        if ($order->completed_quantity >= $order->quantity && $order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '生产工单已完成',
                "生产工单 {$order->order_no} 已完成全部生产任务。完成数量：{$order->completed_quantity}/{$order->quantity}",
                'success',
                route('app.production.orders.show', $order)
            );
        }

        $this->auditLog('report', 'ProductionOrder', $order);

        return redirect()->route('app.production.orders.show', $order)->with('success', '生产汇报已提交');
    }

    public function complete(ProductionOrder $order)
    {
        Gate::authorize('complete', $order);

        if (!$order->canComplete()) {
            return back()->with('error', '工单状态不允许完成');
        }

        $this->orderService->complete($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '生产工单已完工',
                "生产工单 {$order->order_no} 已完工。",
                'success',
                route('app.production.orders.show', $order)
            );
        }

        $this->auditLog('complete', 'ProductionOrder', $order);

        return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单已完工');
    }

    public function close(ProductionOrder $order)
    {
        Gate::authorize('close', $order);

        if (!$order->canClose()) {
            return back()->with('error', '工单状态不允许关闭');
        }

        $this->orderService->close($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '生产工单已关闭',
                "生产工单 {$order->order_no} 已关闭。",
                'info',
                route('app.production.orders.show', $order)
            );
        }

        $this->auditLog('close', 'ProductionOrder', $order);

        return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单已关闭');
    }

    public function cancel(ProductionOrder $order)
    {
        Gate::authorize('cancel', $order);

        if (!$order->canCancel()) {
            return back()->with('error', '工单状态不允许取消');
        }

        $this->orderService->cancel($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '生产工单已取消',
                "生产工单 {$order->order_no} 已被取消。",
                'warning',
                route('app.production.orders.show', $order)
            );
        }

        $this->auditLog('cancel', 'ProductionOrder', $order);

        return redirect()->route('app.production.orders.show', $order)->with('success', '生产工单已取消');
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
