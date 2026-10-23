<?php

namespace App\Models\Asset;

use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    public const STATUS = [
        'in_use' => '在用',
        'idle' => '闲置',
        'repair' => '维修中',
        'scrapped' => '已报废',
    ];

    public const CATEGORIES = ['生产设备', '运输设备', '办公设备', '电子设备', '房屋建筑', '其他'];

    protected $fillable = [
        'asset_no', 'name', 'category', 'location', 'original_cost', 'useful_life_years',
        'purchase_date', 'start_depreciation_date', 'status', 'assigned_to',
    ];

    protected $casts = [
        'original_cost' => 'decimal:4',
    ];

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * 月折旧额（直线法，残值率 5%）
     */
    public function monthlyDepreciation(): float
    {
        $life = max((int) $this->useful_life_years, 1);
        $cost = (float) $this->original_cost;
        $residual = $cost * 0.05;

        return round(($cost - $residual) / ($life * 12), 2);
    }

    /**
     * 已计提月数（从开始折旧月到当前月，不超过总月数）
     */
    public function elapsedMonths(): int
    {
        if (! $this->start_depreciation_date) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->start_depreciation_date);
        $months = $start->diffInMonths(now());
        $total = max((int) $this->useful_life_years, 1) * 12;

        return (int) min(max($months, 0), $total);
    }

    /**
     * 累计折旧
     */
    public function accumulatedDepreciation(): float
    {
        return round($this->monthlyDepreciation() * $this->elapsedMonths(), 2);
    }

    /**
     * 账面净值
     */
    public function netValue(): float
    {
        return round((float) $this->original_cost - $this->accumulatedDepreciation(), 2);
    }
}
