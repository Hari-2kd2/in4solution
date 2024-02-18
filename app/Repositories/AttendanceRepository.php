<?php

namespace App\Repositories;

use PSpell\Config;
use App\Model\CompOff;
use App\Model\Employee;
use App\Model\Incentive;
use App\Model\RhApplication;
use App\Model\WeeklyHoliday;
use App\Model\HolidayDetails;
use App\Model\ApproveOverTime;
use App\Model\LeaveApplication;
use App\Model\EmployeeInOutData;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\UserStatus;
use App\Lib\Enumerations\LeaveStatus;
use App\Lib\Enumerations\AttendanceStatus;

class AttendanceRepository
{
    public $AttendanceStatus = [
        1 => 'P',
        2 => 'A',
        3 => 'LE',
        4 => 'PH',
        14 => 'WH',
    ];
    public $AttendanceStatusClass = [
        1 => 'present',
        2 => 'absence',
        3 => 'leave',
        4 => 'publicHoliday',
        14 => 'leave',
    ];
    public $AttendanceStatusApi = [
        // '' => '-', 'present' => 'P', 'absence' => 'A', 'holiday' => 'WH', 'publicHoliday' => 'PH', 'leave' => 'LE', 'rhleave' => 'RH', 'OD' => 'OD', 'compoff' => 'CO', 'left' => 'LT',
        '' => '-', 1 => 'P', 2 => 'A', 14 => 'WH', 4 => 'PH', 3 => 'LE', 13 => 'OD', 13 => 'CO',
    ];

    public $leaveShort = [
        1 => 'CL',
        2 => 'SL',
        3 => 'PL',
        4 => 'OD',
        5 => 'MT',
        6 => 'PT',
        7 => 'RH',
    ];

    public $AttendanceStatusLegend = [
        'P' => 'Present',
        'A' => 'Absent',
        'WH' => 'Week Holiday',
        'PH' => 'Public Holiday',
        'LE' => 'Leave',
        'RH' => 'RH Leave',
        'OD' => 'On Duty',
        'CO' => 'Comp Off',
        'LT' => 'Left',
    ];

    // public static $PRESENT  = 1; // present
    // public static $ABSENT  = 2; // absent
    // public static $LEAVE  = 3; // leaves
    // public static $HOLIDAY  = 4; // public holiday
    // public static $FUTURE  = 5;
    // public static $UPDATE  = 6;
    // public static $ERROR  = 7;
    // public static $ONETIMEINPUNCH  = 8;
    // public static $ONETIMEOUTPUNCH = 9;
    // public static $LESSHOURS  = 10;
    // public static $COMPOFF  = 11; // comp off
    // public static $INCENTIVE  = 12;
    // public static $OD  = 13; // od
    // public static $WEEKOFF  = 14; // weekoff holiday


    public $AttendanceStatusID = [
        1 => 'Present',
        2 => 'Absent',
        3 => 'Leave',
        4 => 'Public Holiday',
        11 => 'Comp Off',
        13 => 'On Duty',
        14 => 'Week Holiday',
    ];

    const WORK_HOURS = '08:30:00';
    const MIN_OVERTIME = 30; // minutes (minutes based can only given) ex: 1 hours need minimum set 60
    const HALFDAY_HOURS = '04:00:00';
    const WORK_LATE_ALLOW = '00:10:00';

    public function getEmployeeDailyAttendance($date = false, $department_id, $attendance_status, $branch_id = false)
    {
        if ($date) {
            $data = dateConvertFormtoDB($date);
        } else {
            $data = date("Y-m-d");
        }

        $queryResults = DB::select("call `SP_DepartmentDailyAttendance`('" . $data . "', '" . $department_id . "','" . $attendance_status . "')");
        $results = [];

        foreach ($queryResults as $value) {
            $tempArr = [];
            $approvedOvertime = null;

            if ($value->approve_over_time_id != null) {
                $approvedOvertime = ApproveOverTime::find($value->approve_over_time_id);
            }
            $employee_data = DB::table('employee')->where('employee_id', $value->employee_id)->first();
            if ($branch_id && $branch_id != $employee_data->branch_id) {
                continue;
            }
            if (!$employee_data) {
                $employee_data = new Employee;
            }
            $leave = DB::table('leave_application')->where('employee_id', $value->employee_id)->where('application_from_date', '<=', $value->date)->where('application_to_date', '>=', $value->date)->first();

            // $Rhleave = RhApplication::where('employee_id',$value->employee_id)->where('holiday_date','>=',$value->date)->where('holiday_date','>=',$value->date)->first();
            // $OD = LeaveApplication::where('employee_id',$value->employee_id)->where('application_from_date','<=',$value->date)->where('application_to_date','>=',$value->date)->where('leave_type_id',4)->first();
            $publicHoliday = HolidayDetails::where('from_date', '<=', $value->date)->where('to_date', '>=', $value->date)->where('branch_id', $employee_data->branch_id)->first();


            $tempArr = $value;
            $tempArr->overtime_approval = $approvedOvertime;
            if (isset($publicHoliday)) {
                $tempArr->attendance_status = 4;
            } elseif (isset($leave)) {
                $tempArr->attendance_status = 3;
            } elseif (isset($OD)) {
                $tempArr->attendance_status = 13;
            }

            // dd($tempArr);
            $results[$value->department_name][] = $tempArr;
        }
        // dd($results);
        return $results;
    }

