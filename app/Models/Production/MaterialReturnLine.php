<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialReturnLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_return_id', 'line_number', 'item_id', 'quantity', 'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function materialReturn()
    {
        return $this->belongsTo(MaterialReturn::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }
}
