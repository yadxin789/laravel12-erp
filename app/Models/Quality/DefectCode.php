<?php

namespace App\Models\Quality;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'category', 'description', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function defectRecords()
    {
        return $this->hasMany(DefectRecord::class);
    }
}
