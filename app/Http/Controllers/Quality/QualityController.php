<?php

namespace App\Http\Controllers\Quality;

use App\Http\Controllers\Controller;
use App\Models\Quality\QualityInspection;
use App\Services\QualityService;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    protected $service;

    public function __construct(QualityService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', QualityInspection::class);

        $query = QualityInspection::with('item');

        if ($request->filled('inspection_no')) {
            $query->where('inspection_no', 'like', "%{$request->inspection_no}%");
        }
        if ($request->filled('inspection_type')) {
            $query->where('inspection_type', $request->inspection_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', "%{$request->batch_number}%");
        }

        $inspections = $query->paginate(20);

        return view('quality.inspections.index', compact('inspections'));
    }

    public function create()
    {
        $this->authorize('create', QualityInspection::class);

        return view('quality.inspections.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', QualityInspection::class);

        $inspection = $this->service->createInspection($request->all());

        AuditLogger::create('quality_inspection', $inspection);

        return redirect()->route('quality-inspections.index')->with('success', '检验单创建成功');
    }

    public function show(QualityInspection $qualityInspection)
    {
        $this->authorize('view', $qualityInspection);

        return view('quality.inspections.show', compact('qualityInspection'));
    }

    public function edit(QualityInspection $qualityInspection)
    {
        $this->authorize('update', $qualityInspection);

        return view('quality.inspections.edit', compact('qualityInspection'));
    }

    public function complete(Request $request, QualityInspection $qualityInspection)
    {
        $this->authorize('approve', $qualityInspection);

        $inspection = $this->service->completeInspection($qualityInspection, $request->results);

        AuditLogger::approve('quality_inspection', $inspection);

        return redirect()->route('quality-inspections.index')->with('success', '检验完成');
    }
}
