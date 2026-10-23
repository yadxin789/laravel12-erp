<?php

namespace App\Models\Project;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    public const STATUS = [
        'todo' => '待办',
        'in_progress' => '进行中',
        'done' => '已完成',
    ];

    public const PRIORITY = [
        1 => '高',
        2 => '中',
        3 => '低',
    ];

    protected $fillable = [
        'project_id', 'name', 'description', 'status', 'priority', 'due_date', 'assignee_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITY[$this->priority] ?? '中';
    }

    public function getTotalHoursAttribute(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }
}
