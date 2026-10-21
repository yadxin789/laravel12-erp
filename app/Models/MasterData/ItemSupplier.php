<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'supplier_id', 'unit_price', 'currency', 'lead_time', 'primary', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'primary' => 'boolean',
        'active' => 'boolean',
        'unit_price' => 'decimal:4',
        'lead_time' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
