<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'crm_leads';

    protected $fillable = [
        'name', 'contact_person', 'phone', 'email', 'company',
        'source', 'status', 'remark', 'assigned_to', 'created_by',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}
