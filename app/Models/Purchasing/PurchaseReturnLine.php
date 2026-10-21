<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id', 'line_number', 'item_id', 'quantity', 'purchase_order_line_id',
        'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
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
