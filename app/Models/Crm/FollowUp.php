<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $table = 'crm_follow_ups';

    protected $fillable = [
        'lead_id', 'opportunity_id', 'type', 'content', 'created_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }
}
