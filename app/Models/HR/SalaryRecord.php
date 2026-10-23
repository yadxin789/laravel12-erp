<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

/**
 * 工资单
 */
class SalaryRecord extends Model
{
    public const STATUS = [
        'draft' => '草稿',
        'approved' => '已核准',
        'paid' => '已发放',
    ];

    protected $table = 'hr_salaries';

    protected $fillable = [
        'employee_id', 'period', 'base_salary', 'allowance', 'bonus', 'overtime_pay',
        'social_insurance', 'tax', 'other_deduction', 'net_pay', 'status', 'paid_at',
        'remark', 'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'bonus' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'social_insurance' => 'decimal:2',
        'tax' => 'decimal:2',
        'other_deduction' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * 应发合计
     */
    public function grossPay(): float
    {
        return (float) $this->base_salary
            + (float) $this->allowance
            + (float) $this->bonus
            + (float) $this->overtime_pay;
    }

    /**
     * 扣款合计
     */
    public function totalDeduction(): float
    {
        return (float) $this->social_insurance
            + (float) $this->tax
            + (float) $this->other_deduction;
    }

    /**
     * 实发工资（未填时按应发 - 扣款自动计算）
     */
    public function calcNetPay(): float
    {
        return round($this->grossPay() - $this->totalDeduction(), 2);
    }
}
