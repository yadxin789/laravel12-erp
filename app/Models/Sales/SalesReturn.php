<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_no', 'customer_id', 'sales_order_id', 'return_date', 'status',
        'total_amount', 'reason', 'remark', 'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:4',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function lines()
    {
        return $this->hasMany(SalesReturnLine::class);
    }
}
