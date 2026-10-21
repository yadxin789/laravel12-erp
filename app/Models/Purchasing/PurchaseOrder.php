<?php

namespace App\Models\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Support\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, HasAttachments;

    protected $fillable = [
        'order_no', 'supplier_id', 'order_date', 'delivery_date', 'status',
        'total_amount', 'total_tax', 'total_net_amount', 'received_quantity',
        'total_quantity', 'currency', 'payment_terms', 'remark',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:4',
        'total_tax' => 'decimal:4',
        'total_net_amount' => 'decimal:4',
        'received_quantity' => 'decimal:4',
        'total_quantity' => 'decimal:4',
        'status' => PurchaseOrderStatus::class,
    ];

    public function supplier()
    {
        return $this->belongsTo(\App\Models\MasterData\Supplier::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function purchaseReturns()
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function canSubmit()
    {
        return $this->status === PurchaseOrderStatus::Draft;
    }

    public function canApprove()
    {
        return $this->status === PurchaseOrderStatus::Submitted;
    }

    public function canCancel()
    {
        return $this->status === PurchaseOrderStatus::Draft ||
               $this->status === PurchaseOrderStatus::Submitted ||
               $this->status === PurchaseOrderStatus::Approved ||
               $this->status === PurchaseOrderStatus::PartiallyReceived;
    }

    public function canClose()
    {
        return $this->status === PurchaseOrderStatus::FullyReceived;
    }

    public function canReceive()
    {
        return $this->status === PurchaseOrderStatus::Approved ||
               $this->status === PurchaseOrderStatus::PartiallyReceived;
    }
}
