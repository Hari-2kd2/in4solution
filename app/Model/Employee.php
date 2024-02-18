<?php

namespace App\Model;

use App\User;
use App\Traits\CrudTrait;
use App\Components\Common;
use App\Traits\BranchTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Employee extends Model
{
    // use BranchTrait;
    // use CrudTrait;
    // use EncryptedAttribute;
    public $maxDepth = 2;
    public $supervisorIds = [];
    protected $table = 'employee';
    protected $primaryKey = 'employee_id';
    protected $fillable = [
        'employee_id', 'branch_id', 'user_id', 'finger_id', 'department_id', 'designation_id', 'branch_id', 'work_shift', 'work_shift_id', 'supervisor_id', 'email', 'first_name',
        'last_name', 'date_of_birth', 'date_of_joining', 'date_of_leaving', 'relieving_reason', 'gender', 'marital_status', 'pf_account_number', 'esi_card_number',
        'photo', 'address', 'emergency_contacts', 'phone', 'status', 'created_by', 'updated_by', 'religion', 'device_employee_id', 'pf_status', 'basic', 'hra', 'da', 'pf', 'epf', 'insurance',
        'overtime_status', 'no_of_child', 'uan', 'cost_centre', 'pan_gir_no', 'salary_revision', 'salary_esi_stop', 'salary_ctc', 'salary_gross', 'bank_name', 'bank_account', 'bank_ifsc', 'emp_code', 'functional_head_id',
        'document_title', 'document_name', 'document_expiry', 'document_title2', 'document_name2', 'document_expiry2', 'document_title3', 'document_name3', 'document_expiry3', 'document_title4', 'document_name4', 'document_expiry4', 'document_title5', 'document_name5', 'document_expiry5',
        'document_title6', 'document_name6', 'document_expiry6', 'blood_group', 'permanent_status', 'relieving_remark'
    ];
    public $salary_month;

    public function __construct($params = [])
    {
        if (isset($params['salary_month'])) {
            // dd($params);
            $this->salary_month = $params['salary_month'];
        }
    }

    public function ExcelEmployee()
    {
        return $this->hasOne('App\Model\ExcelEmployee', 'emp_code', 'emp_code');
    }

    public function statmentEmployee()
    {
        // dd('salary_month='.$this->salary_month);
        $salary_month = request()->get('salary_month');
        // dd(' request ='.$salary_month);
        return $this->hasOne('App\Model\ExcelEmployee', 'emp_code', 'emp_code')->where('salary_month', $salary_month);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeFilter($query, $request)
    {
        return $query->where('department_id', $request['department_id'])->where('branch_id', $request['branch_id'])->where('employee_id', $request['employee_id']);
    }

    public function userName()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function designation_disp()
    {
        $disp = DB::table('designation')->where('designation_id', $this->designation_id)->first();
        return $disp ? $disp->designation_name : '';
    }

    public function department_disp()
    {
        $disp = DB::table('department')->where('department_id', $this->department_id)->first();
        return $disp ? $disp->department_name : $this->department_id;
    }

    public function department()
    {
        return $this->belongsTo(DepartmentCase::class, 'department_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationCase::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function payGrade()
    {
        return $this->belongsTo(PayGrade::class, 'pay_grade_id');
    }

    public function shiftDetail()
    {
        if ($this->workShift) {
            $shilfDetail = $this->workShift->shift_name;
            $shilfDetail .= '<br> (Start: ' . $this->workShift->start_time . ' - End: ' . $this->workShift->end_time . ') ';
            $shilfDetail .= '<br> Late Allow: ' . $this->workShift->late_count_time;
            return $shilfDetail;
        }
    }

    public $supervisorTitle;

    public function supervisorDetail()
    {
        if ($supervisor = $this->supervisor) {
            $supervisorDetail = '';
            $roleId = session('logged_session_data.role_id');
            $supervisorDetail .= $supervisor->fullname() . ' (' . $supervisor->emp_code . '';
            if ($roleId == 1) {
                $supervisorDetail .= ($supervisor->branch ? ', ' . $supervisor->branch->branch_name : '');
            }

            $supervisorDetail .= ')';
            $this->supervisorTitle = $supervisorDetail . PHP_EOL;
            $this->supervisorTitle .= $supervisor->designation_disp() . PHP_EOL;
            $this->supervisorTitle .= $supervisor->department_disp() . PHP_EOL;
            return ucwords(strtolower($supervisorDetail));
        }
    }

    public $functionalHeadTitle;

    public function functionalHeadDetail()
    {
        if ($functionalHead = $this->functional_head) {
            $functionalHeadDetail = '';
            $roleId = session('logged_session_data.role_id');
            $functionalHeadDetail .= $functionalHead->first_name . ' (' . $functionalHead->emp_code . '';
            if ($roleId == 1) {
                $functionalHeadDetail .= ($functionalHead->branch ? ', ' . $functionalHead->branch->branch_name : '');
            }

            $functionalHeadDetail .= ')';
            $this->functionalHeadTitle = $functionalHeadDetail . PHP_EOL;
            $this->functionalHeadTitle .= $functionalHead->designation_disp() . PHP_EOL;
            $this->functionalHeadTitle .= $functionalHead->department_disp() . PHP_EOL;
            return $functionalHeadDetail;
        }
    }

    public function supervisorList()
    {
        $employee_id = session('logged_session_data.employee_id');
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');
        $supervisorList = Employee::where('status', 1)->get();

        if ($branchId !== null && $roleId !== 1) {
            $supervisorList = Employee::where('status', 1)->where('employee_id', '!=', $employee_id)->where('branch_id', session('logged_session_data.branch_id'))->get();
        } elseif ($selectedbranchId !== null && $roleId == 1) {
            $supervisorList = Employee::selectRaw('employee_id, CONCAT(first_name, " (", emp_code, ")") AS fullname')->where('status', 1)->where('employee_id', '!=', $employee_id)->with('branch')->get();
        } else {
            $supervisorList = Employee::where('status', 1)->where('employee_id', '!=', $employee_id)->get();
        }
        return $supervisorList;
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function functional_head()
    {
        return $this->belongsTo(Employee::class, 'functional_head_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hourlySalaries()
    {
        return $this->belongsTo(HourlySalary::class, 'hourly_salaries_id');
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function EmployeeLeaves()
    {
        return $this->hasOne('App\Model\EmployeeLeaves', 'employee_id', 'employee_id');
    }

    public function maternity_leave_ploicy()
    {
        // maternity leave ploicy
        // 1. Granted only to female employees who have completed a minimum of 6 months of service in the company
        // 2. They may avail of maternity Leave for a maximum of 24 weeks
        // 3. In the larger interest of the nation, this can be availed for the first and second child.
        // Policy 1: Gender
        if ($this->gender == 'Female') {
            $result = ['status' => ''];

            if ($this->marital_status != 'Married') {
                $result['status'] = false;
                $result['message'] = 'This leave is not applicable Unmarried.';
                $result['maternity_leave'] = 'Not Applicable';
                return $result;
            }

            $EmployeeLeaves = $this->EmployeeLeaves;
            if (!$EmployeeLeaves) {
                $result['status'] = false;
                $result['message'] = 'Leave record is not available.';
                $result['maternity_leave'] = 'Not available';
                return $result;
            }

            $serviceEligableDate = addMonth($this->date_of_joining, 6); // 6 months
            $today = date('Y-m-d'); // totay

            // Policy 1: Service duration
            if ($today >= $serviceEligableDate) {
                // Policy 3: First and second child
                if ($this->no_of_child < 2) {
                    $result['status'] = true;
                    $result['message'] = 'Leave is available.';
                    $result['maternity_leave'] = $EmployeeLeaves->maternity_leave;
                } else {
                    $result['status'] = false;
                    $result['message'] = 'Leave is available for first two childs only.';
                    $result['maternity_leave'] = 0;
                }
            } else {
                $result['status'] = false;
                $result['message'] = 'Leave is available after 6 months from the date of joining';
                $result['maternity_leave'] = $EmployeeLeaves->maternity_leave;
            }

            return $result;
        } else {
            $result['status'] = false;
            $result['message'] = 'This leave is not applicable.';
            $result['maternity_leave'] = 'Not Applicable';
            return $result;
        }
    }

    public function paternity_leave_ploicy()
    {
        // paternity leave ploicy
        // 1. Granted only to male employees who have completed a minimum of 6 months of service in the company
        // 2. They may avail of paternity Leave for a maximum of 3 weeks
        // 3. In the larger interest of the nation, this can be availed for the first and second child.
        // Policy 1: Gender
        if ($this->gender == 'Male') {
            $result = ['status' => ''];

            if ($this->marital_status != 'Married') {
                $result['status'] = false;
                $result['message'] = 'This leave is not applicable Unmarried.';
                $result['paternity_leave'] = 'Not Applicable';
                return $result;
            }

            $EmployeeLeaves = $this->EmployeeLeaves;
            if (!$EmployeeLeaves) {
                $result['status'] = false;
                $result['message'] = 'Leave record is not available.';
                $result['paternity_leave'] = 'Not available';
                return $result;
            }

            $serviceEligableDate = addMonth($this->date_of_joining, 6); // 6 months
            $today = date('Y-m-d'); // totay

            // Policy 1: Service duration
            if ($today >= $serviceEligableDate) {
                // Policy 3: First and second child
                if ($this->no_of_child < 2) {
                    $result['status'] = true;
                    $result['message'] = 'Leave is available.';
                    $result['paternity_leave'] = $EmployeeLeaves->paternity_leave;
                } else {
                    $result['status'] = false;
                    $result['message'] = 'Leave is available for first two childs only.';
                    $result['paternity_leave'] = 0;
                }
            } else {
                $result['status'] = false;
                $result['message'] = 'Leave is available after 6 months from the date of joining';
                $result['paternity_leave'] = $EmployeeLeaves->paternity_leave;
            }

            return $result;
        } else {
            $result['status'] = false;
            $result['message'] = 'This leave is not applicable.';
            $result['paternity_leave'] = 'Not Applicable';
            return $result;
        }
    }

    public function fullname()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function detailname()
    {
        $role = $this->userName->role->role_name ?? '';
        $branch = $this->branch->branch_name ?? '';
        $fullDetail = $this->finger_id;
        $fullDetail .= $role ? ' - ' . $role : '';
        $fullDetail .= $branch ? ', ' . $branch : '';
        return ucwords(strtolower($this->fullname() . ' (' . $fullDetail . ')'));
    }

    public function displayName()
    {
        $branch = $this->branch->branch_name ?? '';
        $fullDetail = $this->finger_id;
        $fullDetail .= $branch ? ', ' . $branch : '';
        return ucwords(strtolower($this->fullname() . ' (' . $fullDetail . ')'));
    }

    public function displayNameWithCode()
    {
        $fullDetail = $this->finger_id;
        return ucwords(strtolower($this->fullname() . ' ( Employee Code: ' . $fullDetail . ' )'));
    }

    public function detail_name()
    {
        return ucwords(strtolower($this->fullname() . ' (' . $this->finger_id  . ($this->branch ? (' - ' . $this->branch->branch_name) : '') . ')'));
    }

    public function rolename()
    {
        return $this->userName->role->role_name ?? '-';
    }

    public function leaveTransactions()
    {
        $calanderYear = calanderYear::currentYear();
        $max_rh = Common::MAX_ALLOWED_RESTRICTED_HOLIDAY;
        $use_rh = RhApplication::where('year_id', $calanderYear->year_id)->where('status', 2)->where('employee_id', $this->employee_id)->count();
        $rh_balance = $max_rh - $use_rh;
        $EmployeeLeaves = $this->EmployeeLeaves;
        $result = [];
        $total = ['casual_leave' => 0, 'privilege_leave' => 0, 'sick_leave' => 0, 'OD' => 0, 'maternity_leave' => 0, 'paternity_leave' => 0,];
        $used = ['casual_leave' => 0, 'privilege_leave' => 0, 'sick_leave' => 0, 'OD' => 0, 'maternity_leave' => 0, 'paternity_leave' => 0,];

        // leave_type_id	    leave_type_name
        // 1	                Casual Leave
        // 2	                Sick Leave
        // 3	                Privilege Leave
        // 4	                On Duty
        // 5	                Maternity Leave
        // 6	                Paternity Leave

        if ($EmployeeLeaves) {
            $balance = [
                'casual_leave'      => $EmployeeLeaves->casual_leave,
                'privilege_leave'   => $EmployeeLeaves->privilege_leave,
                'sick_leave'        => $EmployeeLeaves->sick_leave,
                'OD'                => $EmployeeLeaves->OD,
                'maternity_leave'   => $EmployeeLeaves->maternity_leave,
                'paternity_leave'   => $EmployeeLeaves->paternity_leave,
            ];

            $use_casual_leave       = LeaveApplication::where(['calendar_year' => $calanderYear->year_id, 'status' => 2, 'leave_type_id' => 1, 'employee_id' => $this->employee_id])->count();
            $use_sick_leave         = LeaveApplication::where('calendar_year', $calanderYear->year_id)->where('status', 2)->where('leave_type_id', 2)->where('employee_id', $this->employee_id)->count();
            $use_privilege_leave    = LeaveApplication::where('calendar_year', $calanderYear->year_id)->where('status', 2)->where('leave_type_id', 3)->where('employee_id', $this->employee_id)->count();
            $use_OD                 = LeaveApplication::where('calendar_year', $calanderYear->year_id)->where('status', 2)->where('leave_type_id', 4)->where('employee_id', $this->employee_id)->count();
            $use_maternity_leave    = LeaveApplication::where('calendar_year', $calanderYear->year_id)->where('status', 2)->where('leave_type_id', 5)->where('employee_id', $this->employee_id)->count();
            $use_paternity_leave    = LeaveApplication::where('calendar_year', $calanderYear->year_id)->where('status', 2)->where('leave_type_id', 6)->where('employee_id', $this->employee_id)->count();

            $used = [
                'casual_leave'      => $use_casual_leave,
                'privilege_leave'   => $use_privilege_leave,
                'sick_leave'        => $use_sick_leave,
                'OD'                => $use_OD,
                'maternity_leave'   => $use_maternity_leave,
                'paternity_leave'   => $use_paternity_leave,
            ];
        }
        if ($this->gender == 'Male') {
            unset($balance['maternity_leave']);
            unset($used['maternity_leave']);
        } else if ($this->gender == 'Female') {
            unset($balance['paternity_leave']);
            unset($used['paternity_leave']);
        }
        $result['balance'] = $balance;
        $result['used'] = $used;
        $result['show_total'] = true;
        return $result;
    }

    public function rh_balance()
    {
        $calanderYear = calanderYear::currentYear();
        $max_rh = Common::MAX_ALLOWED_RESTRICTED_HOLIDAY;
        $use_rh = RhApplication::where('year_id', $calanderYear->year_id)->where('status', 2)->where('employee_id', $this->employee_id)->count();
        $rh_balance = $max_rh - $use_rh;
        return $rh_balance;
    }
    public function permission_balance()
    {
        $year = date('Y');
        $month = date('m');
        $max_permission = Common::PERMISSION_LIMIT;
        $use_permission =  LeavePermission::whereMonth('leave_permission_date', '=', $month)->whereYear('leave_permission_date', '=', $year)
            ->where('employee_id', $this->employee_id)
            ->where('status', 2)->count();
        $permission_balance = $max_permission - $use_permission;
        return $permission_balance;
    }

    public static function loggedEmployee()
    {
        $LoggedEmployee = Employee::findOrFail(session('logged_session_data.employee_id'));
        return $LoggedEmployee;
    }

    public function subordinateIds($employee_id = '') // logged user direct supervisor id
    {
        if (!$employee_id) {
            $employee_id = (array) session('logged_session_data.employee_id');
        } elseif (!is_array($employee_id)) {
            $employee_id = (array) $employee_id;
        } else {
            $employee_id = [];
        }
        $subordinate = [];

        for ($i = 1; $i <= 1; $i++) {
            $temp = DB::table('employee')->select('employee.employee_id')->whereIn('supervisor_id', $employee_id)->where('employee_id', '!=', session('logged_session_data.employee_id'))
                ->join('user', 'user.user_id', 'employee.user_id')
                ->where('user.role_id', '<=', 3) // not employee level (logged user's admin, HR role)
                ->get();
            foreach ($temp as $key => $row) {
                $subordinate[] = $row->employee_id;
                $employee_id[] = $row->employee_id;
            }
        }
        $supervisorIds = array_unique($subordinate);
        return $supervisorIds;
    }

    public function supervisorIds($employee_id = '') // two level downline supervisor id
    {
        if (!$employee_id) {
            $employee_id = (array) session('logged_session_data.employee_id');
        } elseif (!is_array($employee_id)) {
            $employee_id = (array) $employee_id;
        } else {
            $employee_id = [];
        }
        if ($this->userName->role_id <= 3) {
            $this->supervisorIds[] = current($employee_id);
        }

        for ($i = 1; $i <= $this->maxDepth; $i++) {
            $temp = DB::table('employee')->select('employee_id')->whereIn('supervisor_id', $employee_id)->orWhere('functional_head_id', $employee_id)->get();
            foreach ($temp as $key => $row) {
                $this->supervisorIds[] = $row->employee_id;
                $employee_id[] = $row->employee_id;
            }
        }
        $supervisorIds = array_unique($this->supervisorIds);
        return $supervisorIds;
    }

    public function SalaryRevisions()
    {
        return $this->hasMany('App\Model\SalaryRevisions', 'sal_employee_id', 'employee_id')->orderBy('sal_reviside_on', 'desc');
    }

    // protected function order_date(): Illuminate\Database\Eloquent\Casts\Attribute
    // {
    //     return new Attribute(
    //         fn ($value) => Carbon::parse($value)->format('d-m-Y'), // accessor
    //         fn ($value) => Carbon::parse($value)->format('Y-m-d'), // mutator
    //     );
    // }

}
