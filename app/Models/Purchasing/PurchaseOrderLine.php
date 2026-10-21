<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'line_number', 'item_id', 'quantity', 'received_quantity',
        'unit_price', 'tax_rate', 'line_amount', 'line_tax', 'warehouse_id',
        'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'line_amount' => 'decimal:4',
        'line_tax' => 'decimal:4',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function receiptLines()
    {
        return $this->hasMany(ReceiptLine::class);
    }

    public function purchaseReturnLines()
    {
        return $this->hasMany(PurchaseReturnLine::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->received_quantity;
    }
}
