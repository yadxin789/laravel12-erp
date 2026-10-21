<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issue_no', 'issue_type', 'production_order_id', 'work_center_id',
        'issue_date', 'warehouse_id', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(\App\Models\MasterData\WorkCenter::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\App\Models\MasterData\Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(MaterialIssueLine::class);
    }
}
