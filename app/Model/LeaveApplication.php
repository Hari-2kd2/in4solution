<?php

namespace App\Model;

use App\Traits\CrudTrait;
// use App\Traits\BranchTrait;
use App\Model\EmployeeLeaves;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\LeaveRepository;
use App\Model\LeaveCreditTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    // use BranchTrait;
    // use CrudTrait;

    protected $table = 'leave_application';
    protected $primaryKey = 'leave_application_id';

    protected $fillable = [
        'leave_application_id', 'branch_id', 'employee_id', 'leave_type_id', 'application_from_date', 'application_to_date', 'application_date', 'calendar_year',
        'number_of_day', 'approve_date', 'approve_by', 'reject_date', 'reject_by', 'purpose', 'remarks', 'status', 'medical_file', 'functional_head_status', 'functional_head_approved_by', 'functional_head_reject_by',
        'functional_head_approve_date', 'functional_head_reject_date', 'pass_date', 'pass_by',  'functional_head_remark'
    ];

    public function leaveStatus()
    {
        $leaveStatus = [
            LeaveStatus::$PENDING => __('common.pending'),
            LeaveStatus::$APPROVE => __('common.approved'),
            LeaveStatus::$REJECT => __('common.rejected'),
            LeaveStatus::$REJECT => __('common.passed'),
            LeaveStatus::$CANCEL => __('common.canceled'),
        ];
        return isset($leaveStatus[$this->status]) ? $leaveStatus[$this->status] : '';
    }

    public function leaveClass()
    {
        $leaveClass = [
            LeaveStatus::$PENDING => 'warning',
            LeaveStatus::$APPROVE => 'success',
            LeaveStatus::$REJECT => 'danger',
            LeaveStatus::$CANCEL => 'info',
            LeaveStatus::$PASSED => 'info',
        ];
        return isset($leaveClass[$this->status]) ? $leaveClass[$this->status] : '';
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

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id')->withDefault(
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

    public function updateLeaves()
    {

        if ($this->leave_application_id && $this->status == 2 && $this->employee && $this->employee->EmployeeLeaves) {
            if ($EmployeeLeaves = $this->employee->EmployeeLeaves) {
                $UsedTransactionFlag = false;
                if ($this->leave_type_id == 1) {
                    $EmployeeLeaves->casual_leave =  $EmployeeLeaves->casual_leave - $this->number_of_day;
                    $UsedTransactionFlag = true;
                } elseif ($this->leave_type_id == 2) {
                    $EmployeeLeaves->sick_leave = $EmployeeLeaves->sick_leave - $this->number_of_day;
                    $UsedTransactionFlag = true;
                } elseif ($this->leave_type_id == 3) {
                    $EmployeeLeaves->privilege_leave = $EmployeeLeaves->privilege_leave - $this->number_of_day;
                    $UsedTransactionFlag = true;
                } elseif ($this->leave_type_id == 5 || $this->leave_type_id == 6) {
                    $EmployeeLeaves->employee->no_of_child++;
                    $EmployeeLeaves->employee->update();
                } elseif ($this->leave_type_id == 7) {
                    for ($day = 0.5; $day <= $this->number_of_day; $day += 0.5) {
                        $CompOff = CompOff::where('employee_id', $this->employee_id)->whereIn('status', [0, 1])->first(); // 0 mean un-used used_days
                        if ($CompOff) {
                            $CompOff->used_days += 0.5;
                            if ($CompOff->used_days == 0.5) {
                                $CompOff->status = 1; // half used
                            } else if ($CompOff->used_days == 1) {
                                $CompOff->status = 2; // full used
                            }
                            if ($CompOff->used_days == $CompOff->off_days) {
                                $CompOff->status = 2; // full used
                            }
                            $CompOff->update();
                        }
                    }
                    $EmployeeLeaves->comp_off = $EmployeeLeaves->comp_off - $this->number_of_day;
                    $UsedTransactionFlag = true;
                } // end of all type leave
                if ($UsedTransactionFlag) {
                    $EmployeeLeaves->update();
                    $LeaveCreditTransactions = new LeaveCreditTransactions;
                    $LeaveCreditTransactions->employee_id               = $this->employee_id;
                    $LeaveCreditTransactions->branch_id                 = $this->branch_id;
                    $LeaveCreditTransactions->trn_credit_on             = $this->approve_date; // used=0, trn_credit_on is consider trn_used_on no extra field added
                    $LeaveCreditTransactions->trn_credit_days           = $this->number_of_day; // trn_credit_days is consider trn_used_days no extra field added
                    $LeaveCreditTransactions->trn_leave_type_id         = $this->leave_type_id;
                    $LeaveCreditTransactions->trn_type                  = 0; // used
                    $LeaveCreditTransactions->trn_leave_application_id  = $this->leave_application_id;
                    $LeaveCreditTransactions->year_id                   = $this->calendar_year;
                    $LeaveCreditTransactions->trn_remark                = 'Used transactions entry on approved by admin';
                    $LeaveCreditTransactions->created_at                = $this->updated_at;
                    $LeaveCreditTransactions->updated_at                = $this->updated_at;
                    $LeaveCreditTransactions->save();
                }
            }
        } else {
        }
    }

    public function checkBeforeAfterDates($FROM_DATE, $TO_DATE)
    {
        // check holiday before after if holiday than move next previous date
        previous_day:
        $FROM_DATE = operateDays($FROM_DATE, 1, '-');
        $beforeHoliday = DB::table('holiday_details')->where('from_date', '>=', $FROM_DATE)->where('to_date', '<=', $FROM_DATE)->first();
        $BeforeDay = date('l', strtotime($FROM_DATE));

        if ($beforeHoliday) {
            goto previous_day;
        } else if ($BeforeDay == Config::get('leave.weekly_holiday')) {
            goto previous_day;
        }

        next_day:
        $TO_DATE = operateDays($TO_DATE, 1);
        $afterHoliday = DB::table('holiday_details')->where('from_date', '>=', $TO_DATE)->where('to_date', '<=', $TO_DATE)->first();
        $afterDay = date('l', strtotime($TO_DATE));
        if ($afterHoliday) {
            goto next_day;
        } else if ($afterDay == Config::get('leave.weekly_holiday')) {
            goto next_day;
        }

        return ['PREV_DATE' => $FROM_DATE, 'NEXT_DATE' => $TO_DATE];
    }

    public function CasualLeaveChecks($employee, $request)
    {
        if (!$request->leave_type_id == 1) {
            return;
        }

        $LeaveType = LeaveType::find($request->leave_type_id);
        $employee_id  = $employee->employee_id;

        $api_call = $employee->api_call;

        if (!$api_call) {
            $from_date = dateConvertFormtoDB($request->application_from_date);
            $to_date = dateConvertFormtoDB($request->application_to_date);
        } else {
            $from_date = $request->application_from_date;
            $to_date = $request->application_to_date;
        }



        // if CL type leave check before after date taken any type should not allow 
        $DATES = $this->checkBeforeAfterDates($from_date, $to_date);
        // expect "Sick Leave" and "Privilege Leave" can not be near by before and after should not allow
        if (isset($DATES['PREV_DATE']) && isset($DATES['NEXT_DATE'])) {
            $PREV_DATE = $DATES['PREV_DATE'];
            $NEXT_DATE = $DATES['NEXT_DATE'];
            $BeforeRH = RhApplicationCase::where('employee_id', $employee_id)->whereRaw("holiday_date='$PREV_DATE'")->where('status', '<=', LeaveStatus::$APPROVE)->first();
            $AfterRH = RhApplicationCase::where('employee_id', $employee_id)->whereRaw("holiday_date='$NEXT_DATE'")->where('status', '<=', LeaveStatus::$APPROVE)->first();
            $BeforeLeave = LeaveApplicationCase::where('application_from_date', $PREV_DATE)->where('employee_id', $employee_id)->where('status', '<=', LeaveStatus::$APPROVE)->first();
            $AfterLeave = LeaveApplicationCase::where('application_to_date', $NEXT_DATE)->where('employee_id', $employee_id)->where('status', '<=', LeaveStatus::$APPROVE)->first();
            $BetweenLeave = LeaveApplicationCase::whereRaw("(application_from_date BETWEEN '$PREV_DATE' AND '$NEXT_DATE' OR application_to_date BETWEEN '$PREV_DATE' AND '$NEXT_DATE')")->where('employee_id', $employee_id)->where('status', '<=', LeaveStatus::$APPROVE)->first();

            $messageIs = '';
            if ($BeforeRH) {
                $statusLable = $BeforeRH->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
                $messageIs = 'Already ' . $statusLable . ' RH before the from(' . dateConvertDBtoForm($from_date) . ') date (' . dateConvertDBtoForm($PREV_DATE) . ')';
            } else if ($AfterRH) {
                $statusLable = $AfterRH->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
                $messageIs = 'Already ' . $statusLable . ' RH after the to(' . dateConvertDBtoForm($to_date) . ') date (' . dateConvertDBtoForm($NEXT_DATE) . ')';
            } else if ($BeforeLeave) {
                $statusLable = $BeforeLeave->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
                $messageIs = 'Already ' . $statusLable . ' ' . ($BeforeLeave->leaveType->leave_type_name ?? 'leave') . ' before the from(' . dateConvertDBtoForm($from_date) . ') date (' . dateConvertDBtoForm($PREV_DATE) . ')';
            } else if ($AfterLeave) {
                $statusLable = $AfterLeave->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
                $messageIs = 'Already ' . $statusLable . ' ' . ($AfterLeave->leaveType->leave_type_name ?? 'leave') . ' after the to(' . dateConvertDBtoForm($to_date) . ') date (' . dateConvertDBtoForm($NEXT_DATE) . ')';
            } else if ($BetweenLeave) {
                $statusLable = $BetweenLeave->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
                $messageIs = 'Already ' . $statusLable . ' ' . ($BetweenLeave->leaveType->leave_type_name ?? 'leave') . ' near from OR to(' . dateConvertDBtoForm($PREV_DATE) . ', ' . dateConvertDBtoForm($NEXT_DATE) . ')';
            }

            if ($messageIs) {
                $messageIs = $LeaveType->leave_type_name . ' cannot be combined with other kinds of leave. ' . $messageIs;
                return $messageIs;
            }
        }
    }

    public function PrivilegeLeaveChecks($employee, $request)
    {
        $currentYear = calanderYear::currentYear();
        $PL_APPROVED_APPLY_COUNT = LeaveApplication::where('calendar_year', $currentYear->year_id)->where('employee_id', $employee->employee_id)->where('leave_type_id', $request->leave_type_id)->where('status', '<=', LeaveStatus::$APPROVE)->count();
        if ($request->leave_type_id == 3) {
            $messageIs = '';
            if ($request->number_of_day < LeaveRepository::MIN_PL_APPLY_DAYS) {
                $messageIs = 'Please select minimum ' . (LeaveRepository::MIN_PL_APPLY_DAYS) . ' working days for PL';
                return $messageIs;
            }

            $plAskBefore = plAskBefore();
            $application_from_date = dateConvertFormtoDB($request->application_from_date);

            if ($application_from_date < $plAskBefore) {
                $messageIs = 'You should be apply PL from the ' . dateConvertDBtoForm($plAskBefore);
            } else if ($PL_APPROVED_APPLY_COUNT >= LeaveRepository::PRIVILEGE_LIMIT_PER_YEAR) {
                $PL_PENDING_APPLY_COUNT = LeaveApplication::where('calendar_year', $currentYear->year_id)->where('employee_id', $employee->employee_id)->where('leave_type_id', $request->leave_type_id)->where('status', LeaveStatus::$PENDING)->count();
                $LeaveType = LeaveType::find($request->leave_type_id);
                $messageIs = ($LeaveType->leave_type_name . ' can not be apply for more than ' . LeaveRepository::PRIVILEGE_LIMIT_PER_YEAR . ' times in a year.');
                $messageIs .= ($PL_PENDING_APPLY_COUNT > 0 ? " ($PL_PENDING_APPLY_COUNT Application in pending)" : '');
            }
            return $messageIs;
        }
    }

    public function OtherChecks($employee, $request)
    {
        $api_call = $employee->api_call;
        $past_date = dateConvertFormtoDB(Config('leave.past_date')); // no of past day allow date
        if (!$api_call) {
            $from_date = dateConvertFormtoDB($request->application_from_date);
            $to_date = dateConvertFormtoDB($request->application_to_date);
        } else {
            $from_date = $request->application_from_date;
            $to_date = $request->application_to_date;
        }

        if ($from_date < $past_date) {
            return $messageIs = ('From Date should be greater than or equal to ' . dateConvertDBtoForm($past_date));
        }
        if ($request->leave_type_id == 2 && $request->number_of_day > 2 && !$request->mfile) {
            return $messageIs = 'Please attach Medical Certificate if more than 2 days leaves.';
        }
        if ($request->leave_type_id != 5 && $request->leave_type_id != 6) {
        }

        $employee_id  = $employee->employee_id;
        $leaveBalanceCheckType = ['casual_leave' => 1, 'sick_leave' => 2, 'privilege_leave' => 3, 'comp_off' => 7];
        $leaveBalanceCheckLabel = ['casual_leave' => 'Casual Leave', 'sick_leave' => 'Sick Leave', 'privilege_leave' => 'Privilege Leave', 'comp_off' => 'Comp Off'];
        $checkStatus = LeaveStatus::$APPROVE;
        $whereRaw =
            "
        (((application_from_date='$from_date' OR application_to_date='$from_date') AND status<='$checkStatus' AND employee_id='$employee_id') OR (application_from_date>='$from_date' AND application_to_date<='$from_date' AND status<='$checkStatus' AND employee_id='$employee_id'))
        OR
        (((application_from_date='$to_date' OR application_to_date='$to_date') AND status<='$checkStatus' AND employee_id='$employee_id') OR (application_from_date>='$to_date' AND application_to_date<='$to_date' AND status<='$checkStatus' AND employee_id='$employee_id'))
       ";
        $EmployeeLeaves = $employee->EmployeeLeaves ?? new EmployeeLeaves;
        $LeaveType = LeaveType::find($request->leave_type_id) ?? new LeaveType;
        $currentYear = calanderYear::currentYear();

        $sameDateApply = LeaveApplication::whereRaw($whereRaw)->where('employee_id', $employee_id)->where('status', '<=', LeaveStatus::$APPROVE)->first();
        $sameDateRH = RhApplicationCase::where('employee_id', $employee_id)->whereRaw("holiday_date >= '$from_date'")->whereRaw("holiday_date <='$to_date'")->where('status', '<=', LeaveStatus::$APPROVE)->first();

        $pending_number_of_day = LeaveApplication::where('employee_id', $employee_id)->where('leave_type_id', $request->leave_type_id)->where('status', LeaveStatus::$PENDING)->sum('number_of_day');
        if ($type_field = array_search($request->leave_type_id, $leaveBalanceCheckType)) { // 1=Casual Leave, 2=Sick Leave, 3=Privilege Leave, 7=Comp Off
            // if balance insufficient checks
            $total = $request->number_of_day + $pending_number_of_day;
            if (isset($EmployeeLeaves->$type_field) && $request->number_of_day > $EmployeeLeaves->$type_field) {
                $balance = $EmployeeLeaves->$type_field;
                $leaveName = $leaveBalanceCheckLabel[$type_field] ?? '';
                $messageIs = 'You have ' . $balance . ' ' . $leaveName . ' balance only, Can not apply ' . $request->number_of_day . ' day(s).';
                return $messageIs;
            } elseif (isset($EmployeeLeaves->$type_field) && $total > $EmployeeLeaves->$type_field) {
                $balance = $EmployeeLeaves->$type_field;
                $leaveName = $leaveBalanceCheckLabel[$type_field] ?? '';
                $messageIs = 'You have ' . $balance . ' ' . $leaveName . ' balance only (Pending ' . $pending_number_of_day . '), Can not apply ' . $request->number_of_day . ' day(s).';
                return $messageIs;
            }
        }

        if ($sameDateApply) {
            $statusLable = $sameDateApply->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
            $messageIs = 'The selected date range (' . $request->application_from_date . ' - ' . $request->application_to_date . ') may already ' . $statusLable;
            return $messageIs;
        } else if ($sameDateRH) {
            $statusLable = $sameDateRH->status == LeaveStatus::$APPROVE ? 'taken' : 'applied';
            $messageIs = 'The selected date range (' . $request->application_from_date . ' - ' . $request->application_to_date . ') RH may already ' . $statusLable;
            return $messageIs;
        }
    }

    public function isCancel()
    {
        // 1=Pending or 2=Approved leave only can be cancel
        // If application status in 1=Pending need not to revert credit transaction
        // If application status in 2=Approved should be need to revert credit transaction
        // Leave can be cancel till the leave end of the date application_to_date
        // Employee's API / Web Leave Applied List can use this to send cancle flag
        $TODAY = date('Y-m-d');
        if ($this->leave_application_id && $TODAY <= $this->application_to_date  && ($this->status == LeaveStatus::$PENDING || $this->status == LeaveStatus::$APPROVE)) {
            // 1=Casual Leave, 2=Sick Leave, 3=Privilege, 4=On Duty Leave can be cancel
            if ($this->leave_type_id == 1 || $this->leave_type_id == 2 || $this->leave_type_id == 3 || $this->leave_type_id == 4) {
                return true;
            }
        }
    }

    public function webCancelBtn()
    {
        if ($this->isCancel()) {
            $dateLabel = '';
            if ($this->application_to_date == $this->application_from_date) {
                $dateLabel = dateConvertDBtoForm($this->application_from_date);
            } else {
                $dateLabel = dateConvertDBtoForm($this->application_from_date) . ' to ' . dateConvertDBtoForm($this->application_to_date);
            }
            return '<a href="javascript:;" data-prompt="' . \addslashes($dateLabel) . '" data-url="' . Route('applyForLeave.cancel', ['id' => $this->leave_application_id]) . '" title="Cancel" class="btn btn-xs btn-danger cancel-leave" data-id="' . $this->leave_application_id . '"><i
            class="fa fa-close"></i></a>';
        }
    }

    // can call both web and API
    public function leaveCancelTransaction()
    {
        // check once leave can be in cancel mode status and date bar condition
        if ($this->isCancel()) {
            DB::beginTransaction();
            // allplication change the status 
            $oldStatus = $this->status;
            $this->status = LeaveStatus::$CANCEL;
            $this->functional_head_status = LeaveStatus::$CANCEL;
            $result = $this->update();
            DB::commit();
            return true;
        }
    }
}
