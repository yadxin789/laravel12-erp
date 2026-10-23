<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

/**
 * 绩效评估
 */
class PerformanceReview extends Model
{
    public const STATUS = [
        'draft' => '草稿',
        'completed' => '已完成',
    ];

    public const TYPES = [
        'monthly' => '月度',
        'quarterly' => '季度',
        'annual' => '年度',
    ];

    public const GRADES = [
        'A' => '优秀',
        'B' => '良好',
        'C' => '合格',
        'D' => '待改进',
    ];

    protected $table = 'hr_performances';

    protected $fillable = [
        'employee_id', 'period', 'type', 'score', 'grade', 'reviewer',
        'summary', 'improvement', 'status', 'reviewed_at', 'created_by',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'reviewed_at' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getGradeLabelAttribute(): string
    {
        return self::GRADES[$this->grade] ?? ($this->grade ?: '—');
    }

    /**
     * 按得分自动评级：≥90 A，≥80 B，≥60 C，其余 D
     */
    public static function gradeOf(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 60 => 'C',
            default => 'D',
        };
    }
}
