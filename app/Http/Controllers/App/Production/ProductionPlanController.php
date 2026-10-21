<?php

namespace App\Http\Controllers\App\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionPlan;
use App\Models\Production\ProductionPlanLine;
use App\Models\MasterData\Item;
use App\Models\MasterData\WorkCenter;
use App\Models\Sales\SalesOrder;
use App\Services\MRPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class ProductionPlanController extends Controller
{
    protected $mrpService;

    public function __construct(MRPService $mrpService)
    {
        $this->mrpService = $mrpService;
    }

    public function index()
    {
        Gate::authorize('viewAny', ProductionPlan::class);
        $plans = ProductionPlan::with('lines.item')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('app.production.plans.index', compact('plans'));
    }

    public function create()
    {
        Gate::authorize('create', ProductionPlan::class);
        $items = Item::all();
        $workCenters = WorkCenter::where('active', true)->get();
        $salesOrders = SalesOrder::whereIn('status', ['approved', 'partially_delivered'])
            ->with('customer')
            ->get();
        return view('app.production.plans.create', compact('items', 'workCenters', 'salesOrders'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ProductionPlan::class);

        $validated = $request->validate([
            'plan_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.plan_date' => 'nullable|date',
            'lines.*.work_center_id' => 'nullable|exists:work_centers,id',
            'lines.*.sales_order_id' => 'nullable|exists:sales_orders,id',
            'remark' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $plan = ProductionPlan::create([
                    'plan_no' => 'PP' . date('YmdHis'),
                    'plan_date' => $validated['plan_date'],
                    'status' => 'draft',
                    'remark' => $validated['remark'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $lineNumber = 1;
                foreach ($validated['lines'] as $lineData) {
                    $plan->lines()->create([
                        'line_number' => $lineNumber++,
                        'item_id' => $lineData['item_id'],
                        'quantity' => $lineData['quantity'],
                        'plan_date' => $lineData['plan_date'] ?? $validated['plan_date'],
                        'work_center_id' => $lineData['work_center_id'] ?? null,
                        'sales_order_id' => $lineData['sales_order_id'] ?? null,
                        'remark' => $lineData['remark'] ?? null,
                    ]);
                }
            });

            return redirect()->route('app.production.plans.index')->with('success', '生产计划创建成功');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', '创建失败：' . $e->getMessage());
        }
    }

    public function show(ProductionPlan $plan)
    {
        Gate::authorize('view', $plan);
        $plan->load(['lines.item', 'lines.workCenter', 'lines.salesOrder', 'creator', 'approver']);
        return view('app.production.plans.show', compact('plan'));
    }

    public function edit(ProductionPlan $plan)
    {
        Gate::authorize('update', $plan);

        if ($plan->status !== 'draft') {
            return back()->with('error', '只有草稿状态的计划可以编辑');
        }

        $items = Item::all();
        $workCenters = WorkCenter::where('active', true)->get();
        $salesOrders = SalesOrder::whereIn('status', ['approved', 'partially_delivered'])
            ->with('customer')
            ->get();

        return view('app.production.plans.edit', compact('plan', 'items', 'workCenters', 'salesOrders'));
    }

    public function update(Request $request, ProductionPlan $plan)
    {
        Gate::authorize('update', $plan);

        if ($plan->status !== 'draft') {
            return back()->with('error', '只有草稿状态的计划可以编辑');
        }

        $validated = $request->validate([
            'plan_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.plan_date' => 'nullable|date',
            'lines.*.work_center_id' => 'nullable|exists:work_centers,id',
            'lines.*.sales_order_id' => 'nullable|exists:sales_orders,id',
            'remark' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated, $plan) {
                $plan->update([
                    'plan_date' => $validated['plan_date'],
                    'remark' => $validated['remark'] ?? null,
                    'updated_by' => auth()->id(),
                ]);

                // 删除旧明细，创建新明细
                $plan->lines()->delete();

                $lineNumber = 1;
                foreach ($validated['lines'] as $lineData) {
                    $plan->lines()->create([
                        'line_number' => $lineNumber++,
                        'item_id' => $lineData['item_id'],
                        'quantity' => $lineData['quantity'],
                        'plan_date' => $lineData['plan_date'] ?? $validated['plan_date'],
                        'work_center_id' => $lineData['work_center_id'] ?? null,
                        'sales_order_id' => $lineData['sales_order_id'] ?? null,
                        'remark' => $lineData['remark'] ?? null,
                    ]);
                }
            });

            return redirect()->route('app.production.plans.show', $plan)->with('success', '生产计划更新成功');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', '更新失败：' . $e->getMessage());
        }
    }

    public function destroy(ProductionPlan $plan)
    {
        Gate::authorize('delete', $plan);

        if ($plan->status !== 'draft') {
            return back()->with('error', '只有草稿状态的计划可以删除');
        }

        try {
            $plan->lines()->delete();
            $plan->delete();
            return redirect()->route('app.production.plans.index')->with('success', '生产计划删除成功');
        } catch (\Exception $e) {
            return back()->with('error', '删除失败：' . $e->getMessage());
        }
    }

    public function approve(Request $request, ProductionPlan $plan)
    {
        Gate::authorize('approve', $plan);

        if ($plan->status !== 'draft') {
            return back()->with('error', '只有草稿状态的计划可以审批');
        }

        try {
            $plan->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            return redirect()->route('app.production.plans.show', $plan)->with('success', '生产计划审批通过');
        } catch (\Exception $e) {
            return back()->with('error', '审批失败：' . $e->getMessage());
        }
    }

    public function runMrp(Request $request)
    {
        Gate::authorize('create', ProductionPlan::class);

        $validated = $request->validate([
            'horizon_days' => 'required|integer|min:1|max:365',
        ]);

        try {
            $results = $this->mrpService->runMRP($validated['horizon_days']);

            if (empty($results)) {
                return back()->with('info', '在指定时间范围内没有需要生产的项目');
            }

            // 创建生产计划
            DB::transaction(function () use ($results) {
                $plan = ProductionPlan::create([
                    'plan_no' => 'PP' . date('YmdHis'),
                    'plan_date' => now(),
                    'status' => 'draft',
                    'remark' => '由MRP自动生成',
                    'created_by' => auth()->id(),
                ]);

                $lineNumber = 1;
                foreach ($results as $result) {
                    $plan->lines()->create([
                        'line_number' => $lineNumber++,
                        'item_id' => $result['item_id'],
                        'quantity' => $result['production_quantity'],
                        'plan_date' => $result['required_date'],
                        'sales_order_id' => $result['sales_order_id'] ?? null,
                        'remark' => 'MRP建议',
                    ]);
                }
            });

            return redirect()->route('app.production.plans.index')->with('success', 'MRP运行成功，已生成生产计划');
        } catch (\Exception $e) {
            return back()->with('error', 'MRP运行失败：' . $e->getMessage());
        }
    }

    public function convertToOrders(ProductionPlan $plan)
    {
        Gate::authorize('approve', $plan);

        if ($plan->status !== 'approved') {
            return back()->with('error', '只有已审批的计划可以生成工单');
        }

        $orderService = app(\App\Services\ProductionOrderService::class);

        try {
            DB::transaction(function () use ($plan, $orderService) {
                foreach ($plan->lines as $line) {
                    $orderService->create([
                        'item_id' => $line->item_id,
                        'quantity' => $line->quantity,
                        'plan_start_date' => $line->plan_date,
                        'plan_end_date' => $line->plan_date,
                        'bom_header_id' => $line->item->default_bom_id,
                        'routing_header_id' => $line->item->default_routing_id,
                        'production_plan_id' => $plan->id,
                        'sales_order_id' => $line->sales_order_id,
                    ]);
                }

                $plan->update([
                    'status' => 'converted',
                ]);
            });

            return redirect()->route('app.production.plans.show', $plan)->with('success', '已成功生成' . $plan->lines->count() . '个生产工单');
        } catch (\Exception $e) {
            return back()->with('error', '生成工单失败：' . $e->getMessage());
        }
    }
}
