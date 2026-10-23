<?php

namespace App\Models\HR;

use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'emp_no', 'name', 'department', 'position', 'phone', 'email',
        'hire_date', 'leave_date', 'status', 'user_id',
        'department_id', 'position_id',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 所属部门（组织主数据）
     */
    public function departmentRef()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * 任职岗位（组织主数据）
     */
    public function positionRef()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * 任职岗位对应的系统角色（权限载体，来自岗位.default_role）
     */
    public function getRoleNameAttribute(): ?string
    {
        return $this->positionRef?->default_role;
    }

    /**
     * 角色中文岗位名
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role_name ? \App\Support\RoleCatalog::label($this->role_name) : '—';
    }
}
