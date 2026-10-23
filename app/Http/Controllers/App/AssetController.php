<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Asset\Asset;
use App\Models\HR\Employee;
use Illuminate\Http\Request;

/**
 * 固定资产模块：资产台账 + 直线法折旧 + 状态流转
 */
class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('assignee');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('asset_no', 'like', "%{$kw}%")->orWhere('name', 'like', "%{$kw}%");
            });
        }

        $assets = $query->orderBy('asset_no')->paginate(20)->appends($request->query());

        $all = Asset::get();
        $stats = [
            'count' => $all->count(),
            'cost' => $all->sum(fn ($a) => (float) $a->original_cost),
            'net' => $all->sum(fn ($a) => $a->netValue()),
            'depreciation' => $all->sum(fn ($a) => $a->accumulatedDepreciation()),
        ];

        return view('app.assets.index', [
            'assets' => $assets,
            'stats' => $stats,
            'statusLabels' => Asset::STATUS,
            'categories' => Asset::CATEGORIES,
        ]);
    }

    public function create()
    {
        return view('app.assets.create', [
            'employees' => Employee::active()->orderBy('emp_no')->get(['id', 'emp_no', 'name']),
            'statusLabels' => Asset::STATUS,
            'categories' => Asset::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asset_no' => 'required|string|max:50|unique:assets,asset_no',
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'original_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:50',
            'purchase_date' => 'required|date',
            'start_depreciation_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(Asset::STATUS)),
            'assigned_to' => 'nullable|exists:employees,id',
        ], [
            'asset_no.required' => '请填写资产编号',
            'asset_no.unique' => '该资产编号已存在',
            'name.required' => '请填写资产名称',
            'original_cost.required' => '请填写资产原值',
            'useful_life_years.required' => '请填写折旧年限',
            'purchase_date.required' => '请选择购置日期',
        ]);

        $data['start_depreciation_date'] = $data['start_depreciation_date'] ?: $data['purchase_date'];

        Asset::create($data);

        return redirect()->route('app.assets.index')->with('success', '资产已登记');
    }

    public function show(Asset $asset)
    {
        $asset->load('assignee');

        return view('app.assets.show', [
            'asset' => $asset,
            'statusLabels' => Asset::STATUS,
        ]);
    }

    public function edit(Asset $asset)
    {
        return view('app.assets.edit', [
            'asset' => $asset,
            'employees' => Employee::active()->orderBy('emp_no')->get(['id', 'emp_no', 'name']),
            'statusLabels' => Asset::STATUS,
            'categories' => Asset::CATEGORIES,
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'asset_no' => 'required|string|max:50|unique:assets,asset_no,' . $asset->id,
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:100',
            'original_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1|max:50',
            'purchase_date' => 'required|date',
            'start_depreciation_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(Asset::STATUS)),
            'assigned_to' => 'nullable|exists:employees,id',
        ], [
            'asset_no.unique' => '该资产编号已存在',
            'name.required' => '请填写资产名称',
        ]);

        $data['start_depreciation_date'] = $data['start_depreciation_date'] ?: $data['purchase_date'];

        $asset->update($data);

        return redirect()->route('app.assets.index')->with('success', '资产已更新');
    }

    /**
     * 状态流转：领用 / 归还 / 送修 / 报废
     */
    public function changeStatus(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Asset::STATUS)),
            'assigned_to' => 'nullable|exists:employees,id',
        ]);

        $asset->update([
            'status' => $data['status'],
            'assigned_to' => $data['status'] === 'idle' || $data['status'] === 'scrapped'
                ? null
                : ($data['assigned_to'] ?? $asset->assigned_to),
        ]);

        return back()->with('success', '资产状态已更新为「' . Asset::STATUS[$data['status']] . '」');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('app.assets.index')->with('success', '资产已删除');
    }
}
