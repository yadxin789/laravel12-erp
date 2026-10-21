<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialIssueLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_issue_id', 'line_number', 'item_id', 'quantity',
        'production_order_line_id', 'batch_number', 'remark',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function materialIssue()
    {
        return $this->belongsTo(MaterialIssue::class);
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function productionOrderLine()
    {
        return $this->belongsTo(ProductionOrderLine::class);
    }
}
