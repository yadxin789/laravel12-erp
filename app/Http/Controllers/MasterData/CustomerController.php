<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\MasterData\Customer;
use App\Support\AuditLogger;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::where('active', true);

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        $customers = $query->paginate(20);

        return $request->expectsJson()
            ? response()->json($customers)
            : view('masterdata.customers.index', compact('customers'));
    }

    public function create()
    {
        $this->authorize('create', Customer::class);

        return view('masterdata.customers.create');
    }

    public function store(CustomerRequest $request)
    {
        $this->authorize('create', Customer::class);

        $customer = Customer::create($request->validated());

        AuditLogger::create('customer', $customer);

        return redirect()->route('admin.customers.index')->with('success', '客户创建成功');
    }

    public function show(Customer $customer, Request $request)
    {
        $this->authorize('view', $customer);

        if ($request->expectsJson()) {
            return response()->json($customer);
        }

        return view('masterdata.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorize('update', $customer);

        return view('masterdata.customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $this->authorize('update', $customer);

        $oldValues = $customer->toArray();
        $customer->update($request->validated());

        AuditLogger::update('customer', $customer, $oldValues);

        return redirect()->route('admin.customers.index')->with('success', '客户更新成功');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);

        AuditLogger::delete('customer', $customer);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', '客户删除成功');
    }
}
