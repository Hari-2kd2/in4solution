<?php

namespace App\Model;

use session;
use App\Traits\CrudTrait;
use App\Components\Common;
use App\Traits\BranchTrait;
use App\Model\LeavePermissionCase;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\LeaveStatus;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class LeavePermission extends Model
{
    // use BranchTrait;
    // use CrudTrait;
    protected $table = 'leave_permission';
    protected $primaryKey = 'leave_permission_id';

    protected $fillable = [
        'leave_permission_id', 'employee_id', 'branch_id', 'permission_duration', 'leave_permission_date', 'leave_permission_purpose', 'status', 'plant_head', 'department_head',
        'plant_approval_status', 'department_approval_status', 'from_time', 'to_time', 'head_remarks', 'reject_by', 'reject_date', 'approve_by', 'approve_date', 'functional_head_status', 'functional_head_approved_by', 'functional_head_reject_by',
        'functional_head_approve_date', 'functional_head_reject_date', 'pass_date', 'pass_by', 'functional_head_remark'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function approveBy()
    {
        return $this->belongsTo(Employee::class, 'approve_by', 'employee_id');
    }

    public function rejectBy()
    {
        return $this->belongsTo(Employee::class, 'reject_by', 'employee_id');
    }
    public function approveByFunctionalHead()
    {
        return $this->belongsTo(Employee::class, 'functional_head_approved_by', 'employee_id');
    }

    public function rejectByFunctionalHead()
    {
        return $this->belongsTo(Employee::class, 'functional_head_reject_by', 'employee_id');
    }
    public function getPermissionsInfo($request)
    {
        $Employee = Employee::find($request->employee_id ? $request->employee_id : session('logged_session_data.employee_id'));
        // the below line for mobile date no need to convert
        $permission_date = $request->permission_date ? $request->permission_date : date('Y-m-d');
        if (session('logged_session_data.employee_id') && $request->permission_date) {
            $permission_date = dateConvertFormtoDB($request->permission_date);
        }

        $YEAR  = date("Y", strtotime($permission_date));
        $MONTH = date("m", strtotime($permission_date));

        $PERMISSION_LIMIT = Common::PERMISSION_LIMIT;
        $PER_PERMISSION_HOUR = Common::PER_PERMISSION_HOUR;
        $PERMISSION_USED = LeavePermission::whereMonth('leave_permission_date', $MONTH)
            ->whereYear('leave_permission_date', $YEAR)
            ->where('employee_id', $Employee->employee_id)
            ->where('status', '!=', LeaveStatus::$REJECT)->count(); // approved
        $PERMISSION_PENDING = LeavePermission::whereMonth('leave_permission_date', '=', $MONTH)->whereYear('leave_permission_date', '=', $YEAR)
            ->where('status', LeaveStatus::$PENDING)
            ->where('employee_id', $Employee->employee_id)->count(); // pending
        $PERMISSION_BALANCE = $PERMISSION_LIMIT - $PERMISSION_USED;
        $info = [
            'YEAR' => $YEAR,
            'MONTH' => $MONTH,
            'PERMISSION_BALANCE' => $PERMISSION_BALANCE,
            'PERMISSION_LIMIT' => $PERMISSION_LIMIT,
            'PER_PERMISSION_HOUR' => $PER_PERMISSION_HOUR,
            'PERMISSION_USED' => $PERMISSION_USED,
            'PERMISSION_PENDING' => $PERMISSION_PENDING,
        ];
        return $info;
    }

    public function allChecks($request, $INFO)
    {
        $Employee = Employee::find($request->employee_id ? $request->employee_id : session('logged_session_data.employee_id'));
        $permission_date = $request->permission_date;
        if (session('logged_session_data.employee_id')) {
            $permission_date = dateConvertFormtoDB($request->permission_date);
        }
        info(__FUNCTION__ . ':' . __LINE__ . ': ' . $permission_date);
        $if_leave = \App\Model\LeaveApplicationCase::where('employee_id', $Employee->employee_id)
            ->whereRaw("'" . $permission_date . "' BETWEEN application_from_date AND application_to_date")
            ->where('status', '!=', LeaveStatus::$REJECT)->count(); // not rejected applications

        $if_publicHoliday = DB::table('holiday_details')
            ->whereRaw("'" . $permission_date . "' BETWEEN from_date AND to_date")
            ->count();

        $if_rhLeave = DB::table('restricted_holiday_application')
            ->where('employee_id', $Employee->employee_id)
            ->whereDate('holiday_date', $permission_date)
            ->where('status', '!=', LeaveStatus::$REJECT)->count();

        $DAY_NAME = date('l', strtotime($permission_date));
        $messageIs = '';
        if ($if_leave) {
            $messageIs = 'Already exists leave application to the selected date.';
        } else if ($if_publicHoliday) {
            $messageIs = 'The selected date is a holiday.';
        } else if ($if_rhLeave) {
            $messageIs = 'The selected date is a RH.';
        } else if ($DAY_NAME == Config::get('leave.weekly_holiday')) {
            $messageIs = 'The selected date is a Week off.';
        }

        $if_exists = LeavePermissionCase::where('employee_id', $Employee->employee_id)->where('leave_permission_date', $permission_date)->where('status', '!=', LeaveStatus::$REJECT)->count();

        if ($if_exists) {
            $messageIs = 'A permission request already exists to the selected date.';
        } elseif ($INFO['PERMISSION_USED'] >= Common::PERMISSION_LIMIT) {
            $messageIs = 'You have already taken three permissions.';
        }
        return $messageIs;
    }
}
