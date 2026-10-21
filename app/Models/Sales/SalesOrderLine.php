<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id', 'line_number', 'item_id', 'quantity', 'delivered_quantity',
        'unit_price', 'tax_rate', 'line_amount', 'line_tax', 'warehouse_id', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'delivered_quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'line_amount' => 'decimal:4',
        'line_tax' => 'decimal:4',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function shipmentLines()
    {
        return $this->hasMany(ShipmentLine::class);
    }

    public function salesReturnLines()
    {
        return $this->hasMany(SalesReturnLine::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->delivered_quantity;
    }
}
