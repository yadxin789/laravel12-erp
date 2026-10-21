<?php

namespace App\Models\Inventory;

use App\Enums\InventoryQualityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'warehouse_id', 'location_id', 'batch_number', 'serial_number',
        'quality_status', 'quantity', 'reserved_quantity', 'frozen_quantity',
        'expiry_date', 'production_date', 'supplier_id', 'production_order_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'frozen_quantity' => 'decimal:4',
        'quality_status' => InventoryQualityStatus::class,
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\MasterData\Location::class);
    }

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function productionOrder()
    {
        return $this->belongsTo(\App\Models\Production\ProductionOrder::class);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity - $this->frozen_quantity;
    }
}
