<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class ApprovalFlowStep extends Model
{
    protected $fillable = ['approval_flow_id', 'step_order', 'name', 'approver_type', 'approver_value'];

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }
}
