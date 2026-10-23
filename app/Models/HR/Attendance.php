<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const STATUS = [
        'normal' => '正常',
        'late' => '迟到',
        'early' => '早退',
        'absent' => '缺勤',
        'leave' => '请假',
    ];

    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out', 'status', 'remark',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
