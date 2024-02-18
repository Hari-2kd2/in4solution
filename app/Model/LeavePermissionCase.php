<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LeavePermissionCase extends Model
{
    protected $table = 'leave_permission';
    protected $primaryKey = 'leave_permission_id';

    protected $fillable = [
        'leave_permission_id','employee_id','branch_id','permission_duration','leave_permission_date','leave_permission_purpose','status','plant_head','department_head',
        'plant_approval_status','department_approval_status','from_time','to_time','head_remarks','reject_by','reject_date','approve_by','approve_date'
    ];
    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }

}
