<?php
namespace App\Models\HR;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model {
    protected $fillable = ['employee_id','date','check_in','check_out','status','remark'];
    protected $casts = ['check_in' => 'datetime', 'check_out' => 'datetime'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
