<?php

namespace App\Http\Controllers\App\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sales\SalesReturn;
use App\Models\Sales\SalesOrder;
use App\Models\MasterData\Warehouse;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SalesReturnController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view sales returns');
        $returns = SalesReturn::with('salesOrder')->paginate(20);
        return view('app.sales.returns.index', compact('returns'));
    }

    public function create()
    {
        Gate::authorize('create sales returns');
        $orders = SalesOrder::whereIn('status', ['approved', 'closed'])->get();
        $warehouses = Warehouse::all();
        return view('app.sales.returns.create', compact('orders', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create sales returns');
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'return_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string',
            'lines' => 'required|array',
            'lines.*.sales_order_line_id' => 'required|exists:sales_order_lines,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        $return = SalesReturn::create([
            'sales_order_id' => $validated['sales_order_id'],
            'return_date' => $validated['return_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'reason' => $validated['reason'],
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            $return->lines()->create([
                'sales_order_line_id' => $line['sales_order_line_id'],
                'quantity' => $line['quantity'],
            ]);
        }

        $this->auditLog('create', 'SalesReturn', $return);

        return redirect()->route('app.sales.returns.show', $return)->with('success', '销售退货单创建成功');
    }

    public function show(SalesReturn $return)
    {
        Gate::authorize('view sales returns');
        return view('app.sales.returns.show', compact('return'));
    }

    public function approve(SalesReturn $return)
    {
        Gate::authorize('edit sales returns');
        $return->update(['status' => 'approved']);
        $this->auditLog('approve', 'SalesReturn', $return);

        if ($return->salesOrder?->created_by) {
            $this->notificationService->send(
                $return->salesOrder->created_by,
                '销售退货已审核',
                "退货单 #{$return->id} 已审核通过。",
                'success',
                route('app.sales.returns.show', $return)
            );
        }

        return redirect()->route('app.sales.returns.show', $return)->with('success', '销售退货已审核通过');
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
