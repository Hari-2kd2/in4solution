<?php

namespace App\Repositories;

use App\Model\Role;
use App\Model\Holiday;
use App\Model\Employee;
use App\Model\PayGrade;
use App\Model\LeaveType;
use App\Model\WorkShift;
use App\Model\Department;
use App\Components\Common;
use App\Model\Designation;
use App\Model\TrainingType;
use App\Model\ExcelEmployee;
use App\Model\PerformanceCategory;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\UserStatus;
use App\Repositories\AttendanceRepository;

class SalaryRepository
{
    public $ESI_HRA = 25.87, $NON_ESI_HRA = 26.75;
    public $PERCENTAGE_EPF_EMPLOYER=12, $PERCENTAGE_EPF_EMPLOYEE=12;
    public $PERCENTAGE_ESI_EMPLOYER=3.25, $PERCENTAGE_ESI_EMPLOYEE=0.75;
    public $SICK_LEAVE_PER_YEAR=9, $CASUAL_LEAVE_PER_YEAR=6, $PRIVILEGE_LEAVE_PER_YEAR=15;
    public $PROBATOIN_MONTHS=6, $SERVICE_COMPLETE_YEAR=1, $NO_ESI_AGE_ABOVE=60;
    const PAYROLL_START_DATE=26;
    const PAYROLL_END_DATE=25;


    // public function addUpdatesProcess($salary_month) {
    //     $date = new \DateTime( $salary_month );
    //     $Total_Work_Days = $date->format('t');
    //     $fromDate = subtractMonth($date->format('Y-m-'.$this::PAYROLL_START_DATE), 1);
    //     $toDate = $date->format('Y-m-'.$this::PAYROLL_END_DATE);
    //     $EmployeeAll = Employee::where('status', UserStatus::$ACTIVE)->orderBy('finger_id')->get();
    //     $AttendanceRepository = new AttendanceRepository;
    //     $dateRanges = findMonthFromToDate($fromDate, $toDate);
    //     foreach ($EmployeeAll as $key => $Employee) {
    //         foreach ($dateRanges as $dkey => $data) {
    //             // dd($data);
    //         }
    //         // $findAttendanceMusterReport = $AttendanceRepository->findAttendanceMusterReport($fromDate, $toDate, $Employee->employee_id);
    //         $results = $AttendanceRepository->getEmployeeMonthlyAttendance($fromDate, $toDate, $Employee->employee_id);
    //         // info(__FUNCTION__);
    //         if(!$results) {
    //             info($Employee->emp_code.' NO.');
    //         } else {
    //             if($Employee->emp_code=='T0150') {
    //                 info($Employee->emp_code.' YES.');
    //                 info('Total_Work_Days='.$Total_Work_Days.', fromDate='.$fromDate.', toDate='.$toDate);
    //                 $Present=0;
    //                 $Absence=0;
    //                 $Weekoff=0;
    //                 $TotalLeaves=0;
    //                 // Absence, Weekoff
    //                 foreach ($results as $key => $data) {
    //                     if(isset($data['action'])) {
    //                         $var = $data['action'];
    //                         if(isset($$var)) {
    //                             $$var++;
    //                         } else {
    //                             dd($var);
    //                         }
    //                         // $$data['action']++;
    //                     } else {
    //                         dd($data['action']);
    //                     }
    //                 }
    //                 info('Present='.$Present.', Weekoff='.$Weekoff.', Absence='.$Absence);
    //             }
    //         }
    //     }
    // }

