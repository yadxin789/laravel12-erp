<?php
namespace App\Models\Asset;
use Illuminate\Database\Eloquent\Model;
class Asset extends Model {
    protected $fillable = ['asset_no','name','category','location','original_cost','useful_life_years','purchase_date','start_depreciation_date','status','assigned_to'];

    public function getMonthlyDepreciationAttribute(): float {
        return round($this->original_cost / ($this->useful_life_years * 12), 2);
    }

    public function getAccumulatedDepreciationAttribute(): float {
        if (!$this->start_depreciation_date) return 0;
        $months = now()->diffInMonths($this->start_depreciation_date);
        return round($this->monthly_depreciation * min($months, $this->useful_life_years * 12), 2);
    }

    public function getNetValueAttribute(): float {
        return round($this->original_cost - $this->accumulated_depreciation, 2);
    }
}
