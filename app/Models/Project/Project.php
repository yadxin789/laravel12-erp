<?php
namespace App\Models\Project;
use Illuminate\Database\Eloquent\Model;
class Project extends Model {
    protected $fillable = ['name','description','status','start_date','end_date','budget','manager_id','customer_id'];
    public function tasks() { return $this->hasMany(ProjectTask::class); }
}
