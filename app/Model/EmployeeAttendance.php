<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    // use BranchTrait;
    use CrudTrait;
    protected $table = 'employee_attendance';
    protected $primaryKey = 'employee_attendance_id';

    protected $fillable = [
        'finger_print_id',
        'employee_id',
        'branch_id',
        'in_out_time',
        'face_id',
        'latitude',
        'longitude',
        'employee_id',
        'check_type',
        'uri',
        'status',
        'inout_status',
    ];
}
