<?php
namespace App\Models\HR;
use Illuminate\Database\Eloquent\Model;
class Employee extends Model {
    protected $fillable = ['emp_no','name','department','position','phone','email','hire_date','leave_date','status','user_id'];
    public function attendances() { return $this->hasMany(Attendance::class); }
}
