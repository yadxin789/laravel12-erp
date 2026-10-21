<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\ProductionPlanStatus;

class ProductionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_no', 'plan_date', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'approved_at' => 'datetime',
        'status' => ProductionPlanStatus::class,
    ];

    public function lines()
    {
        return $this->hasMany(ProductionPlanLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function canUpdate()
    {
        return $this->status === ProductionPlanStatus::Draft;
    }

    public function canApprove()
    {
        return $this->status === ProductionPlanStatus::Draft;
    }

    public function canDelete()
    {
        return $this->status === ProductionPlanStatus::Draft;
    }

    public function canConvertToOrders()
    {
        return $this->status === ProductionPlanStatus::Approved;
    }
}
