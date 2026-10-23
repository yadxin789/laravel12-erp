<?php

namespace App\Http\Controllers\App\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\MaterialIssue;
use App\Models\Production\ProductionOrder;
use App\Models\MasterData\Warehouse;
use App\Models\System\NumberSequence;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MaterialIssueController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', ProductionOrder::class);
        $issues = MaterialIssue::with('productionOrder')->paginate(20);
        return view('app.production.issues.index', compact('issues'));
    }

    public function create()
    {
        Gate::authorize('create', ProductionOrder::class);
        $orders = ProductionOrder::whereIn('status', ['released', 'in_progress'])->get();
        $warehouses = \App\Models\MasterData\Warehouse::all();
        $items = \App\Models\MasterData\Item::where('active', true)->orderBy('name')->get();
        return view('app.production.issues.create', compact('orders', 'warehouses', 'items'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ProductionOrder::class);
        $validated = $request->validate([
            'production_order_id' => 'required|exists:production_orders,id',
            'issue_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
        ]);

        // issue_no 为唯一且非空字段，需在此生成（原代码未赋值会导致创建失败）
        $nextId = (int) (MaterialIssue::max('id') ?? 0) + 1;
        $issue = MaterialIssue::create([
            'issue_no' => 'MI' . date('Ymd') . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'production_order_id' => $validated['production_order_id'],
            'issue_date' => $validated['issue_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
        ]);

        // line_number 与 material_issue_id 组成唯一约束，必须递增赋值
        $lineNumber = 1;
        foreach ($validated['lines'] as $line) {
            $issue->lines()->create([
                'line_number' => $lineNumber++,
                'item_id' => $line['item_id'],
                'quantity' => $line['quantity'],
            ]);
        }

        $this->auditLog('create', 'MaterialIssue', $issue);

        return redirect()->route('app.production.issues.show', $issue)->with('success', '领料单创建成功');
    }

    public function show(MaterialIssue $issue)
    {
        Gate::authorize('view', $issue->productionOrder);
        return view('app.production.issues.show', compact('issue'));
    }

    public function confirm(MaterialIssue $issue)
    {
        Gate::authorize('update', $issue->productionOrder);

        if ($issue->status !== 'draft') {
            return back()->with('error', '领料单状态不允许确认');
        }

        DB::transaction(function () use ($issue) {
            $inventoryService = app(InventoryService::class);
            $transactionNo = $this->generateTransactionNo('MI');

            foreach ($issue->lines as $line) {
                // 领料出库扣减库存 (FIFO)
                $inventoryService->issue([
                    'item_id' => $line->item_id,
                    'warehouse_id' => $issue->warehouse_id,
                    'quantity' => $line->quantity,
                    'transaction_no' => $transactionNo,
                    'source_document_id' => $issue->id,
                    'source_document_type' => 'material_issue',
                    'source_document_line_id' => $line->id,
                    'remark' => "生产领料: {$issue->productionOrder->order_no}",
                    'created_by' => auth()->id(),
                ]);

                // 更新生产工单行已发数量
                $orderLine = $issue->productionOrder->lines()
                    ->where('item_id', $line->item_id)
                    ->first();
                if ($orderLine) {
                    $orderLine->issued_quantity += $line->quantity;
                    $orderLine->save();
                }
            }

            // 更新领料单状态
            $issue->update(['status' => 'confirmed']);

            $this->auditLog('confirm', 'MaterialIssue', $issue);
        });

        return redirect()->route('app.production.issues.show', $issue)->with('success', '领料单已确认，库存已扣减');
    }

    protected function generateTransactionNo(string $prefix): string
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => $prefix],
            ['name' => '库存事务', 'prefix' => $prefix, 'number_length' => 8, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $transactionNo = "{$prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $transactionNo;
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
