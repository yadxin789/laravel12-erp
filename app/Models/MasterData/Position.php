<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code', 'name', 'department_id', 'level', 'headcount', 'default_role',
        'sort_order', 'active', 'description',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'headcount' => 'integer',
        'sort_order' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * 该岗位当前在职人数（优先按岗位外键统计，兼容旧的字符串岗位名）。
     */
    public function getEmployeeCountAttribute(): int
    {
        return \App\Models\HR\Employee::where('status', 'active')
            ->where(function ($q) {
                $q->where('position_id', $this->id)->orWhere('position', $this->name);
            })
            ->count();
    }

    /**
     * 任职该岗位默认授予的角色（权限载体）。
     */
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'default_role', 'name');
    }

    /**
     * 默认授予角色的中文岗位名。
     */
    public function getDefaultRoleLabelAttribute(): string
    {
        return $this->default_role ? \App\Support\RoleCatalog::label($this->default_role) : '—';
    }
}
