<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeLeaves extends Model
{
    protected $table = 'employee_leaves';
    protected $primaryKey = 'leave_id';

    protected $fillable = [
        'leave_id', 'employee_id', 'branch_id', 'casual_leave', 'privilege_leave',
        'sick_leave','sick_leave','maternity_leave','paternity_leave','OD'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }
}