    public function getEmployeeDateAttendance($from_date = false, $to_date = false, $department_id, $attendance_status, $branch_id = false)
    {
        if ($from_date) {
            $from_date = dateConvertFormtoDB($from_date);
        } else {
            $from_date = date("Y-m-d");
        }
        if ($to_date) {
            $to_date = dateConvertFormtoDB($to_date);
        } else {
            $to_date = date("Y-m-d");
        }

        $all_dates = findFromDateToDateToAllDate($from_date, $to_date);

        $results = [];

        foreach ($all_dates as $key => $dateArray) {
            $queryResults = DB::select("call `SP_DepartmentDailyAttendance`('" . $dateArray['date'] . "', '" . $department_id . "','" . $attendance_status . "')");
            
            foreach ($queryResults as $value) {
                $tempArr = [];
                $approvedOvertime = null;

                if ($value->approve_over_time_id != null) {
                    $approvedOvertime = ApproveOverTime::find($value->approve_over_time_id);
                }

                $employee_data = DB::table('employee')->where('employee_id', $value->employee_id)->first();

                if (!$employee_data) {
                    $employee_data = new Employee;
                }

                $leave = DB::table('leave_application')->where('employee_id', $value->employee_id)->where('application_from_date', '<=', $value->date)->where('application_to_date', '>=', $value->date)->first();
                $publicHoliday = HolidayDetails::where('from_date', '<=', $value->date)->where('to_date', '>=', $value->date)->where('branch_id', $employee_data->branch_id)->first();

                $tempArr = $value;
                $tempArr->overtime_approval = $approvedOvertime;
                if (isset($publicHoliday)) {
                    $tempArr->attendance_status = 4;
                } elseif (isset($leave)) {
                    $tempArr->attendance_status = 3;
                } elseif (isset($OD)) {
                    $tempArr->attendance_status = 13;
                }

                $results[$value->department_name][] = $tempArr;
            }
        }

        return $results;
    }

