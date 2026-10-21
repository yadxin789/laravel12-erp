<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReceiptLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_receipt_id', 'line_number', 'item_id', 'quantity',
        'batch_number', 'production_date', 'quality_status', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function productionReceipt()
    {
        return $this->belongsTo(ProductionReceipt::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }
}
