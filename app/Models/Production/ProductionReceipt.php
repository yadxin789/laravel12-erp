<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'receipt_no', 'production_order_id', 'receipt_date', 'warehouse_id',
        'status', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(ProductionReceiptLine::class);
    }
}
