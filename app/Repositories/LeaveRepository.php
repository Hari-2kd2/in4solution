<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use App\Model\LeaveBalance;
use App\Model\EarnLeaveRule;
use App\Model\PaidLeaveRule;
use App\Model\EmployeeLeaves;
use App\Model\HolidayDetails;
use App\Model\LeaveApplication;
use Illuminate\Support\Facades\DB;
use App\Model\LeaveApplicationCase;
use Illuminate\Support\Facades\Log;
use App\Lib\Enumerations\LeaveStatus;

class LeaveRepository
{
    public $statusList = [
        1 => 'Pending',
        2 => 'Approve',
        3 => 'Reject',
        4 => 'AutoCancel',
    ];
    public $processList = [
        1 => 'Pending',
        2 => 'Approved',
        3 => 'Rejected',
        4 => 'Cancelled',
    ];
    const maternity_leave_months = 6; // refer given leave policy document
    const PRIVILEGE_LIMIT_PER_YEAR = 4; // refer given leave policy document
    const FILE_SIZE = 5242880; // upload certificate file size in bytes (5MB)
    const FILE_TYPE = ['.jpg', '.jpeg', '.png', '.pdf', '.doc', '.docx']; // upload certificate file types in array
    const SHOULD_MIN_PL = 15; // minimum PL should be keep employee leave balnce, mean more than 15 days only can use encahment
    const BEFORE_DAYS = 20; // PL leave ask before days
    const MIN_PL_APPLY_DAYS = 3; // PL leave should minimum days

