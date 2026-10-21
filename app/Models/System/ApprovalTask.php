<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class ApprovalTask extends Model
{
    protected $fillable = [
        'document_type', 'document_id', 'step_order', 'step_name',
        'approver_id', 'status', 'comment', 'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approver_id');
    }
}
