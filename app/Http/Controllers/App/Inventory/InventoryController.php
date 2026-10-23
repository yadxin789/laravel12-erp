<?php

namespace App\Http\Controllers\App\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryBalance;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();

        // 原 index 未查询余额数据，页面永远显示“暂无数据”；此处按筛选条件查询
        $query = InventoryBalance::with(['item', 'warehouse']);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }

        $balances = $query->orderBy('item_id')->paginate(20)->withQueryString();

        return view('app.inventory.index', compact('items', 'warehouses', 'balances'));
    }

    public function transactions(Request $request)
    {
        Gate::authorize('view inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();
        $types = \App\Enums\InventoryTransactionType::options();

        $query = \App\Models\Inventory\InventoryTransaction::with(['item', 'warehouse'])->latest();

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->paginate(20)->withQueryString();

        return view('app.inventory.transactions', compact('items', 'warehouses', 'types', 'transactions'));
    }

    public function transfer()
    {
        Gate::authorize('transfer inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.inventory.transfer', compact('items', 'warehouses'));
    }

    public function storeTransfer(Request $request)
    {
        Gate::authorize('transfer inventory');
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'batch_number' => 'nullable|string',
        ]);

        $balance = InventoryBalance::where('item_id', $validated['item_id'])
            ->where('warehouse_id', $validated['from_warehouse_id'])
            ->first();

        if (!$balance || $balance->available_quantity < $validated['quantity']) {
            return back()->with('error', '转出仓库库存不足');
        }

        $balance->quantity -= $validated['quantity'];
        $balance->save();

        $toBalance = InventoryBalance::firstOrCreate(
            ['item_id' => $validated['item_id'], 'warehouse_id' => $validated['to_warehouse_id']],
            ['quantity' => 0, 'reserved_quantity' => 0, 'frozen_quantity' => 0]
        );
        $toBalance->quantity += $validated['quantity'];
        $toBalance->save();

        $this->auditLog('transfer', 'Inventory', null, "调拨物料 #{$validated['item_id']} 数量 {$validated['quantity']}");

        return redirect()->route('app.inventory.index')->with('success', '库存调拨成功');
    }

    public function adjust()
    {
        Gate::authorize('adjust inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.inventory.adjust', compact('items', 'warehouses'));
    }

    public function storeAdjust(Request $request)
    {
        Gate::authorize('adjust inventory');
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer',
            'reason' => 'required|string',
        ]);

        $balance = InventoryBalance::firstOrCreate(
            ['item_id' => $validated['item_id'], 'warehouse_id' => $validated['warehouse_id']],
            ['quantity' => 0, 'reserved_quantity' => 0, 'frozen_quantity' => 0]
        );

        $balance->quantity += $validated['quantity'];
        $balance->save();

        $this->auditLog('adjust', 'Inventory', null, "调整物料 #{$validated['item_id']} 数量变更 {$validated['quantity']}，原因：{$validated['reason']}");

        return redirect()->route('app.inventory.index')->with('success', '库存调整成功');
    }

    public function freeze()
    {
        Gate::authorize('freeze inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.inventory.freeze', compact('items', 'warehouses'));
    }

    public function storeFreeze(Request $request)
    {
        Gate::authorize('freeze inventory');
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'action' => 'required|in:freeze,unfreeze',
        ]);

        $balance = InventoryBalance::where('item_id', $validated['item_id'])
            ->where('warehouse_id', $validated['warehouse_id'])
            ->first();

        if (!$balance) {
            return back()->with('error', '库存不存在');
        }

        if ($validated['action'] === 'freeze') {
            if ($balance->available_quantity < $validated['quantity']) {
                return back()->with('error', '可用库存不足');
            }
            $balance->frozen_quantity += $validated['quantity'];
        } else {
            if ($balance->frozen_quantity < $validated['quantity']) {
                return back()->with('error', '冻结库存不足');
            }
            $balance->frozen_quantity -= $validated['quantity'];
        }

        $balance->save();

        $actionText = $validated['action'] === 'freeze' ? '冻结' : '解冻';
        $this->auditLog($validated['action'], 'Inventory', null, "{$actionText}物料 #{$validated['item_id']} 数量 {$validated['quantity']}");

        return redirect()->route('app.inventory.index')->with('success', "库存{$actionText}成功");
    }

    public function stocktake()
    {
        Gate::authorize('stocktake inventory');
        $items = Item::all();
        $warehouses = Warehouse::all();
        return view('app.inventory.stocktake', compact('items', 'warehouses'));
    }

    public function storeStocktake(Request $request)
    {
        Gate::authorize('stocktake inventory');
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'lines' => 'required|array',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.actual_quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['lines'] as $line) {
            $balance = InventoryBalance::firstOrCreate(
                ['item_id' => $line['item_id'], 'warehouse_id' => $validated['warehouse_id']],
                ['quantity' => 0, 'reserved_quantity' => 0, 'frozen_quantity' => 0]
            );
            
            $difference = $line['actual_quantity'] - $balance->quantity;
            $balance->quantity = $line['actual_quantity'];
            $balance->save();
        }

        $this->auditLog('stocktake', 'Inventory', null, "仓库 #{$validated['warehouse_id']} 盘点完成，共 " . count($validated['lines']) . " 项");

        return redirect()->route('app.inventory.index')->with('success', '库存盘点完成');
    }

    public function trace()
    {
        Gate::authorize('trace inventory');
        return view('app.inventory.trace');
    }

    public function searchTrace(Request $request)
    {
        Gate::authorize('trace inventory');
        $validated = $request->validate([
            'batch_number' => 'required|string',
        ]);

        $transactions = \App\Models\Inventory\InventoryTransaction::where('batch_number', $validated['batch_number'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 原代码 compact('batch_number') 引用了未定义变量，查询结果页会报错
        return view('app.inventory.trace', [
            'transactions' => $transactions,
            'batch_number' => $validated['batch_number'],
        ]);
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
