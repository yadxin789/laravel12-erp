<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'req_no', 'source_type', 'source_id', 'status', 'remark',
        'created_by', 'approved_by', 'approved_at', 'updated_by',
    ];

    public function lines()
    {
        return $this->hasMany(PurchaseRequisitionLine::class);
    }
}
