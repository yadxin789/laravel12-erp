<?php

namespace App\Services;

use App\Models\Inventory\InventoryBalance;
use App\Models\Production\BomHeader;
use App\Models\Production\BomLine;
use App\Models\Sales\SalesOrder;
use App\Models\MasterData\Item;
use Illuminate\Support\Facades\DB;

class MRPService
{
    public function calculate(array $params = [])
    {
        $requirements = $this->collectRequirements($params);
        $inventory = $this->collectInventory();

        $results = [];

        foreach ($requirements as $itemId => $requirement) {
            $item = Item::find($itemId);
            
            $availableInventory = $inventory[$itemId]['total'] ?? 0;
            $reserved = $inventory[$itemId]['reserved'] ?? 0;
            $onOrder = $this->getOnOrderQuantity($itemId);
            
            $netRequirement = $requirement - ($availableInventory - $reserved) + $item->safety_stock - $onOrder;
            
            if ($netRequirement <= 0) {
                continue;
            }

            $suggestedQuantity = max($netRequirement, $item->min_order_quantity);

            $results[] = [
                'item_id' => $itemId,
                'item_code' => $item->code,
                'item_name' => $item->name,
                'gross_requirement' => $requirement,
                'available_inventory' => $availableInventory - $reserved,
                'on_order' => $onOrder,
                'safety_stock' => $item->safety_stock,
                'net_requirement' => $netRequirement,
                'suggested_quantity' => $suggestedQuantity,
                'min_order_quantity' => $item->min_order_quantity,
                'item_type' => $item->item_type,
            ];
        }

        return $results;
    }

    protected function collectRequirements(array $params = [])
    {
        $requirements = [];

        $salesOrders = SalesOrder::where('status', 'approved')
            ->where('delivered_quantity', '<', 'total_quantity');

        if (!empty($params['start_date'])) {
            $salesOrders->where('delivery_date', '>=', $params['start_date']);
        }

        if (!empty($params['end_date'])) {
            $salesOrders->where('delivery_date', '<=', $params['end_date']);
        }

        foreach ($salesOrders->get() as $order) {
            foreach ($order->lines as $line) {
                $remaining = $line->quantity - $line->delivered_quantity;
                $requirements[$line->item_id] = ($requirements[$line->item_id] ?? 0) + $remaining;
            }
        }

        $bomRequirements = $this->explodeBOM($requirements);
        foreach ($bomRequirements as $itemId => $quantity) {
            $requirements[$itemId] = ($requirements[$itemId] ?? 0) + $quantity;
        }

        return $requirements;
    }

    protected function explodeBOM(array $requirements)
    {
        $bomRequirements = [];
        $processedItems = [];

        foreach ($requirements as $itemId => $quantity) {
            $this->explodeItemBOM($itemId, $quantity, $bomRequirements, $processedItems);
        }

        return $bomRequirements;
    }

    protected function explodeItemBOM($itemId, $quantity, &$bomRequirements, &$processedItems)
    {
        if (isset($processedItems[$itemId])) {
            return;
        }
        $processedItems[$itemId] = true;

        $bomHeader = BomHeader::where('item_id', $itemId)
            ->where('status', 'approved')
            ->orderBy('version', 'desc')
            ->first();

        if (!$bomHeader) {
            return;
        }

        foreach ($bomHeader->lines as $bomLine) {
            $bomRequirements[$bomLine->item_id] = 
                ($bomRequirements[$bomLine->item_id] ?? 0) + 
                $bomLine->net_quantity * $quantity;

            $this->explodeItemBOM($bomLine->item_id, $bomLine->net_quantity * $quantity, $bomRequirements, $processedItems);
        }
    }

    protected function collectInventory()
    {
        $inventory = [];

        foreach (InventoryBalance::all() as $balance) {
            if (!isset($inventory[$balance->item_id])) {
                $inventory[$balance->item_id] = [
                    'total' => 0,
                    'reserved' => 0,
                ];
            }
            $inventory[$balance->item_id]['total'] += $balance->quantity;
            $inventory[$balance->item_id]['reserved'] += $balance->reserved_quantity;
        }

        return $inventory;
    }

    protected function getOnOrderQuantity($itemId)
    {
        return \App\Models\Purchasing\PurchaseOrderLine::where('item_id', $itemId)
            ->whereHas('purchaseOrder', function ($query) {
                $query->whereIn('status', ['approved', 'partially_received']);
            })
            ->sum('quantity') - 
            \App\Models\Purchasing\PurchaseOrderLine::where('item_id', $itemId)
                ->whereHas('purchaseOrder', function ($query) {
                    $query->whereIn('status', ['approved', 'partially_received']);
                })
                ->sum('received_quantity');
    }
}
