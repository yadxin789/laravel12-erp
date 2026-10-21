<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id', 'line_number', 'item_id', 'required_quantity',
        'issued_quantity', 'returned_quantity', 'scrap_quantity', 'batch_number', 'remark',
    ];

    protected $casts = [
        'required_quantity' => 'decimal:4',
        'issued_quantity' => 'decimal:4',
        'returned_quantity' => 'decimal:4',
        'scrap_quantity' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function materialIssueLines()
    {
        return $this->hasMany(MaterialIssueLine::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->required_quantity - $this->issued_quantity + $this->returned_quantity;
    }
}
