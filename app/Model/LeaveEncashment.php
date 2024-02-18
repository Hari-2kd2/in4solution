<?php

namespace App\Model;

use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use App\Repositories\LeaveRepository;
use Illuminate\Database\Eloquent\Model;

class LeaveEncashment extends Model
{
    protected $table = 'leave_encashment';
    protected $primaryKey = 'enc_entry_id';
    const PENDING=0, APPROVED=1, REJECTED=3;
    public $statusData = [
        self::PENDING => 'Under Review',
        self::APPROVED => 'Given',
        self::REJECTED => 'Rejected',
    ];

    protected $fillable = [
        'enc_entry_id','branch_id','employee_id','year_id','enc_leave_type_id','enc_submit_on','enc_open','enc_days','enc_close','enc_amount','enc_status','enc_remark'
    ];

    public function LeaveEncashmentList($employee_id=null) {
        if($employee_id===false) { // admin application list
            $LoggedEmployee = Employee::find(session('logged_session_data.employee_id'));
            $supervisorIds = $LoggedEmployee->supervisorIds();
            $roleId = session('logged_session_data.role_id');
            
            if($roleId==1) {
                $LeaveEncashmentList = LeaveEncashment::with(['Employee', 'actionBy', 'LeaveType'])
                    ->orderBy('enc_entry_id', 'desc')
                    ->limit(400)->get();
            } else {
                $LeaveEncashmentList = LeaveEncashment::with(['Employee', 'actionBy', 'LeaveType'])
                    ->orderBy('enc_entry_id', 'desc')
                    ->where('employee_id', '!=', session('logged_session_data.employee_id'))
                    ->whereIn('employee_id', $supervisorIds)
                    ->limit(400)->get();
            }
            return $LeaveEncashmentList;
        } else if($employee_id===null) { // logged employee list
            $employee_id = session('logged_session_data.employee_id');
        } else if($employee_id) { // API employee_id employee list
            $employee_id = $employee_id;
        }
        $LeaveEncashmentList = LeaveEncashment::with(['Employee', 'actionBy', 'LeaveType'])
            ->where('employee_id', session('logged_session_data.employee_id'))
            ->orderBy('enc_entry_id', 'desc')
            ->limit(200)->get();
        return $LeaveEncashmentList;
    }

    public function LeaveEncashmentData($employee_id=null) {
        // we can use API by pass employee_id and web app can by using session
        $employee_id = $employee_id ? $employee_id : session('logged_session_data.employee_id');
        $calanderYear = calanderYear::currentYear();
        $Employee = Employee::findOrFail($employee_id);
        $LeaveEncashmentExists = LeaveEncashment::where('year_id', $calanderYear->year_id)->where('employee_id', $employee_id)->first();
        $encahStatus = false;
        $encahStatusMessage = '';
        $CAN_USE_MAX_PL = (($Employee->EmployeeLeaves->privilege_leave ?? 0) - LeaveRepository::SHOULD_MIN_PL) ?? 0;
        if(!$LeaveEncashmentExists) {
            $encahStatus = $CAN_USE_MAX_PL > 0 ? true : false;
            $encahStatusMessage = ($encahStatus ? 'You can use maximum '.$CAN_USE_MAX_PL. ' days for PL encashment.' : 'You could not use PL encashment.');
        } else if ($LeaveEncashmentExists) {
            $encahStatus = $this->statusData[$LeaveEncashmentExists->enc_status] ?? '';
            $encahStatusMessage = $encahStatus ? $encahStatus.' PL encashment.' : '';
            if($LeaveEncashmentExists->enc_status==$LeaveEncashmentExists::REJECTED) {
                $encahStatus = true;
                $encahStatusMessage .= 'Re-apply PL encashment.';
            }
        }
        
        $LeaveEncashmentData = [
            'Employee' => $Employee,
            'statusData' => $this->statusData,
            'encahStatus' => $encahStatus,
            'encahStatusMessage' => $encahStatusMessage,
            'calanderYear' => $calanderYear,
            'SHOULD_MIN_PL' => LeaveRepository::SHOULD_MIN_PL,
            'CAN_USE_MAX_PL' => $CAN_USE_MAX_PL,
        ];
        return $LeaveEncashmentData;
    }

    public function getEncActionByDisplayAttribute() {
        $actionBy = Employee::find($this->enc_action_by);
        if($actionBy) {
            $actionByDetail = '';
            $roleId = session('logged_session_data.role_id');
            $actionByDetail .= $actionBy->first_name . ' (' . $actionBy->emp_code . ')';
            if($roleId==1) {
                $actionByDetail .= ($actionBy->branch ? ', '.$actionBy->branch->branch_name : '');
            }
            return $actionByDetail;
        }
    }

    public function getEmployeeNameAttribute($value)
    {
        return $this->Employee ? $this->Employee->fullname() : '';
    }

    public function getEmpCodeAttribute($value)
    {
        return $this->Employee ? $this->Employee->emp_code : '';
    }

    public function getEncSubmitOnDisplayAttribute($value)
    {
        return $this->enc_submit_on ? dateTimeConvertDBtoForm($this->enc_submit_on) : '';
    }

    public function getEncSalaryDateDisplayAttribute($value)
    {
        return $this->enc_salary_date ? dateConvertDBtoForm($this->enc_salary_date) : '';
    }

    public function getEncActionOnDisplayAttribute($value)
    {
        return $this->enc_action_on ? dateTimeConvertDBtoForm($this->enc_action_on) : '';
    }

    public function getEncStatusDisplayAttribute($value)
    {
        return ($this->statusData[$this->enc_status] ?? '');
    }

    public function Employee()
    {
      return $this->belongsTo(Employee::class, 'employee_id');
    }

    // approved or rejected both also update in this field (enc_action_by)
    public function actionBy()
    {
      return $this->belongsTo(Employee::class, 'enc_action_by', 'employee_id');
    }

    public function LeaveType()
    {
      return $this->belongsTo(LeaveType::class, 'enc_leave_type_id');
    }
  
    public function calanderYear()
    {
      return $this->belongsTo(calanderYear::class, 'year_id');
    }
  

} // end class LeaveEncashment
