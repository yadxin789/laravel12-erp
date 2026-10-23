<?php

namespace App\Models\HR;

use App\Models\MasterData\Department;
use App\Models\MasterData\Position;
use Illuminate\Database\Eloquent\Model;

/**
 * 招聘需求
 */
class RecruitmentRequest extends Model
{
    public const STATUS = [
        'open' => '招聘中',
        'filled' => '已招满',
        'closed' => '已关闭',
    ];

    public const REASONS = ['新增编制', '离职补员', '业务扩产', '项目用工', '其他'];

    public const EMPLOYMENT_TYPES = [
        'full_time' => '全职',
        'part_time' => '兼职',
        'intern' => '实习',
    ];

    protected $table = 'hr_recruitments';

    protected $fillable = [
        'req_no', 'position_id', 'department_id', 'title', 'headcount', 'reason',
        'employment_type', 'salary_range_min', 'salary_range_max', 'expect_date',
        'status', 'remark', 'created_by',
    ];

    protected $casts = [
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'expect_date' => 'date',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'recruitment_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getEmploymentTypeLabelAttribute(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? $this->employment_type;
    }

    /**
     * 已录用人数
     */
    public function getHiredCountAttribute(): int
    {
        return $this->applications()->where('status', 'hired')->count();
    }
}
