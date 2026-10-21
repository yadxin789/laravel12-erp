<?php
namespace App\Models\Project;
use Illuminate\Database\Eloquent\Model;
class TimeEntry extends Model {
    protected $fillable = ['project_task_id','user_id','date','hours','description'];
    public function task() { return $this->belongsTo(ProjectTask::class); }
}
