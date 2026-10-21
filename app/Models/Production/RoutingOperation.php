<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'routing_header_id', 'operation_number', 'operation_code', 'operation_name',
        'work_center_id', 'production_line_id', 'standard_time', 'setup_time',
        'require_report', 'require_inspection', 'is_subcontract', 'remark',
    ];

    protected $casts = [
        'standard_time' => 'decimal:2',
        'setup_time' => 'decimal:2',
        'require_report' => 'boolean',
        'require_inspection' => 'boolean',
        'is_subcontract' => 'boolean',
    ];

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
}
