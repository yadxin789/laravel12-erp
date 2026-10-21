<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'category', 'value', 'value_type', 'label', 'description',
        'active', 'editable', 'sort_order', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'editable' => 'boolean',
    ];
}
