<?php

namespace App\Http\Controllers\App\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\QualityInspection;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use App\Models\Quality\DefectCode;
use App\Services\NotificationService;
use App\Events\QualityCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QualityController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        Gate::authorize('view quality inspections');
        $inspections = QualityInspection::with('item')->paginate(20);
        return view('app.quality.inspections.index', compact('inspections'));
    }

    public function create()
    {
        Gate::authorize('create quality inspections');
        $items = Item::all();
        $warehouses = Warehouse::all();
        $defectCodes = DefectCode::all();
        $types = ['iqc' => 'IQC来料检验', 'ipqc' => 'IPQC过程检验', 'fqc' => 'FQC成品检验', 'oqc' => 'OQC出货检验'];
        return view('app.quality.inspections.create', compact('items', 'warehouses', 'defectCodes', 'types'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create quality inspections');
        $validated = $request->validate([
            'inspection_type' => 'required|in:iqc,ipqc,fqc,oqc',
            'inspection_date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'batch_number' => 'nullable|string',
            'inspected_size' => 'required|integer|min:1',
            'qualified_size' => 'required|integer|min:0',
            'rejected_size' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'result' => 'required|in:qualified,unqualified',
        ]);

        $inspection = QualityInspection::create([
            'inspection_type' => $validated['inspection_type'],
            'inspection_date' => $validated['inspection_date'],
            'item_id' => $validated['item_id'],
            'batch_number' => $validated['batch_number'] ?? null,
            'inspected_size' => $validated['inspected_size'],
            'qualified_size' => $validated['qualified_size'],
            'rejected_size' => $validated['rejected_size'],
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'result' => $validated['result'],
            'status' => 'completed',
        ]);

        $this->auditLog('create', 'QualityInspection', $inspection);

        event(new QualityCompleted($inspection));

        if ($inspection->result === 'unqualified') {
            $this->notificationService->sendToRole(
                'warehouse_manager',
                '质检不合格通知',
                "物料 #{$inspection->item_id} 检验不合格（{$inspection->rejected_size} 件），请处理。",
                'warning',
                route('app.quality.inspections.show', $inspection)
            );
        }

        return redirect()->route('app.quality.inspections.show', $inspection)->with('success', '检验单创建成功');
    }

    public function show(QualityInspection $inspection)
    {
        Gate::authorize('view quality inspections');
        return view('app.quality.inspections.show', compact('inspection'));
    }

    public function reports()
    {
        Gate::authorize('view quality reports');
        $totalInspections = QualityInspection::count();
        $passCount = QualityInspection::where('result', 'pass')->count();
        $failCount = QualityInspection::where('result', 'fail')->count();
        $inspections = QualityInspection::with('item')->latest()->paginate(20);
        return view('app.quality.reports', compact('inspections', 'totalInspections', 'passCount', 'failCount'));
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
