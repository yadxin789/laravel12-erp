<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'address', 'warehouse_type', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function inventoryBalances()
    {
        return $this->hasMany(\App\Models\Inventory\InventoryBalance::class);
    }

    public function receipts()
    {
        return $this->hasMany(\App\Models\Purchasing\Receipt::class);
    }

    public function shipments()
    {
        return $this->hasMany(\App\Models\Sales\Shipment::class);
    }

    public function materialIssues()
    {
        return $this->hasMany(\App\Models\Production\MaterialIssue::class);
    }

    public function stocktakes()
    {
        return $this->hasMany(\App\Models\Inventory\Stocktake::class);
    }
}
