<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'work_center_id', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(\App\Models\Production\ProductionOrder::class);
    }
}
