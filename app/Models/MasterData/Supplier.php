<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
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

    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_suppliers');
    }

    public function itemSuppliers()
    {
        return $this->hasMany(ItemSupplier::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(\App\Models\Purchasing\PurchaseOrder::class);
    }

    public function receipts()
    {
        return $this->hasMany(\App\Models\Purchasing\Receipt::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(\App\Models\Purchasing\PurchaseReturn::class);
    }

    public function evaluations()
    {
        return $this->hasMany(\App\Models\Purchasing\SupplierEvaluation::class);
    }
}
