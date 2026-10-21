<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_no', 'production_order_id', 'operation_id', 'report_date',
        'quantity', 'scrap_quantity', 'rework_quantity', 'remark', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'scrap_quantity' => 'decimal:4',
        'rework_quantity' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}
