<?php

namespace App\Services;

use App\Enums\ProductionOrderStatus;
use App\Models\Production\BomHeader;
use App\Models\Production\BomLine;
use App\Models\Production\ProductionOrder;
use App\Models\Production\ProductionOrderLine;
use App\Models\System\NumberSequence;
use Illuminate\Support\Facades\DB;

class ProductionOrderService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orderNo = $this->generateOrderNo();

            if (isset($data['bom_header_id'])) {
                $bomHeader = BomHeader::findOrFail($data['bom_header_id']);
            } else {
                $bomHeader = BomHeader::where('item_id', $data['item_id'])
                    ->where('version', $data['bom_version'] ?? 'default')
                    ->firstOrFail();
            }
            
            if (!$bomHeader->isApproved()) {
                throw new \Exception('BOM未审核，不能创建生产工单');
            }

            $order = ProductionOrder::create([
                'order_no' => $orderNo,
                'item_id' => $bomHeader->item_id,
                'quantity' => $data['quantity'],
                'completed_quantity' => 0,
                'scrap_quantity' => 0,
                'status' => ProductionOrderStatus::Draft,
                'bom_header_id' => $bomHeader->id,
                'bom_version' => $bomHeader->version,
                'routing_header_id' => $data['routing_header_id'] ?? null,
                'work_center_id' => $data['work_center_id'] ?? null,
                'production_line_id' => $data['production_line_id'] ?? null,
                'plan_start_date' => $data['plan_start_date'] ?? null,
                'plan_end_date' => $data['plan_end_date'] ?? null,
                'production_plan_id' => $data['production_plan_id'] ?? null,
                'sales_order_id' => $data['sales_order_id'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->createOrderLines($order, $bomHeader);

            // 计算计划成本：BOM物料成本 * 生产数量
            $plannedCost = 0;
            foreach ($order->lines as $line) {
                if ($line->item && $line->item->unit_cost) {
                    $plannedCost += $line->quantity * $line->item->unit_cost;
                }
            }
            if ($plannedCost > 0) {
                $order->planned_cost = $plannedCost;
                $order->save();
            }

            return $order->load('lines');
        });
    }

    public function release(ProductionOrder $order)
    {
        if (!$order->canRelease()) {
            throw new \Exception('工单状态不允许下达');
        }

        $shortage = $this->checkMaterialShortage($order);
        if (!empty($shortage)) {
            $order->status = ProductionOrderStatus::MaterialShortage;
            $order->save();
            throw new \Exception('存在物料短缺，请先补充库存');
        }

        $order->status = ProductionOrderStatus::Ready;
        $order->approved_by = auth()->id();
        $order->approved_at = now();
        $order->save();

        return $order;
    }

    public function start(ProductionOrder $order)
    {
        if (!$order->canStart()) {
            throw new \Exception('工单状态不允许开工');
        }

        $order->status = ProductionOrderStatus::InProgress;
        $order->actual_start_date = now();
        $order->save();

        return $order;
    }

    public function report(ProductionOrder $order, array $data)
    {
        if ($order->status !== ProductionOrderStatus::InProgress) {
            throw new \Exception('工单未在执行中');
        }

        $order->completed_quantity += $data['completed_quantity'] ?? $data['quantity'];
        $order->scrap_quantity += $data['scrap_quantity'] ?? 0;

        if ($order->completed_quantity >= $order->quantity) {
            $order->status = ProductionOrderStatus::Completed;
            $order->actual_end_date = now();
        }

        $order->save();

        return $order;
    }

    public function complete(ProductionOrder $order)
    {
        if (!$order->canComplete()) {
            throw new \Exception('工单状态不允许完成');
        }

        $order->status = ProductionOrderStatus::Completed;
        $order->actual_end_date = now();
        $order->save();

        return $order;
    }

    public function close(ProductionOrder $order)
    {
        if (!$order->canClose()) {
            throw new \Exception('工单状态不允许关闭');
        }

        $order->status = ProductionOrderStatus::Closed;
        $order->save();

        return $order;
    }

    public function cancel(ProductionOrder $order)
    {
        if (!$order->canCancel()) {
            throw new \Exception('工单状态不允许取消');
        }

        $order->status = ProductionOrderStatus::Cancelled;
        $order->save();

        return $order;
    }

    public function checkMaterialShortage(ProductionOrder $order)
    {
        $shortage = [];
        $inventoryService = app(InventoryService::class);

        foreach ($order->lines as $line) {
            $available = $inventoryService->getAvailable($line->item_id, $order->workCenter?->warehouse_id ?? 1);
            if ($available < $line->required_quantity - $line->issued_quantity) {
                $shortage[] = [
                    'item_id' => $line->item_id,
                    'item_name' => $line->item->name,
                    'required' => $line->required_quantity - $line->issued_quantity,
                    'available' => $available,
                ];
            }
        }

        return $shortage;
    }

    protected function createOrderLines(ProductionOrder $order, BomHeader $bomHeader)
    {
        foreach ($bomHeader->lines as $index => $bomLine) {
            $netQuantity = $bomLine->quantity * $order->quantity * (1 + $bomLine->scrap_rate);

            ProductionOrderLine::create([
                'production_order_id' => $order->id,
                'line_number' => $index + 1,
                'item_id' => $bomLine->item_id,
                'required_quantity' => $netQuantity,
                'issued_quantity' => 0,
                'returned_quantity' => 0,
                'scrap_quantity' => 0,
                'remark' => null,
            ]);
        }
    }

    protected function generateOrderNo()
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => 'WO'],
            ['name' => '生产工单', 'prefix' => 'WO', 'number_length' => 6, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $orderNo = "{$sequence->prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $orderNo;
    }
}