    public function calculateTotalNumberOfLeaveDays($application_from_date, $application_to_date)
    {
        $holidays =  DB::table('holiday_details')->get();
        $leave_count = 0;
        $govt_holidays = [];
        foreach ($holidays as $holiday) {
            $start_date = $holiday->from_date;
            $end_date = $holiday->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        $leave_days = [];
        $format = 'Y-m-d';
        $current = strtotime($application_from_date);
        $date2 = strtotime($application_to_date);
        $stepVal = '+1 day';
        while ($current <= $date2) {
            if (!in_array(date('Y-m-d', $current), $govt_holidays)) {
                $dt1 = strtotime(date('Y-m-d', $current));
                $dt2 = date("l", $dt1);
                $dt3 = strtolower($dt2);
                if ($dt3 != "sunday") {
                    $leave_days[] = date($format, $current);
                }
            }
            $current = strtotime($stepVal, $current);
        }

        $leave_count = count($leave_days);
        return $leave_count;
    }

    public function calculateEmployeeLeaveBalanceArray($leave_type_id, $employee_id)
    {
        $leaveTypes = LeaveType::get();
        $leaveArr = [];
        foreach ($leaveTypes as $key => $ltype) {

            $leaveArr[$key]['leaveType'] = $ltype->leave_type_name;
            $leavetaken = DB::select(DB::raw('call SP_calculateEmployeeLeaveBalance(' . $employee_id . ',' . $ltype->leave_type_id . ')'));
            $leaveBalance = EmployeeLeaves::where('employee_id', $employee_id)->first();
            // dd($leavetaken);
            $leaveArr[$key]['leaveTaken'] = $leavetaken[0]->totalNumberOfDays;

            if ($ltype->leave_type_id == 1) {
                if (isset($leaveBalance->casual_leave)) {
                    $leaveArr[$key]['leaveBalance'] = $leaveBalance->casual_leave;
                } else {
                    $leaveArr[$key]['leaveBalance'] = 0;
                }
                $leaveArr[$key]['totalDays'] = $leaveArr[$key]['leaveBalance'] +  $leaveArr[$key]['leaveTaken'];
            } elseif ($ltype->leave_type_id == 2) {
                if (isset($leaveBalance->sick_leave)) {
                    $leaveArr[$key]['leaveBalance'] = $leaveBalance->sick_leave;
                } else {
                    $leaveArr[$key]['leaveBalance'] = 0;
                }
                $leaveArr[$key]['totalDays'] = $leaveArr[$key]['leaveBalance'] +  $leaveArr[$key]['leaveTaken'];
            } elseif ($ltype->leave_type_id == 3) {
                if (isset($leaveBalance->privilege_leave)) {
                    $leaveArr[$key]['leaveBalance'] = $leaveBalance->privilege_leave;
                } else {
                    $leaveArr[$key]['leaveBalance'] = 0;
                }
                $leaveArr[$key]['totalDays'] = $leaveArr[$key]['leaveBalance'] +  $leaveArr[$key]['leaveTaken'];
            } elseif ($ltype->leave_type_id == 4) {
                if (isset($leaveBalance->OD)) {
                    $leaveArr[$key]['leaveBalance'] = '';
                } else {
                    $leaveArr[$key]['leaveBalance'] = '';
                }
                $leaveArr[$key]['totalDays'] = '';
            } else {
                $leaveArr[$key]['leaveBalance'] = 0;
            }
        }

        return $leaveArr;

        // $leaveTypes = LeaveType::get();
        // $leaveArr = [];
        // foreach ($leaveTypes as $key => $ltype) {

        //     $leaveArr[$key]['leaveType'] = $ltype->leave_type_name;
        //     $leaveBalance = DB::select(DB::raw('call SP_calculateEmployeeLeaveBalance(' . $employee_id . ',' . $ltype->leave_type_id . ')'));
        //     $leaveArr[$key]['totalDays'] = $ltype->num_of_day;
        //     $leaveArr[$key]['leaveTaken'] = $leaveBalance[0]->totalNumberOfDays;
        //     $leaveArr[$key]['leaveBalance'] = $ltype->num_of_day - $leaveBalance[0]->totalNumberOfDays;
        // }

        // return $leaveArr;
    }


    public function calculateEmployeeLeaveAvailability($leaveTypeId, $employeeId)
    {
        $currentYear = calanderYear::where('year_status', 0)->first();
        $employeeLeaveData = EmployeeLeaves::where('employee_id', $employeeId)->first();
        $balance = 0;
        if ($employeeLeaveData) {
            if ($leaveTypeId == 1) {
                $balance = $employeeLeaveData->casual_leave;
            } elseif ($leaveTypeId == 2) {
                $balance = $employeeLeaveData->sick_leave;
            } elseif ($leaveTypeId == 3) {
                $balance = $employeeLeaveData->privilege_leave;
            } elseif ($leaveTypeId == 4) {
                $balance = $employeeLeaveData->OD;
            } elseif ($leaveTypeId == 5) {
                $balance = $employeeLeaveData->maternity_leave;
            } elseif ($leaveTypeId == 6) {
                $balance = $employeeLeaveData->paternity_leave;
            } elseif ($leaveTypeId == 7) {
                $balance = $employeeLeaveData->comp_off;
            }
        }
        return $balance;
    }

    public function calculateEmployeeLeaveStatus($leave_type_id, $employee_id, $application_from_date, $application_to_date, $number_of_day)
    {
        $employee = Employee::where('employee_id', $employee_id)->first();
        $holidays =  HolidayDetails::where('branch_id', $employee->branch_id)->get();
        $leave_count = 0;
        $govt_holidays = [];
        foreach ($holidays as $holiday) {
            $start_date = $holiday->from_date;
            $end_date = $holiday->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        $leave_days = [];

        if (count($govt_holidays) <= 0) {
            $format = 'd-m-Y';
            $current = strtotime($application_from_date);
            $date2 = strtotime($application_to_date);
            $stepVal = '+1 day';
            while ($current <= $date2) {
                if (!in_array($current, $govt_holidays)) {
                    $dt1 = strtotime($current);
                    $dt2 = date("l", $dt1);
                    $dt3 = strtolower($dt2);
                    if ($dt3 != "sunday") {
                        $leave_days[] = date($format, $current);
                    }
                }
                $current = strtotime($stepVal, $current);
            }

            $leave_count = count($leave_days);
        } else {
            $format = 'd-m-Y';
            $current = strtotime($application_from_date);
            $date2 = strtotime($application_to_date);
            $stepVal = '+1 day';
            while ($current <= $date2) {
                $dt1 = strtotime($current);
                $dt2 = date("l", $dt1);
                $dt3 = strtolower($dt2);
                if ($dt3 != "sunday") {
                    $leave_days[] = date($format, $current);
                }

                $current = strtotime($stepVal, $current);
            }

            $leave_count = count($leave_days);
        }
        $currentYear = calanderYear::where('year_status', 0)->first();
        if ($leave_type_id == 3) {
            $totalLeaveTaken = LeaveApplication::where('employee_id', $employee_id)->where('status', 2)->whereYear('calendar_year',  $currentYear->year_id)->count();
            if ($totalLeaveTaken >= 4) {
                $leave_count = -1;
            }
        }
        return $leave_count;
    }


    public function calculateEmployeeLeaveBalance($leaveTypeId, $employeeId)
    {

        $leaveType = LeaveType::where('leave_type_id', $leaveTypeId)->value('leave_type_name');

        $currentYear = Carbon::now()->year;

        $totalNumberOfDays = LeaveApplication::select(DB::raw('SUM(number_of_day) as totalNumberOfDays'))
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', 2)
            ->whereBetween('approve_date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->value('totalNumberOfDays');

        $query = EmployeeLeaves::where('employee_id', $employeeId)
            ->where(function ($query) use ($leaveType) {
                $query->where('casual_leave', 'LIKE', "%$leaveType%")
                    ->orWhere('privilege_leave', 'LIKE', "%$leaveType%")
                    ->orWhere('sick_leave', 'LIKE', "%$leaveType%")
                    ->orWhere('maternity_leave', 'LIKE', "%$leaveType%")
                    ->orWhere('paternity_leave', 'LIKE', "%$leaveType%");
            });

        $lastYearBalance = $query->first();


        $leaveTypeNumofDays = LeaveType::where('leave_type_id', $leaveTypeId)
            ->value('num_of_day');

        $remainingBalance = ($leaveTypeNumofDays + $lastYearBalance) - $totalNumberOfDays;


        return $remainingBalance;
    }

    public function getEmployeeTotalLeaveBalancePerYear($leave_type_id, $employee_id)
    {

        $paidleaveType = LeaveType::where('leave_type_id', 2)->sum('num_of_day');
        $paidLeaveRule = PaidLeaveRule::where('day_of_paid_leave')->first();
        $totalLeaveTaken = LeaveApplication::where('employee_id', $employee_id)->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])->where('status', 2)->pluck('number_of_day');
        $sumOfLeaveTaken = $totalLeaveTaken->sum();
        $checkLeaveEligibility = $sumOfLeaveTaken <= 15;
        $sumOfPaidLeaveTaken = $paidLeaveRule->day_of_paid_leave->sum();
        $results = $checkLeaveEligibility == true ? ($paidleaveType - $sumOfLeaveTaken) : 0;

        if ($employee_id != '' && $leave_type_id != '') {
            return $results;
        }
    }

    public function calculateEmployeeEarnLeave($leave_type_id, $employee_id, $action = false)
    {

        $employeeInfo = Employee::where('employee_id', $employee_id)->first();
        $joiningYearAndMonth = explode('-', $employeeInfo->date_of_joining);

        $joiningYear = $joiningYearAndMonth[0];
        $joiningMonth = (int) $joiningYearAndMonth[1];

        $currentYear = date("Y");
        $currentMonth = (int) date("m");

        $totalMonth = 0;

        if ($joiningYear == $currentYear) {
            for ($i = $joiningMonth; $i <= $currentMonth; $i++) {
                $totalMonth += 1;
            }
        } else {
            for ($i = 1; $i <= $currentMonth; $i++) {
                $totalMonth += 1;
            }
        }

        $ifExpenseLeave = LeaveApplication::select(DB::raw('IFNULL(SUM(leave_application.number_of_day), 0) as number_of_day'))
            ->where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('status', 2)
            ->whereBetween('approve_date', [date("Y-01-01"), date("Y-12-31")])
            ->first();

        $earnLeaveRule = EarnLeaveRule::first();

        if ($action == 'getEarnLeaveBalanceAndExpenseBalance') {
            $totalNumberOfDays = $totalMonth * $earnLeaveRule->day_of_earn_leave;
            $data = [
                'totalEarnLeave' => round($totalMonth * $earnLeaveRule->day_of_earn_leave),
                'leaveConsume' => $ifExpenseLeave->number_of_day,
                'currentBalance' => round($totalNumberOfDays - $ifExpenseLeave->number_of_day),
            ];
            return $data;
        }

        $totalNumberOfDays = $totalMonth * $earnLeaveRule->day_of_earn_leave;
        return round($totalNumberOfDays - $ifExpenseLeave->number_of_day);
    }

    public function calculateEmployeePaidLeave($leave_type_id, $employee_id, $action = false)
    {

        $employeeInfo = Employee::where('employee_id', $employee_id)->first();
        $joiningYearAndMonth = explode('-', $employeeInfo->date_of_joining);

        $joiningYear = $joiningYearAndMonth[0];
        $joiningMonth = (int) $joiningYearAndMonth[1];

        $currentYear = date("Y");
        $currentYearInt = (int) date("Y");
        $currentMonth = (int) date("m");

        $totalMonth = 0;
        $totalYear = 0;

        if ($joiningYear == $currentYear) {
            for ($i = $joiningMonth; $i <= $currentMonth; $i++) {
                $totalMonth += 1;
            }
        } else {
            for ($i = 1; $i <= $currentMonth; $i++) {
                $totalMonth += 1;
            }
            for ($y = 1; $y <= $currentYearInt; $y++) {
                $totalYear += 1;
            }
        }

        $ifExpenseLeave = LeaveApplication::select(DB::raw('IFNULL(SUM(leave_application.number_of_day), 0) as number_of_day'))
            ->where('employee_id', $employee_id)
            ->where('leave_type_id', $leave_type_id)
            ->where('status', 2)
            ->whereBetween('approve_date', [date("Y-01-01"), date("Y-12-31")])
            ->first();
        $totalLeavePerYear = LeaveApplication::where('employee_id', $employee_id)->whereBetween('created_at', [Carbon::now()->subYear(), Carbon::now()])->where('status', 2)->sum('number_of_day');
        $expectedPaidleave = LeaveType::where('leave_type_id', 2)->sum('num_of_day');
        $paidLeaveRule = PaidLeaveRule::first();

        // $totalYear         = $totalMonth >= 12 ? $totalMonth / 12 : $totalMonth;
        // $seperateTotalYear = explode('.', $totalMonth / 12);
        // $getTotalYear = $seperateTotalYear[0];
        // $getTotalMonth = $seperateTotalYear[1];

        if ($action == 'getEarnLeaveBalanceAndExpenseBalance') {
            $totalNumberOfDays = 1 * $paidLeaveRule->day_of_paid_leave;
            $data = [
                'totalPaidLeave' => round(1 * $paidLeaveRule->day_of_paid_leave),
                'leaveConsume' => $ifExpenseLeave->number_of_day,
                'currentBalance' => round($totalNumberOfDays - $ifExpenseLeave->number_of_day),
            ];
            return $data;
        }

        $totalNumberOfDays = 1 * $paidLeaveRule->day_of_paid_leave;
        // $leaveBalance      = round($totalNumberOfDays - $ifExpenseLeave->number_of_day);
        $results = ($totalLeavePerYear <= $paidLeaveRule->day_of_paid_leave ? $totalNumberOfDays : 0);
        return $results;
    }
}
