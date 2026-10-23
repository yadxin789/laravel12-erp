<?php

namespace App\Models\Project;

use App\Models\MasterData\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public const STATUS = [
        'planning' => '规划中',
        'in_progress' => '进行中',
        'on_hold' => '已挂起',
        'completed' => '已完成',
        'cancelled' => '已取消',
    ];

    protected $fillable = [
        'name', 'description', 'status', 'start_date', 'end_date', 'budget',
        'manager_id', 'customer_id',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * 已投入工时（汇总任务下的所有工时记录）
     */
    public function getTotalHoursAttribute(): float
    {
        return (float) \App\Models\Project\TimeEntry::whereIn(
            'project_task_id',
            $this->tasks()->pluck('id')
        )->sum('hours');
    }

    public function getDoneTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }
}
