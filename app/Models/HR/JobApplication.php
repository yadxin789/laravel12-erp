<?php

namespace App\Models\HR;

use App\Models\MasterData\Position;
use Illuminate\Database\Eloquent\Model;

/**
 * 应聘者（招聘流程：投递 → 筛选 → 面试 → 录用/淘汰）
 */
class JobApplication extends Model
{
    public const STATUS = [
        'applied' => '已投递',
        'screening' => '筛选中',
        'interview' => '面试中',
        'offered' => '已发 Offer',
        'hired' => '已录用',
        'rejected' => '已淘汰',
    ];

    /** 允许推进的下一状态 */
    public const NEXT = [
        'applied' => ['screening', 'rejected'],
        'screening' => ['interview', 'rejected'],
        'interview' => ['offered', 'rejected'],
        'offered' => ['hired', 'rejected'],
        'hired' => [],
        'rejected' => [],
    ];

    public const SOURCES = ['招聘网站', '内部推荐', '校园招聘', '猎头', '现场招聘会', '其他'];

    public const EDUCATIONS = ['高中', '大专', '本科', '硕士', '博士', '其他'];

    protected $table = 'hr_applications';

    protected $fillable = [
        'app_no', 'name', 'phone', 'email', 'position_id', 'recruitment_id',
        'education', 'experience_years', 'expect_salary', 'source', 'status',
        'interview_at', 'hired_at', 'evaluation', 'created_by',
    ];

    protected $casts = [
        'interview_at' => 'datetime',
        'hired_at' => 'date',
        'expect_salary' => 'decimal:2',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function recruitment()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'recruitment_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['hired', 'rejected'], true);
    }
}