    public function findAttendanceMusterReport($start_date, $end_date, $employee_id = '', $department_id = '', $branch_id = '')
    {
        $data = findMonthFromToDate($start_date, $end_date);

        $qry = '1 ';

        if ($employee_id != '') {
            $qry .= ' AND employee.employee_id=' . $employee_id;
        }
        if ($department_id != '') {
            $qry .= ' AND employee.department_id=' . $department_id;
        }
        if ($branch_id != '') {
            $qry .= ' AND employee.branch_id=' . $branch_id;
        }

        $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'branch_name', 'finger_id', 'employee_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('branch', 'branch.branch_id', 'employee.branch_id')->orderBy('employee.finger_id', 'ASC')->whereRaw($qry)
            ->where('status', UserStatus::$ACTIVE)->get();

        $attendance = DB::table('view_employee_in_out_data')->whereBetween('date', [$start_date, $end_date])->get();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));

        $dataFormat = [];
        $tempArray = [];

        foreach ($employees as $employee) {

            foreach ($data as $key => $value) {

                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['branch_name'] = $employee->branch_name;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];

                $hasAttendance = $this->hasEmployeeMusterAttendance($attendance, $employee->finger_id, $value['date']);

                $ifPublicHoliday = $this->ifPublicHoliday($govtHolidays, $value['date']);

                if ($ifPublicHoliday) {
                    $tempArray['attendance_status'] = 'holiday';
                    $tempArray['shift_name'] = $hasAttendance['shift_name'];
                    $tempArray['in_time'] = $hasAttendance['in_time'];
                    $tempArray['out_time'] = $hasAttendance['out_time'];
                    $tempArray['working_time'] = $hasAttendance['working_time'];
                    $tempArray['over_time'] = $hasAttendance['over_time'];
                    $tempArray['over_time_status'] = $hasAttendance['over_time_status'];
                    $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'] ?? '';
                } elseif ($hasAttendance) {
                    $tempArray['attendance_status'] = 'present';
                    $tempArray['shift_name'] = $hasAttendance['shift_name'];
                    $tempArray['in_time'] = $hasAttendance['in_time'];
                    $tempArray['out_time'] = $hasAttendance['out_time'];
                    $tempArray['working_time'] = $hasAttendance['working_time'];
                    $tempArray['over_time'] = $hasAttendance['over_time'];
                    $tempArray['over_time_status'] = $hasAttendance['over_time_status'];
                    $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'] ?? '';
                } else {

                    $tempArray['attendance_status'] = 'absence';
                    $tempArray['shift_name'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['over_time'] = '';
                    $tempArray['working_time'] = '';
                    $tempArray['over_time_status'] = '';
                    $tempArray['employee_attendance_id'] = '';
                }

                $dataFormat[$employee->finger_id][] = $tempArray;
            }
        }

        return $dataFormat;
    }
    public function hasEmployeeMusterAttendance($attendance, $finger_print_id, $date)
    {
        $dataFormat = [];
        $dataFormat['in_time'] = '';
        $dataFormat['out_time'] = '';
        $dataFormat['over_time'] = '';
        $dataFormat['working_time'] = '';
        $dataFormat['over_time_status'] = '';
        $dataFormat['shift_name'] = '';
        $dataFormat['employee_attendance_id'] = '';

        foreach ($attendance as $key => $val) {
            // dd($val);
            if (($val->finger_print_id == $finger_print_id && $val->date == $date && $val->in_time != null)) {
                $dataFormat['shift_name'] = $val->shift_name;
                $dataFormat['in_time'] = $val->in_time;
                $dataFormat['out_time'] = $val->out_time;
                $dataFormat['over_time'] = $val->over_time;
                $dataFormat['working_time'] = $val->working_time;
                $dataFormat['over_time_status'] = $val->over_time_status;
                $dataFormat['employee_attendance_id'] = $val->employee_attendance_id ?? '';
                return $dataFormat;
            }
        }
        return $dataFormat;
    }
    public function ifPublicHoliday($govtHolidays, $date)
    {
        $govt_holidays = [];

        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }
        return false;
    }

    public function getEmployeeMonthlyAttendance($from_date, $to_date, $employee_id)
    {
        $employee_data = Employee::where('employee_id', $employee_id)->first();
        $monthlyAttendanceData = DB::select("CALL `SP_monthlyAttendance`('" . $employee_id . "','" . $from_date . "','" . $to_date . "')");
        // dd($monthlyAttendanceData, $from_date, $to_date, $employee_id);

        $workingDates = $this->number_of_working_days_date($from_date, $to_date, $employee_id);
        $employeeLeaveRecords = $this->getEmployeeLeaveRecord($from_date, $to_date, $employee_id);
        $employeeODRecords = $this->getEmployeeODRecord($from_date, $to_date, $employee_id);
        $publicHolidayRecords = $this->getHolidayRecord($from_date, $to_date, $employee_data->branch_id);
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        // $weeklyHolidays = select day_name , employee_id from  weekly_holiday where status=1;;
        // if($monthlyAttendanceData) {
        //     dd(__LINE__);
        //     dd($monthlyAttendanceData);
        // }
        // if($employee_data->finger_id=='T0349') {
        //     dd($monthlyAttendanceData);
        // }
        $from_month = new \DateTime($from_date);
        $from_month = $from_month->format('Y-m');
        $to_month = new \DateTime($to_date);
        $to_month = $to_month->format('Y-m');
        $weeklyHolidays = DB::table('weekly_holiday')->whereRaw("employee_id='$employee_id' AND status=1 AND (month='$from_month' OR month='$to_month')")->get();
        $weeklyHolidayDates = [];
        foreach ($weeklyHolidays as $key => $eachMonth) {
            $weekoff_dates = json_decode($eachMonth->weekoff_days);
            if (is_array($weekoff_dates)) {
                $weeklyHolidayDates = array_merge($weeklyHolidayDates, $weekoff_dates);
            }
        }

        $dataFormat = [];
        $tempArray = [];
        $present = null;
        // dd($monthlyAttendanceData);

        if ($workingDates && $monthlyAttendanceData) {
            foreach ($workingDates as $data) {
                $flag = 0;
                foreach ($monthlyAttendanceData as $value) {
                    if ($data == $value->date && ($value->in_time != null || $value->out_time != null)) {
                        $flag = 1;
                        break;
                    }
                }
                $EmployeeInOutData = DB::table('view_employee_in_out_data')->where('date', $value->date)->where('finger_print_id', $value->finger_print_id)->first();
                $EmployeeInOutData = $EmployeeInOutData ?? new EmployeeInOutData;
                $tempArray['EmployeeInOutData'] = $EmployeeInOutData;
                $tempArray['total_present'] = null;
                if ($flag == 0) {
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $data;
                    $tempArray['working_time'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['lateCountTime'] = '';
                    $tempArray['ifLate'] = '';
                    $tempArray['totalLateTime'] = '';
                    $tempArray['workingHour'] = '';
                    $tempArray['total_present'] = $present;
                    if (in_array($data, $employeeLeaveRecords)) {
                        $tempArray['action'] = 'Leave';
                    } elseif (in_array($data, $weeklyHolidayDates)) {
                        $tempArray['action'] = 'Weekoff';
                    } elseif (in_array($data, $publicHolidayRecords)) {
                        $tempArray['action'] = 'Holiday';
                    } else {
                        $tempArray['action'] = 'Absence';
                    }
                    $dataFormat[] = $tempArray;
                } else {
                    $tempArray['total_present'] = $present += 1;
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $value->date;
                    $tempArray['working_time'] = $value->working_time;
                    $tempArray['in_time'] = $value->in_time;
                    $tempArray['out_time'] = $value->out_time;
                    $tempArray['lateCountTime'] = $value->lateCountTime;
                    $tempArray['ifLate'] = $value->ifLate;
                    $tempArray['totalLateTime'] = $value->totalLateTime;
                    $tempArray['workingHour'] = $value->workingHour;
                    $tempArray['action'] = 'Present';
                    $dataFormat[] = $tempArray;
                }
            }
        }
        return $dataFormat;
    }

    public function number_of_working_days_date($from_date, $to_date, $employee_id)
    {
        $holidays = DB::select(DB::raw('call SP_getHoliday("' . $from_date . '","' . $to_date . '")'));
        $public_holidays = [];
        foreach ($holidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $public_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        // $weeklyHolidays     = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        // $weeklyHolidayArray = [];
        // foreach ($weeklyHolidays as $weeklyHoliday) {
        //     $weeklyHolidayArray[] = $weeklyHoliday->day_name;
        // }

        $weeklyHolidayArray = WeeklyHoliday::select('day_name')
            ->where('employee_id', $employee_id)
            ->where('month', date('Y-m', strtotime($from_date)))
            ->orWhere('month', date('Y-m', strtotime($to_date)))
            ->first();

        $target = strtotime($from_date);
        $workingDate = [];

        while ($target <= strtotime(date("Y-m-d", strtotime($to_date)))) {

            //get weekly  holiday name
            $timestamp = strtotime(date('Y-m-d', $target));
            $dayName = date("l", $timestamp);

            // if (!in_array(date('Y-m-d', $target), $public_holidays) && !in_array($dayName, $weeklyHolidayArray->toArray())) {
            //     array_push($workingDate, date('Y-m-d', $target));
            // }

            // if (!in_array(date('Y-m-d', $target), $public_holidays)) {
            //     array_push($workingDate, date('Y-m-d', $target));
            // }

            \array_push($workingDate, date('Y-m-d', $target));

            if (date('Y-m-d') <= date('Y-m-d', $target)) {
                break;
            }
            $target += (60 * 60 * 24);
        }
        return $workingDate;
    }

    public function getEmployeeLeaveRecord($from_date, $to_date, $employee_id)
    {
        $queryResult = DB::table('leave_application')->select('application_from_date', 'application_to_date')
            ->where('status', LeaveStatus::$APPROVE)
            ->where('application_from_date', '>=', $from_date)
            ->where('application_to_date', '<=', $to_date)
            // ->where('leave_type_id','<', 4)
            ->where('employee_id', $employee_id)
            ->get();
        $leaveRecord = [];
        foreach ($queryResult as $value) {
            $start_date = $value->application_from_date;
            $end_date = $value->application_to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $leaveRecord[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        $queryResult = DB::table('restricted_holiday_application')->select('holiday_date', 'holiday_date')
            ->where('status', LeaveStatus::$APPROVE)
            ->where('holiday_date', '>=', $from_date)
            ->where('holiday_date', '<=', $to_date)
            ->where('employee_id', $employee_id)
            ->get();

        foreach ($queryResult as $value) {
            $start_date = $value->holiday_date;
            $end_date = $value->holiday_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $leaveRecord[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }
        return $leaveRecord;
    }
    public function getEmployeeODRecord($from_date, $to_date, $employee_id)
    {
        $queryResult = LeaveApplication::select('application_from_date', 'application_to_date')
            ->where('status', LeaveStatus::$APPROVE)
            ->where('application_from_date', '>=', $from_date)
            ->where('application_to_date', '<=', $to_date)
            ->where('leave_type_id', 4)
            ->where('employee_id', $employee_id)
            ->get();
        $leaveRecord = [];
        foreach ($queryResult as $value) {
            $start_date = $value->application_from_date;
            $end_date = $value->application_to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $leaveRecord[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }
        return $leaveRecord;
    }
    public function getHolidayRecord($from_date, $to_date, $branch_id)
    {
        $queryResult = DB::table('holiday_details')->select('from_date', 'to_date')
            ->where('from_date', '>=', $from_date)
            ->where('to_date', '<=', $to_date)
            ->where('branch_id', $branch_id)
            ->get();
        $holidayRecord = [];
        foreach ($queryResult as $value) {
            $start_date = $value->from_date;
            $end_date = $value->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $holidayRecord[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }
        return $holidayRecord;
    }

    public function getEmployeeHolidayRecord($from_date, $to_date, $employee_id)
    {
        $queryResult = WeeklyHoliday::select('weekoff_days')
            ->where('employee_id', $employee_id)
            ->whereBetween('month', [date('Y-m', strtotime($from_date)), date('Y-m', strtotime($to_date))])
            ->first();

        $holidayRecord = [];
        if ($queryResult) {
            foreach (\json_decode($queryResult['weekoff_days']) as $value) {
                $holidayRecord[] = $value;
            }
        }
        return $holidayRecord;
    }

    public function newAttendanceSummaryReport($month, $start_date, $end_date, $weekdays = false) // web monthly attendance summary
    {
        $data = findFromDateToDateToAllDate($start_date, $end_date);
        $attendance = DB::table('view_employee_in_out_data')->select('employee_attendance_id', 'finger_print_id', 'date', 'in_time', 'shift_name', 'inout_status', 'out_time', 'working_time')->whereBetween('date', [$start_date, $end_date])->get();

        if (session('logged_session_data.role_id') == 1) {
            $employees = DB::table('employee')->select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name',  'designation_name', 'finger_id', 'employee_id', 'branch.branch_id', 'branch.branch_name')
                ->join('designation', 'designation.designation_id', 'employee.designation_id')
                ->join('department', 'department.department_id', 'employee.department_id')
                ->join('branch', 'branch.branch_id', 'employee.branch_id')
                ->orderBy('employee.finger_id', 'ASC')
                // ->where('employee.branch_id', session('selected_branchId'))
                ->where('status', UserStatus::$ACTIVE)
                ->get();
        } else {
            $LoggedEmployee = Employee::loggedEmployee();
            $supervisorIds = $LoggedEmployee->supervisorIds();
            $employees = DB::table('employee')->select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name',  'designation_name', 'finger_id', 'employee_id', 'branch.branch_id', 'branch.branch_name')
                ->join('designation', 'designation.designation_id', 'employee.designation_id')
                ->join('department', 'department.department_id', 'employee.department_id')
                ->join('branch', 'branch.branch_id', 'employee.branch_id')
                ->orderBy('employee.finger_id', 'ASC')
                ->where('employee.branch_id', session('logged_session_data.branch_id'))
                ->where('status', UserStatus::$ACTIVE)
                ->whereIn('employee.supervisor_id', $supervisorIds)
                ->get();
            info('supervisorIds=' . implode(',', $supervisorIds));
        }

        $dataFormat = [];
        $tempArray = [];

        // static week holiday Sunday only need this project, so that we can enter Sunday Weekly Holiday record by manually
        $allSundays = getSundays($month);
        $flagInserted = false;
        // $weeklyHolidaysDates = WeeklyHoliday::where('month', date('Y-m', strtotime($start_date)))->get();

        foreach ($employees as $employee) {

            if (Config('leave.weekly_holiday') == 'Sunday') {
                // $tempweeklyHolidaysDates = $weeklyHolidaysDates->filter(function ($q) use ($employee) {
                //     return $q->where('employee_id', $employee->employee_id);
                // })->values()->first();
                $tempweeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->get();

                if (!$tempweeklyHolidaysDates) {
                    $weekData = [
                        'branch_id' => $employee->branch_id,
                        'employee_id' => $employee->employee_id,
                        'month' => $month,
                        'day_name' => Config('leave.weekly_holiday'),
                        'weekoff_days' => json_encode(array_values($allSundays)),
                        'created_by' => session('logged_session_data.employee_id') ?? 1,
                        'updated_by' => session('logged_session_data.employee_id') ?? 1,
                    ];
                    WeeklyHoliday::create($weekData);
                    $flagInserted = true;
                }
            }
        }

        if ($flagInserted == true && $weekdays === true) {
            return $weekdays;
        }

        foreach ($employees as $employee) {
            $activeUser = $employee->status;
            $leftUser = $employee->status;
            foreach ($data as $key => $value) {
                $EmployeeInOutData = DB::table('view_employee_in_out_data')->where('finger_print_id', $employee->finger_id)->whereBetween('date', [$value['date'], $value['date']])->first();
                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['employee_attendance_id'] = $EmployeeInOutData->employee_attendance_id ?? null;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['gender'] = $employee->gender;
                $tempArray['status'] = $employee->status;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];
                $tempArray['branch_id'] = $employee->branch_id;
                $tempArray['branch_name'] = $employee->branch_name;
                $tempArray['EmployeeInOutData'] = $EmployeeInOutData ?? null;
                $leftDate = date('Y-m-d', strtotime($employee->updated_at));
                $dataFormat[$employee->finger_id][] = $tempArray;
            } // $data end
        } // $employee end
        return $dataFormat;
    } // newAttendanceSummaryReport end

    public function findAttendanceSummaryReport($month, $start_date, $end_date, $weekdays = false) // web monthly attendance summary
    {
        $data = findFromDateToDateToAllDate($start_date, $end_date);
        $attendance = DB::table('view_employee_in_out_data')->select('employee_attendance_id', 'finger_print_id', 'date', 'in_time', 'shift_name', 'inout_status', 'out_time', 'working_time')->whereBetween('date', [$start_date, $end_date])->get();

        if (session('logged_session_data.role_id') == 1) {
            $employees = DB::table('employee')->select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name',  'designation_name', 'finger_id', 'employee_id', 'branch.branch_id', 'branch.branch_name')
                ->join('designation', 'designation.designation_id', 'employee.designation_id')
                ->join('department', 'department.department_id', 'employee.department_id')
                ->join('branch', 'branch.branch_id', 'employee.branch_id')
                ->orderBy('employee.finger_id', 'ASC')
                ->where('employee.branch_id', session('selected_branchId'))
                ->where('status', UserStatus::$ACTIVE)
                ->get();
        } else {
            $LoggedEmployee = Employee::loggedEmployee();
            $supervisorIds = $LoggedEmployee->supervisorIds();
            $employees = DB::table('employee')->select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name',  'designation_name', 'finger_id', 'employee_id', 'branch.branch_id', 'branch.branch_name')
                ->join('designation', 'designation.designation_id', 'employee.designation_id')
                ->join('department', 'department.department_id', 'employee.department_id')
                ->join('branch', 'branch.branch_id', 'employee.branch_id')
                ->orderBy('employee.finger_id', 'ASC')
                ->where('employee.branch_id', session('logged_session_data.branch_id'))
                ->where('status', UserStatus::$ACTIVE)
                ->whereIn('employee.supervisor_id', $supervisorIds)
                ->get();
            info('supervisorIds=' . implode(',', $supervisorIds));
        }

        $leave = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'leave_application.leave_type_id', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->get();

        $rHleave = DB::table('restricted_holiday_application')->select('restricted_holiday_application.holiday_date', 'restricted_holiday_application.holiday_id', 'employee_id', 'holiday_name')
            ->join('holiday_restricted', 'holiday_restricted.holiday_id', 'restricted_holiday_application.holiday_id')
            ->whereRaw("restricted_holiday_application.holiday_date >= '" . $start_date . "' and restricted_holiday_application.holiday_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->get();

        $od = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'leave_application.leave_type_id', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)
            ->where('leave_type.leave_type_id', 4)->get();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));

        $dataFormat = [];
        $tempArray = [];

        // static week holiday Sunday only need this project, so that we can enter Sunday Weekly Holiday record by manually
        $allSundays = getSundays($month);
        $flagInserted = false;
        foreach ($employees as $employee) {
            if (Config('leave.weekly_holiday') == 'Sunday') {
                $tempweeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();
                if (!$tempweeklyHolidaysDates) {
                    $weekData = [
                        'branch_id' => $employee->branch_id,
                        'employee_id' => $employee->employee_id,
                        'month' => $month,
                        'day_name' => Config('leave.weekly_holiday'),
                        'weekoff_days' => json_encode(array_values($allSundays)),
                        'created_by' => session('logged_session_data.employee_id'),
                        'updated_by' => session('logged_session_data.employee_id'),
                    ];
                    WeeklyHoliday::create($weekData);
                    $flagInserted = true;
                }
            }
        }

        if ($flagInserted == true && $weekdays === true) {
            return $weekdays;
        }

        foreach ($employees as $employee) {
            $activeUser = $employee->status;
            $leftUser = $employee->status;
            $weeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();
            foreach ($data as $key => $value) {
                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['gender'] = $employee->gender;
                $tempArray['status'] = $employee->status;
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];
                $tempArray['branch_id'] = $employee->branch_id;
                $tempArray['branch_name'] = $employee->branch_name;

                $leftDate = date('Y-m-d', strtotime($employee->updated_at));

                $hasAttendance = $this->hasEmployeeAttendance($attendance, $employee->finger_id, $value['date']);
                $tempArray['employee_attendance_id'] = $hasAttendance['employee_attendance_id'] ?? null;
                if ($employee->finger_id == 'T0220' && $value['date'] == '2023-10-27') {
                    // dd($hasAttendance);
                }

                if ($hasAttendance['status'] == true) {
                    $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                    if ($ifHoliday['weekly_holiday'] == true) {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } elseif ($ifHoliday['govt_holiday'] == true) {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['gov_day_worked'] = 'yes';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } else {
                        $tempArray['attendance_status'] = 'present';
                        $tempArray['leave_type'] = '';
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    }
                } else {

                    // if ($activeUser === UserStatus::$ACTIVE) {

                    $hasLeave = $this->ifEmployeeWasLeave($leave, $employee->employee_id, $value['date'], $rHleave);
                    // $hasOD = $this->ifEmployeeWasOD($od, $employee->employee_id, $value['date']);
                    $ifApplyLeaveOnHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);

                    if ($hasLeave) {
                        if ($ifApplyLeaveOnHoliday['weekly_holiday'] == true) {
                            $tempArray['attendance_status'] = 'holiday';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } elseif ($ifApplyLeaveOnHoliday['govt_holiday'] == true) {
                            $tempArray['attendance_status'] = 'publicHoliday';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } else {
                            $tempArray['inout_status'] = '';
                            $tempArray['attendance_status'] = 'leave';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = $hasLeave;
                            $tempArray['shift_name'] = '';
                            $tempArray['leave_name'] = $this->leave_name;
                            $tempArray['leave_type_id'] = $this->leave_type_id;

                            if (isset($v['attendance_status']) && isset($this->AttendanceStatus[$v['attendance_status']])) {
                                $tempArray['leave_name'] = $this->AttendanceStatus[$v['attendance_status']];
                            }
                        }
                    } else {
                        if ($value['date'] > date("Y-m-d")) {
                            $tempArray['attendance_status'] = '';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } elseif ($leftUser === UserStatus::$INACTIVE && $value['date'] >= $leftDate) {
                            $tempArray['attendance_status'] = 'left';
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } else {
                            $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                            if ($ifHoliday['weekly_holiday'] == true) {
                                $tempArray['attendance_status'] = 'holiday';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } elseif ($ifHoliday['govt_holiday'] == true) {
                                $tempArray['attendance_status'] = 'publicHoliday';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } else {
                                $tempArray['attendance_status'] = 'absence';
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            }
                        }
                    }
                    // } elseif (!$activeUser === UserStatus::$INACTIVE && $value['date'] > $leftDate) {
                    //     $tempArray['attendance_status'] = 'left';
                    //     $tempArray['gov_day_worked'] = 'no';
                    //     $tempArray['leave_type'] = '';
                    //     $tempArray['shift_name'] = '';
                    //     $tempArray['inout_status'] = '';
                    // }

                }

                $dataFormat[$employee->finger_id][] = $tempArray;
            }
        }

        return $dataFormat;
    }


    public function hasEmployeeAttendance($attendance, $finger_print_id, $date)
    {
        $temp = [];
        $temp['status'] = false;
        $temp['shift_name'] = '';
        $temp['inout_status'] = '';
        // dump($attendance, $finger_print_id, $date);
        foreach ($attendance as $key => $val) {
            if (($val->finger_print_id == $finger_print_id && $val->date == $date && $val->in_time != null)) {
                $temp['status'] = true;
                $temp['shift_name'] = $val->shift_name;
                $temp['inout_status'] = $val->inout_status;
                $temp['employee_attendance_id'] = $val->employee_attendance_id ?? '';
                $temp['halfday_status'] = $val->halfday_status ?? '';
                $temp['comp_off_status'] = $val->comp_off_status ?? '';
                return $temp;
            }
        }
        return $temp;
    }

    public $leave_name = '', $leave_type_id;
    public function ifEmployeeWasLeave($leave, $employee_id, $date, $rHleave)
    {

        $this->leave_name = '';
        $this->leave_type_id = '';
        $leaveRecord = [];
        $temp = [];
        foreach ($leave as $value) {
            if ($employee_id == $value->employee_id) {
                $start_date = $value->application_from_date;
                $end_date = $value->application_to_date;
                while (strtotime($start_date) <= strtotime($end_date)) {
                    $temp['employee_id'] = $employee_id;
                    $temp['date'] = $start_date;
                    $temp['leave_type_name'] = $value->leave_type_name;
                    $temp['leave_type_id'] = $value->leave_type_id ?? '';
                    $temp['employee_attendance_id'] = $value->employee_attendance_id ?? '';
                    $leaveRecord[] = $temp;
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
        }

        foreach ($leaveRecord as $val) {
            if (($val['employee_id'] == $employee_id && $val['date'] == $date)) {
                $this->leave_name = isset($this->leaveShort[$val['leave_type_id']]) ? $this->leaveShort[$val['leave_type_id']] : $val['leave_type_name'];
                $this->leave_type_id = isset($val['leave_type_id']) ? $val['leave_type_id'] : '';
                return $val['leave_type_name'];
            }
        }

        $leaveRhRecord = [];
        $temp = [];
        foreach ($rHleave as $value) {
            if ($employee_id == $value->employee_id) {
                $start_date = $value->holiday_date;
                $end_date = $value->holiday_date;
                while (strtotime($start_date) <= strtotime($end_date)) {
                    $temp['employee_id'] = $employee_id;
                    $temp['date'] = $start_date;
                    $temp['holiday_name'] = $value->holiday_name;
                    $temp['employee_attendance_id'] = $value->employee_attendance_id ?? '';
                    $leaveRhRecord[] = $temp;
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
        }

        foreach ($leaveRhRecord as $val) {

            if (($val['employee_id'] == $employee_id && $val['date'] == $date)) {
                $this->leave_name = 'RH';
                return $val['holiday_name'];
            }
        }



        return false;
    }
    // public function ifEmployeeWasOD($od, $employee_id, $date)
    // {
    //     $leaveRecord = [];
    //     $temp = [];
    //     foreach ($od as $value) {
    //         if ($employee_id == $value->employee_id) {
    //             $start_date = $value->application_from_date;
    //             $end_date = $value->application_to_date;
    //             while (strtotime($start_date) <= strtotime($end_date)) {
    //                 $temp['employee_id'] = $employee_id;
    //                 $temp['date'] = $start_date;
    //                 $temp['leave_type_name'] = $value->leave_type_name;
    //                 $leaveRecord[] = $temp;
    //                 $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
    //             }
    //         }
    //     }

    //     foreach ($leaveRecord as $val) {

    //         if (($val['employee_id'] == $employee_id && $val['date'] == $date)) {
    //             return $val['leave_type_name'];
    //         }
    //     }

    //     return false;
    // }

    public function ifHoliday($govtHolidays, $date, $employee_id, $weeklyHolidays, $weeklyHolidaysDates)
    {

        $govt_holidays = [];
        $result = [];
        $result['govt_holiday'] = false;
        $result['weekly_holiday'] = false;
        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                $result['govt_holiday'] = true;
            }
        }

        $timestamp = strtotime($date);
        $dayName = date("l", $timestamp);

        foreach ($weeklyHolidays as $v) {
            if ($v->day_name == $dayName && $v->employee_id == $employee_id && isset($weeklyHolidaysDates) && $dayName == $weeklyHolidaysDates['day_name']) {
                $result['weekly_holiday'] = true;
                return $result;
            }
        }
        return $result;
    }
}
