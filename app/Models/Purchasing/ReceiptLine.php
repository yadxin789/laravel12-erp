<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id', 'line_number', 'item_id', 'quantity', 'inspected_quantity',
        'qualified_quantity', 'rejected_quantity', 'purchase_order_line_id',
        'batch_number', 'production_date', 'expiry_date', 'quality_status', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'inspected_quantity' => 'decimal:4',
        'qualified_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function purchaseOrderLine()
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }
}
