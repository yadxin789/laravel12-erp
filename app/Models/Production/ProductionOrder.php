<?php

namespace App\Models\Production;

use App\Enums\ProductionOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_no', 'item_id', 'quantity', 'completed_quantity', 'scrap_quantity',
        'status', 'bom_header_id', 'bom_version', 'routing_header_id', 'routing_version',
        'work_center_id', 'production_line_id', 'plan_start_date', 'plan_end_date',
        'actual_start_date', 'actual_end_date', 'planned_cost', 'actual_cost',
        'production_plan_id', 'sales_order_id',
        'remark', 'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'completed_quantity' => 'decimal:4',
        'scrap_quantity' => 'decimal:4',
        'planned_cost' => 'decimal:4',
        'actual_cost' => 'decimal:4',
        'status' => ProductionOrderStatus::class,
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class);
    }

    public function routingHeader()
    {
        return $this->belongsTo(RoutingHeader::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(\App\Models\MasterData\WorkCenter::class);
    }

    public function productionLine()
    {
        return $this->belongsTo(\App\Models\MasterData\ProductionLine::class);
    }

    public function lines()
    {
        return $this->hasMany(ProductionOrderLine::class);
    }

    public function materialIssues()
    {
        return $this->hasMany(MaterialIssue::class);
    }

    public function materialReturns()
    {
        return $this->hasMany(MaterialReturn::class);
    }

    public function productionReports()
    {
        return $this->hasMany(ProductionReport::class);
    }

    public function productionReceipts()
    {
        return $this->hasMany(ProductionReceipt::class);
    }

    public function reworkOrders()
    {
        return $this->hasMany(ReworkOrder::class);
    }

    public function productionLosses()
    {
        return $this->hasMany(ProductionLoss::class);
    }

    public function canRelease()
    {
        return $this->status === ProductionOrderStatus::Draft ||
               $this->status === ProductionOrderStatus::MaterialShortage;
    }

    public function canStart()
    {
        return $this->status === ProductionOrderStatus::Ready ||
               $this->status === ProductionOrderStatus::Paused;
    }

    public function canComplete()
    {
        return $this->status === ProductionOrderStatus::InProgress;
    }

    public function canPause()
    {
        return $this->status === ProductionOrderStatus::InProgress;
    }

    public function canResume()
    {
        return $this->status === ProductionOrderStatus::Paused;
    }

    public function canClose()
    {
        return $this->status === ProductionOrderStatus::Draft ||
               $this->status === ProductionOrderStatus::Ready ||
               $this->status === ProductionOrderStatus::MaterialShortage ||
               $this->status === ProductionOrderStatus::Completed;
    }

    public function canCancel()
    {
        return $this->status === ProductionOrderStatus::Draft || 
               $this->status === ProductionOrderStatus::Ready ||
               $this->status === ProductionOrderStatus::MaterialShortage;
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->completed_quantity - $this->scrap_quantity;
    }
}
