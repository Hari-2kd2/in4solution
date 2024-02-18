<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Components\Common;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class RhApplication extends Model
{
    // use BranchTrait;
    use CrudTrait;
  
    protected $table = 'restricted_holiday_application';
    protected $primaryKey = 'rh_application_id';
    protected $fillable = [
      'employee_id', 'branch_id', 'holiday_id', 'year_id', 'holiday_date', 'application_date', 'approve_date', 'approve_by', 'reject_date', 'reject_by', 'purpose', 'remarks', 'status'
    ];

    public function getTaken() {
      // get already approved count (approved or aut canceled record already approved)
      $getTaken = RhApplication::whereRaw("(status=2 OR status=4) AND employee_id='$this->employee_id' AND branch_id='$this->branch_id' AND year_id='$this->year_id'")->count();
      return $getTaken;
    }

    public function getDetail() {
      $detail = [];
      if($this->rh_application_id) {
        $detail['max_allowed_restricted_holiday'] = Common::MAX_ALLOWED_RESTRICTED_HOLIDAY;
        $detail['approved_restricted_holiday'] = self::where('status', 2)->where('employee_id', $this->employee_id)->where('year_id', $this->year_id)->where('branch_id', $this->branch_id)->count();
        $detail['rejected_restricted_holiday'] = self::where('status', 3)->where('employee_id', $this->employee_id)->where('year_id', $this->year_id)->where('branch_id', $this->branch_id)->count();
        $detail['canceled_restricted_holiday'] = self::where('status', 4)->where('employee_id', $this->employee_id)->where('year_id', $this->year_id)->where('branch_id', $this->branch_id)->count();
      }
      return $detail;
    }
    
    public function employee()
    {
      return $this->belongsTo(Employee::class, 'employee_id')->withDefault(
        [
          'employee_id' => 0,
          'user_id' => 0,
          'department_id' => 0,
          'email' => 'unknown email',
          'first_name' => 'unknown',
          'last_name' => 'unknown last name'
  
        ]
      );
    }
  
    public function approveBy()
    {
      return $this->belongsTo(Employee::class, 'approve_by', 'employee_id')->withDefault(
        [
          'employee_id' => 0,
          'user_id' => 0,
          'department_id' => 0,
          'email' => 'unknown email',
          'first_name' => 'unknown',
          'last_name' => 'unknown last name'
  
        ]
      );
    }
  
    public function rejectBy()
    {
      return $this->belongsTo(Employee::class, 'reject_by', 'employee_id')->withDefault(
        [
          'employee_id' => 0,
          'user_id' => 0,
          'department_id' => 0,
          'email' => 'unknown email',
          'first_name' => 'unknown',
          'last_name' => 'unknown last name'
  
        ]
      );
    }
  
    public function RestrictedHoliday()
    {
      return $this->belongsTo(RestrictedHoliday::class, 'holiday_id', 'holiday_id')->withDefault(
        [
          'holiday_id' => 0,
        ]
      );
    }
  
    public function calanderYear()
    {
      return $this->belongsTo(calanderYear::class, 'year_id', 'year_id')->withDefault(
        [
          'year_id' => 0,
        ]
      );
    }
  
    public function reviewedBy() {
      if($this->rh_application_id) {
        if($this->status==1) {
          return '-';
        } else if($this->status==2 && $this->approveBy) {
          return $this->approveBy->first_name .' ' . $this->approveBy->last_name;
        } else if($this->status==3 && $this->rejectBy) {
          return $this->rejectBy->first_name .' ' . $this->rejectBy->last_name;
        } else if($this->status==4) {
          return 'Policy';
        }
      }
    }
}
