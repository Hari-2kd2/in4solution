<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ViewEmployeeInOutData extends Model
{
    protected $table ="view_employee_in_out_data";
    protected $primaryKey ="employee_attendance_id";
    protected $fillable = ['branch_id', 'approve_over_time_id', 'incentive_details_id', 'comp_off_details_id', 'finger_print_id', 'date', 'in_time', 'out_time', 'working_time', 'working_hour', 'over_time', 'early_by', 'late_by', 'in_out_time', 'shift_name', 'work_shift_id', 'device_name', 'inout_status', 'live_status', 'attendance_status', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
}
