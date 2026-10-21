<?php

namespace App\Services;

use App\Enums\InventoryQualityStatus;
use App\Enums\InventoryTransactionType;
use App\Events\InventoryChanged;
use App\Models\Inventory\InventoryBalance;
use App\Models\Inventory\InventoryTransaction;
use App\Models\MasterData\Item;
use App\Models\MasterData\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function receive(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = Item::findOrFail($data['item_id']);
            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            $qualityStatus = $item->quality_inspection_required 
                ? InventoryQualityStatus::Pending 
                : InventoryQualityStatus::Qualified;

            $balance = InventoryBalance::firstOrNew([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'quality_status' => $qualityStatus,
            ]);

            $balance->quantity = ($balance->quantity ?: 0) + ($data['quantity'] ?: 0);
            $balance->production_date = $data['production_date'] ?? $balance->production_date;
            $balance->expiry_date = $data['expiry_date'] ?? $balance->expiry_date;
            $balance->supplier_id = $data['supplier_id'] ?? $balance->supplier_id;
            $balance->production_order_id = $data['production_order_id'] ?? $balance->production_order_id;
            $balance->save();

            InventoryTransaction::create([
                'transaction_no' => $data['transaction_no'],
                'transaction_type' => InventoryTransactionType::PurchaseReceive,
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['warehouse_id'],
                'location_id' => $data['location_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'quantity' => $data['quantity'],
                'quality_status' => $qualityStatus,
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => $data['source_document_type'] ?? null,
                'source_document_line_id' => $data['source_document_line_id'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $balance;
        });

        event(new InventoryChanged(
            $data['item_id'],
            $data['warehouse_id'],
            'receive',
            $data['quantity'],
            $data['transaction_no']
        ));

        return $balance;
    }

    public function issue(array $data)
    {
        return DB::transaction(function () use ($data) {
            $item = Item::findOrFail($data['item_id']);

            $available = $this->getAvailable($data['item_id'], $data['warehouse_id'], $data['batch_number'] ?? null);

            if ($available < $data['quantity']) {
                throw new \Exception('可用库存不足');
            }

            $balances = InventoryBalance::where('item_id', $data['item_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('quality_status', InventoryQualityStatus::Qualified)
                ->where('quantity', '>', 0);

            if (!empty($data['batch_number'])) {
                $balances->where('batch_number', $data['batch_number']);
            }

            $balances = $balances->orderBy('production_date')->get();

            $remaining = $data['quantity'];
            foreach ($balances as $balance) {
                if ($remaining <= 0) break;

                $issueQty = min($balance->available_quantity, $remaining);

                $balance->quantity -= $issueQty;
                $balance->save();

                InventoryTransaction::create([
                    'transaction_no' => $data['transaction_no'],
                    'transaction_type' => InventoryTransactionType::ProductionIssue,
                    'item_id' => $data['item_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'location_id' => $balance->location_id,
                    'batch_number' => $balance->batch_number,
                    'serial_number' => $balance->serial_number,
                    'quantity' => $issueQty,
                    'quality_status' => $balance->quality_status,
                    'source_document_id' => $data['source_document_id'] ?? null,
                    'source_document_type' => $data['source_document_type'] ?? null,
                    'source_document_line_id' => $data['source_document_line_id'] ?? null,
                    'remark' => $data['remark'] ?? null,
                    'created_by' => $data['created_by'] ?? auth()->id(),
                ]);

                $remaining -= $issueQty;
            }

            if ($remaining > 0) {
                throw new \Exception('可用库存不足');
            }

            return true;
        });

        event(new InventoryChanged(
            $data['item_id'],
            $data['warehouse_id'],
            'issue',
            -$data['quantity'],
            $data['transaction_no']
        ));

        return true;
    }

    public function transfer(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->issue([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'batch_number' => $data['batch_number'] ?? null,
                'quantity' => $data['quantity'],
                'transaction_no' => $data['transaction_no'],
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => $data['source_document_type'] ?? null,
                'remark' => '调拨出库',
            ]);

            $this->receive([
                'item_id' => $data['item_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'location_id' => $data['to_location_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'quantity' => $data['quantity'],
                'transaction_no' => $data['transaction_no'],
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => $data['source_document_type'] ?? null,
                'remark' => '调拨入库',
            ]);

            return true;
        });
    }

    public function adjust(array $data)
    {
        return DB::transaction(function () use ($data) {
            $balance = InventoryBalance::findOrFail($data['balance_id']);

            $oldQuantity = $balance->quantity;
            $balance->quantity += $data['adjustment_quantity'];
            $balance->save();

            InventoryTransaction::create([
                'transaction_no' => $data['transaction_no'],
                'transaction_type' => InventoryTransactionType::Adjust,
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'location_id' => $balance->location_id,
                'batch_number' => $balance->batch_number,
                'serial_number' => $balance->serial_number,
                'quantity' => $data['adjustment_quantity'],
                'quality_status' => $balance->quality_status,
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => 'stocktake',
                'remark' => $data['remark'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return ['old_quantity' => $oldQuantity, 'new_quantity' => $balance->quantity];
        });
    }

    public function freeze(array $data)
    {
        return DB::transaction(function () use ($data) {
            $balance = InventoryBalance::findOrFail($data['balance_id']);

            if ($balance->available_quantity < $data['quantity']) {
                throw new \Exception('可用库存不足');
            }

            $balance->frozen_quantity += $data['quantity'];
            $balance->save();

            InventoryTransaction::create([
                'transaction_no' => $data['transaction_no'],
                'transaction_type' => InventoryTransactionType::Freeze,
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'location_id' => $balance->location_id,
                'batch_number' => $balance->batch_number,
                'quantity' => $data['quantity'],
                'quality_status' => $balance->quality_status,
                'remark' => $data['remark'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $balance;
        });
    }

    public function unfreeze(array $data)
    {
        return DB::transaction(function () use ($data) {
            $balance = InventoryBalance::findOrFail($data['balance_id']);

            if ($balance->frozen_quantity < $data['quantity']) {
                throw new \Exception('冻结数量不足');
            }

            $balance->frozen_quantity -= $data['quantity'];
            $balance->save();

            InventoryTransaction::create([
                'transaction_no' => $data['transaction_no'],
                'transaction_type' => InventoryTransactionType::Unfreeze,
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'location_id' => $balance->location_id,
                'batch_number' => $balance->batch_number,
                'quantity' => $data['quantity'],
                'quality_status' => $balance->quality_status,
                'remark' => $data['remark'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $balance;
        });
    }

    public function return(array $data)
    {
        return $this->receive($data);
    }

    public function getAvailable($itemId, $warehouseId, $batchNumber = null)
    {
        $query = InventoryBalance::where('item_id', $itemId)
            ->where('warehouse_id', $warehouseId)
            ->where('quality_status', InventoryQualityStatus::Qualified);

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        return $query->sum('quantity') - $query->sum('reserved_quantity') - $query->sum('frozen_quantity');
    }

    public function getBalance($itemId, $warehouseId = null)
    {
        $query = InventoryBalance::where('item_id', $itemId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->sum('quantity');
    }

    public function updateQualityStatus(array $data)
    {
        return DB::transaction(function () use ($data) {
            $balance = InventoryBalance::findOrFail($data['balance_id']);
            $balance->quality_status = $data['quality_status'];
            $balance->save();

            InventoryTransaction::create([
                'transaction_no' => $data['transaction_no'],
                'transaction_type' => InventoryTransactionType::Adjust,
                'item_id' => $balance->item_id,
                'warehouse_id' => $balance->warehouse_id,
                'location_id' => $balance->location_id,
                'batch_number' => $balance->batch_number,
                'quantity' => 0,
                'quality_status' => $data['quality_status'],
                'source_document_id' => $data['source_document_id'] ?? null,
                'source_document_type' => 'quality_inspection',
                'remark' => '质量状态变更',
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            return $balance;
        });
    }

    public function traceBatch($batchNumber, $itemId = null)
    {
        $query = InventoryTransaction::where('batch_number', $batchNumber);

        if ($itemId) {
            $query->where('item_id', $itemId);
        }

        return $query->orderBy('created_at')->get();
    }

    public function getBalancesByWarehouse($warehouseId)
    {
        return InventoryBalance::where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->with('item', 'location')
            ->get();
    }

    public function getLowStockItems($warehouseId = null)
    {
        $query = InventoryBalance::query();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->join('items', 'inventory_balances.item_id', '=', 'items.id')
            ->whereColumn('inventory_balances.quantity', '<', 'items.safety_stock')
            ->where('inventory_balances.quantity', '>', 0)
            ->select('items.*', 'inventory_balances.quantity', 'inventory_balances.warehouse_id')
            ->get();
    }
}
