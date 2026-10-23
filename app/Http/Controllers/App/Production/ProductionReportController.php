<?php

namespace App\Http\Controllers\App\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionReport;
use App\Models\Production\ProductionOrder;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductionReportController extends Controller
{
    protected $orderService;

    public function __construct(ProductionOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        Gate::authorize('viewAny', ProductionOrder::class);
        $reports = ProductionReport::with('productionOrder')->paginate(20);
        return view('app.production.reports.index', compact('reports'));
    }

    public function create()
    {
        Gate::authorize('create', ProductionOrder::class);
        $orders = ProductionOrder::where('status', \App\Enums\ProductionOrderStatus::InProgress)->get();
        return view('app.production.reports.create', compact('orders'));
    }

    public function store(Request $request)
    {
        Gate::authorize('update', ProductionOrder::class);

        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'operation_id' => 'required',
            'reported_quantity' => 'required|integer|min:1',
            'rejected_quantity' => 'nullable|integer|min:0',
            'scrapped_quantity' => 'nullable|integer|min:0',
            'remark' => 'nullable|string',
        ]);

        $order = ProductionOrder::findOrFail($validated['production_order_id']);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $order) {
                // 表字段为 quantity / scrap_quantity / rework_quantity，需与表单字段做映射
                $nextId = (int) (ProductionReport::max('id') ?? 0) + 1;
                // 1. 创建生产汇报记录
                ProductionReport::create([
                    'report_no' => 'RPT' . date('Ymd') . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
                    'production_order_id' => $validated['production_order_id'],
                    'operation_id' => is_numeric($validated['operation_id']) ? $validated['operation_id'] : null,
                    'quantity' => $validated['reported_quantity'],
                    'scrap_quantity' => $validated['scrapped_quantity'] ?? 0,
                    'rework_quantity' => $validated['rejected_quantity'] ?? 0,
                    'remark' => $validated['remark'] ?? '',
                    'report_date' => now(),
                ]);

                // 2. 回写工单完成数量（与 ProductionOrderService::report() 统一逻辑）
                $order->completed_quantity += $validated['reported_quantity'];
                $order->scrap_quantity += $validated['scrapped_quantity'] ?? 0;

                // 3. 达成目标数量自动完成
                if ($order->completed_quantity >= $order->quantity) {
                    $order->status = \App\Enums\ProductionOrderStatus::Completed;
                    $order->actual_end_date = now();
                }

                $order->save();
            });

            $this->auditLog('report', 'ProductionReport', null, "工单 #{$order->id} 汇报完成数量 {$validated['reported_quantity']}");

            return redirect()->route('app.production.reports.index')->with('success', '生产汇报创建成功，工单完成数量已更新');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', '汇报失败：' . $e->getMessage());
        }
    }

    public function show(ProductionReport $report)
    {
        Gate::authorize('view', $report->productionOrder);
        return view('app.production.reports.show', compact('report'));
    }

    protected function auditLog(string $action, string $module, ?int $recordId = null, ?string $remark = null): void
    {
        \App\Models\System\AuditLog::create([
            'action' => $action,
            'module' => $module,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'record_id' => $recordId,
            'record_type' => $module,
            'remark' => $remark,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
