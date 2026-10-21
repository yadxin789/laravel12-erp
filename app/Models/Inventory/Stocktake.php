<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_no', 'warehouse_id', 'stocktake_date', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at',
    ];

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(StocktakeLine::class);
    }
}
