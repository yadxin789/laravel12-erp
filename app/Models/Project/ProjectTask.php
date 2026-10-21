<?php
namespace App\Models\Project;
use Illuminate\Database\Eloquent\Model;
class ProjectTask extends Model {
    protected $fillable = ['project_id','name','description','status','priority','due_date','assignee_id'];
    public function project() { return $this->belongsTo(Project::class); }
    public function timeEntries() { return $this->hasMany(TimeEntry::class); }
}
