<?php

namespace App\Http\Controllers\App\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesOrder;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use App\Services\NotificationService;
use App\Services\SalesOrderService;
use App\Events\OrderSubmitted;
use App\Events\OrderApproved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalesOrderController extends Controller
{
    protected $notificationService;
    protected $orderService;

    public function __construct(NotificationService $notificationService, SalesOrderService $orderService)
    {
        $this->notificationService = $notificationService;
        $this->orderService = $orderService;
    }

    public function index()
    {
        Gate::authorize('viewAny', SalesOrder::class);
        $orders = SalesOrder::with('customer')->paginate(20);
        return view('app.sales.orders.index', compact('orders'));
    }

    public function create()
    {
        Gate::authorize('create', SalesOrder::class);
        $customers = Customer::all();
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.sales.orders.create', compact('customers', 'items', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', SalesOrder::class);
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 检查客户信用额度
        $customer = Customer::find($validated['customer_id']);
        if ($customer && $customer->credit_limit > 0) {
            $orderTotal = 0;
            foreach ($validated['lines'] as $line) {
                $orderTotal += $line['quantity'] * $line['unit_price'];
            }
            $currentBalance = SalesOrder::where('customer_id', $customer->id)
                ->whereIn('status', ['approved', 'partially_delivered'])
                ->sum('total_amount');
            if (($currentBalance + $orderTotal) > $customer->credit_limit) {
                return back()->withInput()->with('error', "客户信用额度不足。信用额度：¥{$customer->credit_limit}，当前余额：¥{$currentBalance}");
            }
        }

        $order = $this->orderService->create($validated);

        $this->auditLog('create', 'SalesOrder', $order);

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单创建成功');
    }

    public function show(SalesOrder $order)
    {
        Gate::authorize('view', $order);
        return view('app.sales.orders.show', compact('order'));
    }

    public function edit(SalesOrder $order)
    {
        Gate::authorize('update', $order);
        $customers = Customer::all();
        $items = Item::all();
        return view('app.sales.orders.edit', compact('order', 'customers', 'items'));
    }

    public function update(Request $request, SalesOrder $order)
    {
        Gate::authorize('update', $order);

        if (!$order->canUpdate()) {
            return back()->with('error', '订单状态不允许修改');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'delivery_date' => 'required|date',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        // 检查客户信用额度
        $customer = Customer::find($validated['customer_id']);
        if ($customer && $customer->credit_limit > 0) {
            $orderTotal = 0;
            foreach ($validated['lines'] as $line) {
                $orderTotal += $line['quantity'] * $line['unit_price'];
            }
            $currentBalance = SalesOrder::where('customer_id', $customer->id)
                ->whereIn('status', ['approved', 'partially_delivered'])
                ->where('id', '!=', $order->id)
                ->sum('total_amount');
            if (($currentBalance + $orderTotal) > $customer->credit_limit) {
                return back()->withInput()->with('error', "客户信用额度不足。信用额度：¥{$customer->credit_limit}，当前余额：¥{$currentBalance}");
            }
        }

        $order->update([
            'customer_id' => $validated['customer_id'],
            'order_date' => $validated['order_date'],
            'delivery_date' => $validated['delivery_date'],
        ]);

        // 更新明细行
        $order->lines()->delete();
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($validated['lines'] as $lineData) {
            $line = $order->lines()->create([
                'item_id' => $lineData['item_id'],
                'quantity' => $lineData['quantity'],
                'unit_price' => $lineData['unit_price'],
                'delivered_quantity' => 0,
            ]);

            $lineAmount = $lineData['quantity'] * $lineData['unit_price'];
            $totalAmount += $lineAmount;
            $totalQuantity += $lineData['quantity'];
        }

        $order->update([
            'total_amount' => $totalAmount,
            'total_quantity' => $totalQuantity,
        ]);

        $this->auditLog('update', 'SalesOrder', $order);

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单更新成功');
    }

    public function submit(SalesOrder $order)
    {
        Gate::authorize('submit', $order);

        if (!$order->canSubmit()) {
            return back()->with('error', '订单状态不允许提交');
        }

        // 信用额度检查
        if ($order->customer && $order->customer->credit_limit > 0) {
            $outstanding = \App\Models\Finance\CustomerInvoice::where('customer_id', $order->customer_id)
                ->where('status', '!=', 'paid')
                ->sum('amount');
            $totalAfter = $outstanding + $order->total_amount;
            if ($totalAfter > $order->customer->credit_limit) {
                return back()->with('error', "信用额度不足：当前应收 ¥{$outstanding} + 本单 ¥{$order->total_amount} = ¥{$totalAfter}，超过信用额度 ¥{$order->customer->credit_limit}");
            }
        }

        $this->orderService->submit($order);

        // 通知有审核权限的用户
        $this->notificationService->sendToPermission(
            'approve sales orders',
            '销售订单待审核',
            "销售订单 {$order->order_no} 已提交，请及时审核。客户：{$order->customer?->name}",
            'info',
            route('app.sales.orders.show', $order)
        );

        $this->auditLog('submit', 'SalesOrder', $order);

        event(new OrderSubmitted($order, 'sales'));

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单已提交审核');
    }

    public function approve(SalesOrder $order)
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
                '销售订单已审核',
                "销售订单 {$order->order_no} 已审核通过。",
                'success',
                route('app.sales.orders.show', $order)
            );
        }

        $this->auditLog('approve', 'SalesOrder', $order);

        event(new OrderApproved($order, 'sales'));

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单已审核通过');
    }

    public function cancel(SalesOrder $order)
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
                '销售订单已取消',
                "销售订单 {$order->order_no} 已被取消。",
                'warning',
                route('app.sales.orders.show', $order)
            );
        }

        $this->auditLog('cancel', 'SalesOrder', $order);

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单已取消');
    }

    public function close(SalesOrder $order)
    {
        Gate::authorize('update', $order);

        if (!$order->canClose()) {
            return back()->with('error', '订单状态不允许关闭');
        }

        $this->orderService->close($order);

        // 通知创建人
        if ($order->created_by) {
            $this->notificationService->send(
                $order->created_by,
                '销售订单已关闭',
                "销售订单 {$order->order_no} 已关闭。",
                'info',
                route('app.sales.orders.show', $order)
            );
        }

        $this->auditLog('close', 'SalesOrder', $order);

        return redirect()->route('app.sales.orders.show', $order)->with('success', '销售订单已关闭');
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
