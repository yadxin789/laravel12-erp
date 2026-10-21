<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\Purchasing\PurchaseOrder;
use App\Models\Purchasing\PurchaseOrderLine;
use App\Models\System\NumberSequence;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orderNo = $this->generateOrderNo();

            $order = PurchaseOrder::create([
                'order_no' => $orderNo,
                'supplier_id' => $data['supplier_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'],
                'status' => PurchaseOrderStatus::Draft,
                'currency' => $data['currency'] ?? 'CNY',
                'payment_terms' => $data['payment_terms'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->createLines($order, $data['lines'] ?? []);

            return $order->load('lines');
        });
    }

    public function update(PurchaseOrder $order, array $data)
    {
        if ($order->status !== PurchaseOrderStatus::Draft) {
            throw new \Exception('已提交的采购订单不能修改');
        }

        return DB::transaction(function () use ($order, $data) {
            $order->update([
                'supplier_id' => $data['supplier_id'] ?? $order->supplier_id,
                'order_date' => $data['order_date'] ?? $order->order_date,
                'delivery_date' => $data['delivery_date'] ?? $order->delivery_date,
                'currency' => $data['currency'] ?? $order->currency,
                'payment_terms' => $data['payment_terms'] ?? $order->payment_terms,
                'remark' => $data['remark'] ?? $order->remark,
                'updated_by' => auth()->id(),
            ]);

            if (!empty($data['lines'])) {
                $order->lines()->delete();
                $this->createLines($order, $data['lines']);
            }

            return $order->load('lines');
        });
    }

    public function submit(PurchaseOrder $order)
    {
        if (!$order->canSubmit()) {
            throw new \Exception('采购订单状态不允许提交');
        }

        $order->status = PurchaseOrderStatus::Submitted;
        $order->save();

        return $order;
    }

    public function approve(PurchaseOrder $order)
    {
        if (!$order->canApprove()) {
            throw new \Exception('采购订单状态不允许审核');
        }

        $order->status = PurchaseOrderStatus::Approved;
        $order->approved_by = auth()->id();
        $order->approved_at = now();
        $order->save();

        return $order;
    }

    public function cancel(PurchaseOrder $order)
    {
        if (!$order->canCancel()) {
            throw new \Exception('采购订单状态不允许取消');
        }

        $order->status = PurchaseOrderStatus::Cancelled;
        $order->save();

        return $order;
    }

    public function receive(PurchaseOrder $order, array $data)
    {
        if ($order->status !== PurchaseOrderStatus::Approved && 
            $order->status !== PurchaseOrderStatus::PartiallyReceived) {
            throw new \Exception('采购订单状态不允许收货');
        }

        return DB::transaction(function () use ($order, $data) {
            $inventoryService = app(InventoryService::class);
            $totalReceived = 0;

            foreach ($data['lines'] ?? [] as $lineData) {
                $purchaseLine = PurchaseOrderLine::where('purchase_order_id', $order->id)
                    ->where('item_id', $lineData['item_id'])
                    ->firstOrFail();

                $purchaseLine->received_quantity += $lineData['quantity'];
                $purchaseLine->save();

                $inventoryService->receive([
                    'item_id' => $lineData['item_id'],
                    'warehouse_id' => $purchaseLine->warehouse_id ?? 1,
                    'quantity' => $lineData['quantity'],
                    'batch_number' => $lineData['batch_number'] ?? null,
                    'transaction_no' => $this->generateReceiptNo(),
                    'reference_no' => $order->order_no,
                ]);

                $totalReceived += $lineData['quantity'];
            }

            return $this->updateReceiving($order, $totalReceived);
        });
    }

    public function updateReceiving(PurchaseOrder $order, $quantity)
    {
        $order->received_quantity += $quantity;

        if ($order->received_quantity >= $order->total_quantity) {
            $order->status = PurchaseOrderStatus::FullyReceived;
        } elseif ($order->received_quantity > 0) {
            $order->status = PurchaseOrderStatus::PartiallyReceived;
        }

        $order->save();

        return $order;
    }

    public function close(PurchaseOrder $order)
    {
        if ($order->status !== PurchaseOrderStatus::FullyReceived) {
            throw new \Exception('采购订单未完全收货，不能关闭');
        }

        $order->status = PurchaseOrderStatus::Closed;
        $order->save();

        return $order;
    }

    protected function createLines(PurchaseOrder $order, array $lines)
    {
        $totalAmount = 0;
        $totalTax = 0;
        $totalQuantity = 0;

        foreach ($lines as $index => $lineData) {
            $lineAmount = $lineData['quantity'] * $lineData['unit_price'];
            $lineTax = $lineAmount * ($lineData['tax_rate'] ?? 0) / 100;

            PurchaseOrderLine::create([
                'purchase_order_id' => $order->id,
                'line_number' => $index + 1,
                'item_id' => $lineData['item_id'],
                'quantity' => $lineData['quantity'],
                'received_quantity' => 0,
                'unit_price' => $lineData['unit_price'],
                'tax_rate' => $lineData['tax_rate'] ?? 0,
                'line_amount' => $lineAmount,
                'line_tax' => $lineTax,
                'warehouse_id' => $lineData['warehouse_id'] ?? null,
                'batch_number' => $lineData['batch_number'] ?? null,
                'remark' => $lineData['remark'] ?? null,
            ]);

            $totalAmount += $lineAmount;
            $totalTax += $lineTax;
            $totalQuantity += $lineData['quantity'];
        }

        $order->update([
            'total_amount' => $totalAmount,
            'total_tax' => $totalTax,
            'total_net_amount' => $totalAmount + $totalTax,
            'total_quantity' => $totalQuantity,
        ]);
    }

    protected function generateReceiptNo()
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => 'RC'],
            ['name' => '收货单', 'prefix' => 'RC', 'number_length' => 6, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $receiptNo = "{$sequence->prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $receiptNo;
    }

    protected function generateOrderNo()
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => 'PO'],
            ['name' => '采购订单', 'prefix' => 'PO', 'number_length' => 6, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $orderNo = "{$sequence->prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $orderNo;
    }
}
