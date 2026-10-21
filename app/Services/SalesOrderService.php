<?php

namespace App\Services;

use App\Enums\SalesOrderStatus;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderLine;
use App\Models\System\NumberSequence;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $orderNo = $this->generateOrderNo();

            $order = SalesOrder::create([
                'order_no' => $orderNo,
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'],
                'status' => SalesOrderStatus::Draft,
                'currency' => $data['currency'] ?? 'CNY',
                'payment_terms' => $data['payment_terms'] ?? null,
                'remark' => $data['remark'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->createLines($order, $data['lines'] ?? []);

            return $order->load('lines');
        });
    }

    public function update(SalesOrder $order, array $data)
    {
        if ($order->status !== SalesOrderStatus::Draft) {
            throw new \Exception('已提交的订单不能修改');
        }

        return DB::transaction(function () use ($order, $data) {
            $order->update([
                'customer_id' => $data['customer_id'] ?? $order->customer_id,
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

    public function submit(SalesOrder $order)
    {
        if (!$order->canSubmit()) {
            throw new \Exception('订单状态不允许提交');
        }

        $order->status = SalesOrderStatus::Submitted;
        $order->save();

        return $order;
    }

    public function approve(SalesOrder $order)
    {
        if (!$order->canApprove()) {
            throw new \Exception('订单状态不允许审核');
        }

        $order->status = SalesOrderStatus::Approved;
        $order->approved_by = auth()->id();
        $order->approved_at = now();
        $order->save();

        return $order;
    }

    public function cancel(SalesOrder $order)
    {
        if (!$order->canCancel()) {
            throw new \Exception('订单状态不允许取消');
        }

        $order->status = SalesOrderStatus::Cancelled;
        $order->save();

        return $order;
    }

    public function updateDelivery(SalesOrder $order, $quantity)
    {
        $order->delivered_quantity += $quantity;

        if ($order->delivered_quantity >= $order->total_quantity) {
            $order->status = SalesOrderStatus::FullyDelivered;
        } elseif ($order->delivered_quantity > 0) {
            $order->status = SalesOrderStatus::PartiallyDelivered;
        }

        $order->save();

        return $order;
    }

    public function close(SalesOrder $order)
    {
        if ($order->status !== SalesOrderStatus::FullyDelivered) {
            throw new \Exception('订单未完全交付，不能关闭');
        }

        $order->status = SalesOrderStatus::Closed;
        $order->save();

        return $order;
    }

    protected function createLines(SalesOrder $order, array $lines)
    {
        $totalAmount = 0;
        $totalTax = 0;
        $totalQuantity = 0;

        foreach ($lines as $index => $lineData) {
            $lineAmount = $lineData['quantity'] * $lineData['unit_price'];
            $lineTax = $lineAmount * ($lineData['tax_rate'] ?? 0) / 100;

            SalesOrderLine::create([
                'sales_order_id' => $order->id,
                'line_number' => $index + 1,
                'item_id' => $lineData['item_id'],
                'quantity' => $lineData['quantity'],
                'delivered_quantity' => 0,
                'unit_price' => $lineData['unit_price'],
                'tax_rate' => $lineData['tax_rate'] ?? 0,
                'line_amount' => $lineAmount,
                'line_tax' => $lineTax,
                'warehouse_id' => $lineData['warehouse_id'] ?? null,
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

    protected function generateOrderNo()
    {
        $sequence = NumberSequence::firstOrCreate(
            ['code' => 'SO'],
            ['name' => '销售订单', 'prefix' => 'SO', 'number_length' => 6, 'year_reset' => true]
        );

        $year = now()->format('Y');
        $number = str_pad($sequence->current_number, $sequence->number_length, '0', STR_PAD_LEFT);
        $orderNo = "{$sequence->prefix}-{$year}-{$number}";

        $sequence->current_number++;
        $sequence->save();

        return $orderNo;
    }
}
