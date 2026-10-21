<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\MasterData\Warehouse;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Warehouse::class);

        $query = Warehouse::where('active', true);

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        $warehouses = $query->paginate(20);

        return view('masterdata.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $this->authorize('create', Warehouse::class);

        return view('masterdata.warehouses.create');
    }

    public function store(WarehouseRequest $request)
    {
        $this->authorize('create', Warehouse::class);

        $warehouse = Warehouse::create($request->validated());

        AuditLogger::create('warehouse', $warehouse);

        return redirect()->route('admin.warehouses.index')->with('success', '仓库创建成功');
    }

    public function show(Warehouse $warehouse)
    {
        $this->authorize('view', $warehouse);

        return view('masterdata.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        return view('masterdata.warehouses.edit', compact('warehouse'));
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $this->authorize('update', $warehouse);

        $oldValues = $warehouse->toArray();
        $warehouse->update($request->validated());

        AuditLogger::update('warehouse', $warehouse, $oldValues);

        return redirect()->route('admin.warehouses.index')->with('success', '仓库更新成功');
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete', $warehouse);

        AuditLogger::delete('warehouse', $warehouse);
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('success', '仓库删除成功');
    }
}
