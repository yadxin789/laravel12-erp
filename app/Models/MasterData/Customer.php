<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'short_name', 'contact_person', 'phone', 'email', 'address',
        'tax_id', 'payment_terms', 'credit_limit', 'active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:4',
        'active' => 'boolean',
    ];

    public function salesOrders()
    {
        return $this->hasMany(\App\Models\Sales\SalesOrder::class);
    }

    public function quotations()
    {
        return $this->hasMany(\App\Models\Sales\Quotation::class);
    }

    public function shipments()
    {
        return $this->hasMany(\App\Models\Sales\Shipment::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(\App\Models\Sales\SalesReturn::class);
    }
}
