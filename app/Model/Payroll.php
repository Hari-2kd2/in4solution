<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $table = 'payroll';
    protected $primaryKey = 'payroll_id';

    public function employee(){
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function departmentinfo()
    {
        return $this->belongsTo(Department::class, 'department');
    }

    public function branch(){
        return $this->belongsTo(Branch::class, 'branch');
    }

    public function employeeinfo(){
        return $this->belongsTo(Employee::class, 'employee');
    }


    public function designatioinfo()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    protected $fillable = [
       "employee","yearly_ctc","month","year","days_in_month","sundays","holidays","cl","sl","el","lop","working_days","worked_days",
       "month_salary","day_salary","hour_salary","worked_salary","basic","da","hra","conveyance","medical","children","lta","special","other",
       "income_tax","professional_tax","wages_earnings","deduction","net_amount","basic_percentage","hra_percentage","da_percentage",
       "finger_print_id","department","full_basic","full_da","full_hra","full_conveyance","full_medical","full_children","full_lta","full_special",
       "full_other","full_wages_earnings",'lop_amount','absent','ot_amount','ot_hour','advance_deduction','pf_amount','bank_name','bank_branch','bank_account_no',
       'bank_of_the_city','date','fdate','tdate','branch','no_day_wages','company_holiday','total_days','per_day_basic_da','per_day_basic','per_day_da',
       'per_day_hra','per_day_wages','basic_da_amount','employee_pf','employee_pf_percentage',
       'employee_esic','employee_esic_percentage','employer_pf','employer_pf_percentage','employer_esic',
       'employer_esic_percentage','earned_leave_balance','earned_leave','leave_amount',
       'manhours','manhours_amount','manhour_days','salary','basic_da_percentage',
       'employee_total_deduction','employer_total_deduction','other_allowance',
       'other_deduction'
    ];
    /*public function scopeStatus($query,$status){
        return $query->where('status',$status);
    }

    public function scopeFilter($query,$request){
        return $query->where('department_id',$request['department_id'])->where('branch_id',$request['branch_id'])->where('employee_id',$request['employee_id']);
    }

    public function userName()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function costcenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function subdepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function payGrade()
    {
        return $this->belongsTo(PayGrade::class, 'pay_grade_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
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
    }*/

    // public function scopeFilter($query, $request)
    // {
    //     return $query->where('employee_id', $request['employee_id'])->where('department', $request['department'])->where('finger_id', $request['finger_id']);
    // }
    // public function scopeStatus($query, $status)
    // {
    //     return $query->where('status', $status);
    // }
}