    public function salaryMonths($prompt=true) {
        // $PayrollStatementAll = DB::table('payroll_statement')->groupBy(DB::raw('MONTH(date), YEAR(date)'))->orderByDesc('date')->limit(200)->get();
        $EmployeeInOutDataAll = DB::table('view_employee_in_out_data')->groupBy(DB::raw('MONTH(date), YEAR(date)'))->orderByDesc('date')->limit(200)->get();
        $salaryMonthList=[];
        if($prompt==true) {
            $salaryMonthList[null] = ['- Select -'];
        }
        foreach ($EmployeeInOutDataAll as $key => $EmployeeInOutData) {
            $date = new \DateTime( $EmployeeInOutData->date );
            $monthKey = $date->format('Y-m');
            $monthDis = $date->format('m/Y');
            
            $previousMonth = subtractMonth($date->format('Y-m-'.$this::PAYROLL_START_DATE), 1);
            $currentMonth = $date->format('Y-m-'.$this::PAYROLL_END_DATE);
            $daysDiffs = $date->format('t');
            
            $Attendance = count(DB::table('view_employee_in_out_data')->where('date', '>=', $previousMonth)->where('date', '<=', $currentMonth)->groupBy('date')->get());
            $F = DB::table('view_employee_in_out_data')->where('date', '=', $previousMonth)->first(); // first date of previous month attendance found
            $E = DB::table('view_employee_in_out_data')->where('date', '=', $currentMonth)->first(); // laste date of current month attendance found
            if($F && $E) {
                info($previousMonth.', '.$currentMonth.', Attendance='.$Attendance);
                $monthDis = $date->format('m/Y');
                $salaryMonthList[$monthKey] = $monthDis . ' '.$Attendance.' (' . dateConvertDBtoForm($previousMonth) . ' - ' . dateConvertDBtoForm($currentMonth) . ', Month Days: '. $daysDiffs .' days)';
            }
        }
        return $salaryMonthList;
    }

    public function data($id, $field) {
        $data[1002] = [
            'worked_days' => 25,
            'leave_days' => 0,
            'absent_days' => 0,
            'above_60' => 1,
            'with_esi' => 0,
            'ot_hours' => 0,
        ];
        $data[1006] = [
            'worked_days' => 24,
            'leave_days' => 1,
            'sl_days' => 2,
            'absent_days' => 0,
            'with_esi' => 1,
            'ot_hours' => 46,
            'salary_advance' => 1111,
        ];
        $data[1233] = [
            'worked_days' => 24,
            'leave_days' => 1,
            'absent_days' => 0,
            'with_esi' => 1,
            'ot_hours' => 46,
            'salary_advance' => 1111,
        ];
        $data[1003] = [
            'worked_days' => 23,
            'leave_days' => 1,
            'absent_days' => 1,
            'with_esi' => 1,
            'ot_hours' => 0,
        ];
        
        if(isset($data[$id][$field])) {
            return $data[$id][$field];
        }
    }
    
    public function salaryBase() {
        $total_working = 25;
        $total_holiday = 1;
        $total_weekoff = 5;
        $total_days = $total_working + $total_holiday + $total_weekoff;

        return [
            'from' => '2023-07-25',
            'to' => '2023-08-26',
            'total_working' => $total_working,
            'total_holiday' => $total_holiday,
            'total_weekoff' => $total_weekoff,
            'total_days' => $total_days,
        ];
    }

    public function empData($finger_id, $field)
    {
        $Employee = Employee::where('finger_id', $finger_id)->first();
        if($Employee) {
            if($field == 'permonth') {
                $permonth = $Employee->salary_ctc;
                return $permonth;
            } else if($field == 'basic') {
                $permonth = $Employee->salary_ctc;
                $BASIC = $permonth / 100 * Common::PERCENTAGE_BASIC;
                return round($BASIC);
            }
            if(isset($Employee->$field)) {
                return $Employee->$field;
            }
        }
    }

    public function excelData($emp_code, $field)
    {
        $ExcelEmployee = ExcelEmployee::where('emp_code', $emp_code)->first();
        if($ExcelEmployee) {
            if($field == 'permonth') {
                $permonth = $ExcelEmployee->ctc;
                return $permonth;
            } else if($field == 'basic') {
                $permonth = $ExcelEmployee->ctc;
                $BASIC = $permonth / 100 * Common::PERCENTAGE_BASIC;
                return round($BASIC);
            }
            if(isset($ExcelEmployee->$field)) {
                return $ExcelEmployee->$field;
            }
        }
    }

    public function getEmployeeInfo($id)
    {
        return Employee::where('user_id', $id)->first();
    }

    public function getEmployeeDetails($id)
    {
        return Employee::where('employee_id', $id)->first();
    }


}
