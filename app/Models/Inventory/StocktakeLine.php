<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocktakeLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_id', 'line_number', 'item_id', 'batch_number', 'system_quantity',
        'actual_quantity', 'difference', 'status', 'remark',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'actual_quantity' => 'decimal:4',
        'difference' => 'decimal:4',
    ];

    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }
}
