<?php

namespace App\Http\Controllers\App\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Purchasing\PurchaseRequisition;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PurchaseRequisitionController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view purchase requisitions');
        $requisitions = PurchaseRequisition::paginate(20);
        return view('app.purchase.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        Gate::authorize('create purchase requisitions');
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.purchase.requisitions.create', compact('items', 'warehouses'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create purchase requisitions');
        $validated = $request->validate([
            'requisition_date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ]);

        $requisition = PurchaseRequisition::create([
            'requisition_date' => $validated['requisition_date'],
            'warehouse_id' => $validated['warehouse_id'],
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            $requisition->lines()->create([
                'item_id' => $line['item_id'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ]);
        }

        $this->auditLog('create', 'PurchaseRequisition', $requisition);

        return redirect()->route('app.purchase.requisitions.show', $requisition)->with('success', '请购单创建成功');
    }

    public function show(PurchaseRequisition $requisition)
    {
        Gate::authorize('view purchase requisitions');
        return view('app.purchase.requisitions.show', compact('requisition'));
    }

    public function submit(PurchaseRequisition $requisition)
    {
        Gate::authorize('edit purchase requisitions');
        $requisition->update(['status' => 'submitted']);
        $this->auditLog('submit', 'PurchaseRequisition', $requisition);

        $this->notificationService->sendToPermission(
            'approve purchase requisitions',
            '请购单已提交',
            "请购单 #{$requisition->id} 已提交，请审核。",
            'info',
            route('app.purchase.requisitions.show', $requisition)
        );

        return redirect()->route('app.purchase.requisitions.show', $requisition)->with('success', '请购单已提交审核');
    }

    public function approve(PurchaseRequisition $requisition)
    {
        Gate::authorize('edit purchase requisitions');
        $requisition->update(['status' => 'approved']);
        $this->auditLog('approve', 'PurchaseRequisition', $requisition);

        if ($requisition->created_by) {
            $this->notificationService->send(
                $requisition->created_by,
                '请购单已审核通过',
                "请购单 #{$requisition->id} 已审核通过。",
                'success',
                route('app.purchase.requisitions.show', $requisition)
            );
        }

        return redirect()->route('app.purchase.requisitions.show', $requisition)->with('success', '请购单已审核通过');
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
