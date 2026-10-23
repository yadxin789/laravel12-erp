<?php

namespace App\Models\Quality;

use App\Enums\InspectionType;
use App\Enums\QualityResult;
use App\Support\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspection extends Model
{
    use HasFactory, HasAttachments;

    protected $fillable = [
        'inspection_no', 'inspection_type', 'source_document_id', 'source_document_type',
        'item_id', 'batch_number', 'inspection_date', 'sample_size', 'inspected_size',
        'qualified_size', 'rejected_size', 'result', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'sample_size' => 'decimal:4',
        'inspected_size' => 'decimal:4',
        'qualified_size' => 'decimal:4',
        'rejected_size' => 'decimal:4',
        'inspection_type' => InspectionType::class,
        'result' => QualityResult::class,
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\MasterData\Item::class);
    }

    public function lines()
    {
        return $this->hasMany(QualityInspectionLine::class);
    }
}
