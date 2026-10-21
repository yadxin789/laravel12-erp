<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no', 'supplier_id', 'purchase_order_id', 'return_date', 'status',
        'total_amount', 'reason', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:4',
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseReturnLine::class);
    }
}
