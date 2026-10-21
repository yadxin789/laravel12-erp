<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_no', 'supplier_id', 'evaluation_date', 'quality_score',
        'delivery_score', 'service_score', 'price_score', 'overall_score', 'remark',
        'created_by',
    ];

    protected $casts = [
        'quality_score' => 'decimal:2',
        'delivery_score' => 'decimal:2',
        'service_score' => 'decimal:2',
        'price_score' => 'decimal:2',
        'overall_score' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }
}
