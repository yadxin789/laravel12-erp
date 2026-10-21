<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_no', 'customer_id', 'sales_order_id', 'invoice_date', 'due_date',
        'subtotal', 'tax_amount', 'total_amount', 'paid_amount', 'status', 'remark', 'created_by',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(\App\Models\Sales\SalesOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
