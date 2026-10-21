<?php

namespace App\Models\Quality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityInspectionLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_inspection_id', 'line_number', 'inspection_item', 'specification',
        'result', 'judgment', 'remark',
    ];

    public function qualityInspection()
    {
        return $this->belongsTo(QualityInspection::class);
    }
}
