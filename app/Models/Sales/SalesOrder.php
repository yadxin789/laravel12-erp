<?php

namespace App\Models\Sales;

use App\Enums\SalesOrderStatus;
use App\Support\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes, HasAttachments;

    protected $fillable = [
        'order_no', 'customer_id', 'order_date', 'delivery_date', 'status',
        'total_amount', 'total_tax', 'total_net_amount', 'delivered_quantity',
        'total_quantity', 'currency', 'payment_terms', 'remark',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_amount' => 'decimal:4',
        'total_tax' => 'decimal:4',
        'total_net_amount' => 'decimal:4',
        'delivered_quantity' => 'decimal:4',
        'total_quantity' => 'decimal:4',
        'status' => SalesOrderStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\MasterData\Customer::class);
    }

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function productionPlanLines()
    {
        return $this->hasMany(\App\Models\Production\ProductionPlanLine::class);
    }

    public function canSubmit()
    {
        return $this->status === SalesOrderStatus::Draft;
    }

    public function canApprove()
    {
        return $this->status === SalesOrderStatus::Submitted ||
               $this->status === SalesOrderStatus::ChangePending;
    }

    public function canCancel()
    {
        return $this->status === SalesOrderStatus::Draft ||
               $this->status === SalesOrderStatus::Submitted ||
               $this->status === SalesOrderStatus::Approved ||
               $this->status === SalesOrderStatus::ChangePending ||
               $this->status === SalesOrderStatus::PartiallyDelivered;
    }

    public function canClose()
    {
        return $this->status === SalesOrderStatus::FullyDelivered;
    }

    public function canDeliver()
    {
        return $this->status === SalesOrderStatus::Approved ||
               $this->status === SalesOrderStatus::PartiallyDelivered;
    }

    public function canUpdate()
    {
        return $this->status === SalesOrderStatus::Draft;
    }
}
