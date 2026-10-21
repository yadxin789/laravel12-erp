<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\MasterData\Supplier;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $query = Supplier::where('active', true);

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        $suppliers = $query->paginate(20);

        return $request->expectsJson()
            ? response()->json($suppliers)
            : view('masterdata.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create', Supplier::class);

        return view('masterdata.suppliers.create');
    }

    public function store(SupplierRequest $request)
    {
        $this->authorize('create', Supplier::class);

        $supplier = Supplier::create($request->validated());

        AuditLogger::create('supplier', $supplier);

        return redirect()->route('admin.suppliers.index')->with('success', '供应商创建成功');
    }

    public function show(Supplier $supplier, Request $request)
    {
        $this->authorize('view', $supplier);

        if ($request->expectsJson()) {
            return response()->json($supplier);
        }

        return view('masterdata.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        return view('masterdata.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $oldValues = $supplier->toArray();
        $supplier->update($request->validated());

        AuditLogger::update('supplier', $supplier, $oldValues);

        return redirect()->route('admin.suppliers.index')->with('success', '供应商更新成功');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        AuditLogger::delete('supplier', $supplier);
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('success', '供应商删除成功');
    }
}
