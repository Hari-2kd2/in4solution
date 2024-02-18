<?php

namespace App\Http\Controllers\Attendance;

use DateTime;
use App\Model\MsSql;
use App\Model\CompOff;
use App\Jobs\ReportJob;
use App\Model\Employee;
use App\Model\WorkShift;
use Carbon\CarbonPeriod;
use App\Model\Department;
use App\Components\Common;
use App\Model\EmployeeShift;
use App\Model\ExcelEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Model\EmployeeInOutData;
use App\Model\LeavePermissionCase;
use Illuminate\Support\Facades\DB;
use App\Model\ManualAttendanceCase;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\EmployeeInOutDataCase;
use App\Model\ViewEmployeeInOutData;
use App\Lib\Enumerations\LeaveStatus;
use Illuminate\Support\Facades\Config;
use App\Lib\Enumerations\GeneralStatus;
use App\Lib\Enumerations\ShiftConstant;
use App\Lib\Enumerations\OvertimeStatus;
use App\Lib\Enumerations\AttendanceStatus;
use App\Repositories\AttendanceRepository;

class GenerateReportController extends Controller
{
    public $LEAVE, $RHLEAVE, $OVERTIME;
    public $finger_id_trace = ['T0002', 'T0042'];
    public function calculateAttendance(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $department_id = $request->department_id;
        $departmentList = Department::all();


        return view('admin.attendance.calculateAttendance.index', compact('from_date', 'to_date', 'departmentList', 'department_id'));
    }

    public function generateManualAttendanceReport($finger_print_id, $date, $in_time = '', $out_time = '', $manual, $recompute)
    {
        ob_start();
        set_time_limit(0);
        info('Generate Manual Attendance Report.....................');
        $employee = Employee::status(UserStatus::$ACTIVE)->where('finger_id', $finger_print_id)->select('finger_id', 'employee_id')->first();
        ob_end_flush();

        return $this->calculate_attendance($employee->finger_id, $employee->employee_id, $date, $in_time, $out_time, $manual, $recompute);
    }

    public function regenerateAttendanceReport(Request $request)
    {

        try {

            ob_start();
            set_time_limit(0);
            ini_set('memory_limit', '3072M');
            info('Calculate Attendance Report via recompute method.....................');

            $time_start = microtime(true);

            $datePeriod = CarbonPeriod::create(dateConvertFormtoDB($request->from_date), dateConvertFormtoDB($request->to_date));
            $startDate = $datePeriod->startDate->format('Y-m-d');
            $endDate = $datePeriod->endDate->format('Y-m-d');
            $sDAY = $datePeriod->startDate->format('d');
            $eDAY = $datePeriod->endDate->format('d');
            $sLAST_DAY = $datePeriod->startDate->format('t');
            $eLAST_DAY = $datePeriod->endDate->format('t');

            if ($sDAY >= ExcelEmployee::PAYROLL_START_DATE && $sDAY <= $sLAST_DAY) {
                $nextMonth = addMonth($datePeriod->startDate->format('Y-m-01'), 1);
                $nextMonth = new \DateTime($nextMonth);
                $SALARY_MONTH_KEY_START = $nextMonth->format('Y-m-d');
            } else {
                $SALARY_MONTH_KEY_START = $datePeriod->startDate->format('Y-m-01');
            }
            if ($eDAY >= ExcelEmployee::PAYROLL_START_DATE && $eDAY <= $eLAST_DAY) {
                $nextMonth = addMonth($datePeriod->endDate->format('Y-m-01'), 1);
                $nextMonth = new \DateTime($nextMonth);
                $SALARY_MONTH_KEY_END = $nextMonth->format('Y-m-d');
            } else {
                $SALARY_MONTH_KEY_END = $datePeriod->endDate->format('Y-m-01');
            }

            $PayrollStatement1 = \App\Model\PayrollStatement::where('salary_month', $SALARY_MONTH_KEY_START)->where('salary_freeze', 1)->first();
            $PayrollStatement2 = \App\Model\PayrollStatement::where('salary_month', $SALARY_MONTH_KEY_END)->where('salary_freeze', 1)->first();
            if ($PayrollStatement1 || $PayrollStatement2) {
                return '-1';
            }
            if ($request->department_id) {
                Employee::select('finger_id', 'employee_id')->where('employee_id', '>', 1)->orderBy('emp_code')->status(UserStatus::$ACTIVE)->whereIn('department_id', $request->department_id)
                    ->chunk(5, function ($employeeData) use ($datePeriod, $request) {
                        foreach ($employeeData as $key => $employee) {
                            foreach ($datePeriod as $date) {
                                $date = $date->format('Y-m-d');
                                $in_time = '';
                                $out_time = '';
                                $manualAttendance = false;
                                $recompute = true;
                                $this->calculate_attendance($employee->finger_id, $employee->employee_id, dateConvertFormtoDB($date), '', '', false, true);
                            }
                        }
                    });
            } else {
                Employee::select('finger_id', 'employee_id')->where('employee_id', '>', 1)->orderBy('emp_code')->status(UserStatus::$ACTIVE)->chunk(5, function ($employeeData) use ($datePeriod, $request) {
                    foreach ($employeeData as $key => $employee) {
                        foreach ($datePeriod as $date) {
                            $date = $date->format('Y-m-d');
                            $in_time = '';
                            $out_time = '';
                            $manualAttendance = false;
                            $recompute = true;
                            $this->calculate_attendance($employee->finger_id, $employee->employee_id, dateConvertFormtoDB($date), '', '', false, true);
                        }
                    }
                });
            }
            $bug = 0;

            $time_end = microtime(true);
            $execution_time_in_seconds = ($time_end - $time_start) . ' Seconds';

            info('Execution_time_in_seconds : ' . $execution_time_in_seconds);
            ob_end_flush();
            echo 'success';
        } catch (\Throwable $th) {
            $bug = $th->getMessage();
            info(json_encode(debug_backtrace()));
            info(__FILE__ . ':' . __LINE__ . ', bug=' . print_r($bug, 1));
            ob_end_flush();
            echo 'error';
        }
    }

    public function generatePayroll($dates, $employee)
    {
        $request = request();
        $salary_key = $request->salary_month . '-01';
        $month = $request->salary_month; // like this 2023-11
        info('Payroll excel upload start here ' . $request->salary_month . ', finger_id=' . $employee->finger_id);
        // Half Day present (0.5 P) (Any time between 4:00 hours & 8:20 hours is a Half day)
        // Less than 4:00 hours - it is LOP (though they have punches)
        // Full Day Present (1 P)- should be equal or greater than 8:20 hours

        $AttendanceRepository = new \App\Repositories\AttendanceRepository;
        $fromdate = dateConvertFormtoDB($dates['from_date'] ?? '');
        $todate = dateConvertFormtoDB($dates['to_date'] ?? '');

        $data = findFromDateToDateToAllDate($fromdate, $todate);

        $referenceData = [];
        $presents = 0;
        $absents = 0;
        $leaves = 0;
        $holidays = 0;
        $weekoffs = 0;
        $othours = 0;
        $allKeys = [];

        $referenceData = [];

        $temp = new DateTime($salary_key);
        $salary_month = $temp->format('m/Y');

        $ExcelEmployee = ExcelEmployee::where('salary_key', $salary_key)->where('emp_code', $employee->finger_id)->first();
        if (!$ExcelEmployee) {
            $ExcelEmployee = new ExcelEmployee;
            $ExcelEmployee->salary_month = $salary_month;
            $ExcelEmployee->salary_key = $salary_key;
            $ExcelEmployee->emp_code = $employee->finger_id;
            $ExcelEmployee->ctc = $ExcelEmployee->Employee->salary_ctc ?? 0;
            $ExcelEmployee->gross_salary = $ExcelEmployee->Employee->salary_gross ?? 0;
            $ExcelEmployee->salary_freeze = 0;
            $ExcelEmployee->save();
        } else {
            $ExcelEmployee->salary_month = $salary_month;
            $ExcelEmployee->salary_key = $salary_key;
            $ExcelEmployee->ctc = $ExcelEmployee->Employee->salary_ctc ?? 0;
            $ExcelEmployee->gross_salary = $ExcelEmployee->Employee->salary_gross ?? 0;
            if ($ExcelEmployee->salary_freeze == 0) {
                $ExcelEmployee->update();
            }
        }

        foreach ($data as $key => $value) {
            $EmployeeInOutData = EmployeeInOutDataCase::where('finger_print_id', $employee->finger_id)->where('date', $value['date'])->with('employee')->first();
            if (!$EmployeeInOutData) {
                $$EmployeeInOutData = new EmployeeInOutDataCase;
            }
            $tempArray['employee_id'] = $employee->employee_id;
            $tempArray['employee_attendance_id'] = $EmployeeInOutData->employee_attendance_id ?? null;
            $tempArray['finger_id'] = $employee->finger_id;
            $tempArray['fullName'] = $employee->fullname();
            $tempArray['designation_name'] = $employee->designation_name;
            $tempArray['department_name'] = $employee->department_name;
            $tempArray['gender'] = $employee->gender;
            $tempArray['status'] = $employee->status;
            $tempArray['date'] = $value['date'];
            $tempArray['day'] = $value['day'];
            $tempArray['day_name'] = $value['day_name'];
            $tempArray['branch_id'] = $employee->branch_id;
            $tempArray['branch_name'] = $employee->branch_name;

            $dataFormat[$value['date']] = $tempArray;

            $temp = new DateTime($salary_key);
            $salary_month = $temp->format('m/Y');


            if ($EmployeeInOutData) {
                switch ($EmployeeInOutData->attendance_status) {
                    case AttendanceStatus::$PRESENT:
                        if ($EmployeeInOutData->halfday_status > 0) {
                            $presents += $EmployeeInOutData->halfday_status;
                            // $absents += $EmployeeInOutData->halfday_status;
                        } else {
                            $presents++;
                        }
                        break;
                    case AttendanceStatus::$ABSENT:
                        $absents++;
                        break;
                    case AttendanceStatus::$LEAVE:
                        $leaves++;
                        break;
                    case AttendanceStatus::$HOLIDAY:
                        $holidays++;
                        break;
                    case AttendanceStatus::$WEEKOFF:
                        $weekoffs++;
                        break;

                    default:
                        info('No cashe please check this attendance_status' . $EmployeeInOutData->attendance_status);
                        break;
                }

                $WorkShift = DB::table('work_shift')->where('work_shift_id', $EmployeeInOutData->work_shift_id)->first();
                if ($WorkShift) {
                    $SHIFT_HOURS = timeDiffInHoursFormat($WorkShift->start_time, $WorkShift->end_time);
                    $SHIFT_HOURS = isset($SHIFT_HOURS['TOTAL_HOURS']) ? $SHIFT_HOURS['TOTAL_HOURS'] : AttendanceRepository::WORK_HOURS;
                } else {
                    $SHIFT_HOURS = AttendanceRepository::WORK_HOURS;
                }

                if ($EmployeeInOutData->over_time && $EmployeeInOutData->employee->overtime_status) {
                    $startTime = Carbon::parse('00:00:00');
                    $finishTime = Carbon::parse($EmployeeInOutData->over_time);
                    $overTimes = $finishTime->diffInMinutes($startTime);
                    // if($employee->finger_id='T0002') {
                    //     dd('overTimes', $overTimes);
                    // }
                    if ($overTimes >= AttendanceRepository::MIN_OVERTIME) {
                        $hours = $overTimes / 60;
                        $othours += truncateNum($hours);
                    }
                }
            }
        } // all dates entry ExcelEmployee (payroll_upload table)

        $action_totals = [
            'presents' => $presents,
            'absents' => $absents,
            'leaves' => $leaves,
            'holidays' => $holidays,
            'weekoffs' => $weekoffs,
            'othours' => $othours,
        ];
        $dataFormat['action'] = $action_totals;
        $referenceData = $dataFormat;


        $ExcelEmployee->attendance_log = json_encode($referenceData);
        $ExcelEmployee->days_presents = $presents;
        $ExcelEmployee->days_absents = $absents;
        $ExcelEmployee->days_holidays = $holidays;
        $ExcelEmployee->days_weekoffs = $weekoffs;
        $ExcelEmployee->days_leaves = $leaves;
        $ExcelEmployee->over_time = $othours;
        if ($ExcelEmployee->salary_freeze == 0) {
            $ExcelEmployee->update();
        }
        return redirect('payroll.generateUploadStatement')->with('success', 'Manual attendance saved successfully. ');
    }

    public function generateAttendanceReportForAnEmployee($finger_id, $date)
    {

        $employee = Employee::status(UserStatus::$ACTIVE)->where('finger_id', $finger_id)->select('finger_id', 'employee_id')->first();

        $in_time = '';
        $out_time = '';
        $manualAttendance = false;
        $recompute = true;

        dispatch(new ReportJob($employee->finger_id, $employee->employee_id, $date, $in_time, $out_time, $manualAttendance, $recompute));
    }

    public function generateAttendanceReport($date, $id = null)
    {
        \ob_start();
        \set_time_limit(0);
        info('Generate Attendance Report Scheduler.....................' . ($id ?? ''));

        $qry = 'employee_id > 1 '; // expect superadmin (is not an employee)
        if ($id != null) {
            $qry .= " AND finger_id='$id' ";
        }

        $employeeData = Employee::status(UserStatus::$ACTIVE)->whereRaw($qry)->select('finger_id', 'employee_id')->orderBy('finger_id', 'asc')->get();
        $in_time = '';
        $out_time = '';
        $manualAttendance = false;
        $recompute = true;

        foreach ($employeeData as $key => $employee) {
            $this->calculate_attendance($employee->finger_id, $employee->employee_id, $date, $in_time, $out_time, $manualAttendance, $recompute);
        }

        ob_end_flush();
    }

    public function compOffCredit($data_format, $Employee)
    {
        if (isset($data_format['comp_off_status']) && $data_format['comp_off_status'] > 0) {
            $employee_id = $Employee->employee_id;
            $CompOff = CompOff::where('employee_id', $Employee->employee_id)->where('working_date', $data_format['date'])->first();
            // not credits or un-used status will be every generate attendance update
            if (!$CompOff || ($CompOff && $CompOff->status == 0)) {
                if (!$CompOff) {
                    $CompOff = new CompOff;
                    $CompOff->employee_id = $employee_id;
                    $CompOff->working_date = $data_format['date'];
                    $CompOff->finger_print_id = $data_format['finger_print_id'];
                    $CompOff->branch_id = $Employee->branch_id;
                    $CompOff->save();
                }
                $expire_date = nextMonthFirstDate($data_format['date']);
                $expire_date = nextMonthFirstDate($expire_date);
                $CompOff->off_days = $data_format['comp_off_status'];
                $CompOff->expire_date = $expire_date;
                $CompOff->status = 0;
                $CompOff->update();
            }
            $comp_off_total = CompOff::where('employee_id', $employee_id)->where('status', 0)->sum('off_days');
            if ($Employee->EmployeeLeaves) {
                $Employee->EmployeeLeaves->comp_off = $comp_off_total;
                $Employee->EmployeeLeaves->update();
            }
        }
    }

    public function store($data_format, $employee_id, $manualAttendance, $recompute)
    {
        //insert employee attendance data to report table
        $if_exists = EmployeeInOutDataCase::where('finger_print_id', $data_format['finger_print_id'])->where('date', $data_format['date'])->first();
        $if_manual_override_exists = EmployeeInOutDataCase::where('finger_print_id', $data_format['finger_print_id'])->where('date', $data_format['date'])->where('device_name', 'Manual')->first();
        $Employee = Employee::where('employee_id', $employee_id)->with('EmployeeLeaves')->first();
        if ($Employee) {
            $data_format['branch_id'] = $Employee->branch_id;
        }
        if ($Employee->finger_id == 'TEST01') {
            // dd(__LINE__.' data_format', $data_format, $this->OVERTIME);
        }

        // comp off credit process
        $this->compOffCredit($data_format, $Employee);

        $leaveStatus = $this->leaveStatus($data_format['date'], $employee_id);
        $notes = [];
        $data_format['notes'] = null;
        if ($this->OVERTIME) {
            $notes['OVERTIME'] = $this->OVERTIME;
        }
        if ($Employee->finger_id == 'TEST01') {
            // dd(__LINE__.' data_format', $data_format, $this->OVERTIME);
        }

        if ($this->LEAVE) {
            $notes['LEAVE'] = $this->LEAVE;
        }
        if ($this->RHLEAVE) {
            $notes['RHLEAVE'] = $this->RHLEAVE;
        }
        if ($leaveStatus) {
            $data_format['attendance_status'] = AttendanceStatus::$LEAVE;
        }
        if ($notes) {
            $data_format['notes'] = json_encode($notes);
        }

        if (($recompute) || ($recompute == false && $manualAttendance)) {
            if ($data_format != []) {
                // dd(__LINE__);
                if (!$if_exists) {
                    EmployeeInOutDataCase::insert($data_format);
                    return true;
                } else {
                    unset($data_format['created_by']);
                    unset($data_format['created_at']);
                    foreach ($data_format as $key => $value) {
                        $if_exists->$key = $value;
                    }
                    $test = $if_exists;
                    $if_exists->update();
                    if ($Employee->finger_id == 'TEST01') {
                        // dd('test', $test, 'if_exists', $if_exists);
                    }
                    return true;
                }
            } else {
                // dd(__LINE__);
                $tempArray = [];

                $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $data_format['date'] . '","' . $data_format['date'] . '")'));
                $companyHolidayDetails = DB::select(DB::raw('call SP_getCompanyHoliday("' . $data_format['date'] . '","' . $data_format['date'] . '","' . $employee_id . '")'));
                if ($data_format['date'] > date("Y-m-d")) {
                    $tempArray['attendance_status'] = AttendanceStatus::$FUTURE;
                } else {
                    $ifHoliday = $this->ifHoliday($govtHolidays, $data_format['date']);
                    $ifCompanyHoliday = $this->ifCompanyHoliday($companyHolidayDetails, $data_format['date']);

                    if ($ifHoliday) {
                        $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                    } elseif ($ifCompanyHoliday) {
                        $tempArray['attendance_status'] = AttendanceStatus::$WEEKOFF;
                    } else {
                        $tempArray['attendance_status'] = AttendanceStatus::$ABSENT;
                    }
                }
                if (!$if_exists) {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    EmployeeInOutDataCase::insert($data_format);
                } else {
                    $data_format['attendance_status'] = $tempArray['attendance_status'];
                    foreach ($data_format as $key => $value) {
                        $if_exists->$key = $value;
                    }
                    $if_exists->update();
                }
            }
        } else {
            info('Manual override skipped when calculating reports for an employee - ' . $data_format['finger_print_id'] . ' on ' . $data_format['date'] . '...........');
        }
    }

    public function calculate_attendance($finger_id, $employee_id, $date, $in_time = '', $out_time = '', $manualAttendance = false, $recompute = false)
    {
        $month = date('Y-m', strtotime($date));
        $dataSet = [];

        $day = 'd_' . (int) date('d', strtotime($date));

        $shift = DB::table('employee_shift')->where('finger_print_id', $finger_id)->where('month', $month)->first();

        if ($manualAttendance) {
            if ($shift && $shift->$day != null) {
                info('manualAttenadance With Shift Allotted' . $finger_id);
                $dataSet = $this->manualAttendanceReport($in_time, $out_time, $date, $finger_id, $shift, $day);
            } else {
                info('manualAttenadance ' . $finger_id);
                $dataSet = $this->manualAttendanceReport($in_time, $out_time, $date, $finger_id);
            }
        } else {

            if ($shift && $shift->$day != null) {
                info('shiftBasedReport ' . $finger_id);
                $dataSet = $this->shiftBasedReport($shift, $date, $month, $day, $finger_id);
            } else {

                $hasReport = DB::table('view_employee_in_out_data')->where('finger_print_id', $finger_id)->whereDate('date', $date)->first();

                $Employee = DB::table('employee')->where('finger_id', $finger_id)->first();
                $WorkShift = DB::table('work_shift')->where('work_shift_id', $Employee->work_shift_id)->first();

                if (!$WorkShift) {
                    info('No shift assigned for employee (' . $finger_id . ')!');
                    $WorkShift = new WorkShift;
                }

                $start_time = $WorkShift->start_time;
                $minTime = date('Y-m-d H:i:s', strtotime('-' . ShiftConstant::$SHIFT_BUFFER_INT . ' minutes', strtotime($start_time)));

                $start_date = DATE('Y-m-d', strtotime($date)) . " " . date('H:i:s', strtotime('-' . ShiftConstant::$SHIFT_BUFFER_INT . ' minutes', strtotime($minTime)));
                $end_date = DATE('Y-m-d', strtotime($date . " +1 day")) . " 00:00:00";
                $fingerID = (object) ['finger_id' => $finger_id];

                $dataSet = $this->autoGenReport($start_date, $end_date, $fingerID, $hasReport ? true : false);
                // dd($Employee->finger_id);
                if ($Employee->finger_id == 'TEST01') {
                    // dd(__LINE__.'dataSet', $dataSet);
                }
            }
        }
        if ($finger_id == 'TEST01') {
            // dd(__LINE__.' dataSet', $dataSet);
        }
        return $this->store($dataSet, $employee_id, $manualAttendance, $recompute);
    }

    public function leaveStatus($date, $employee_id)
    {
        $Employee = DB::table('employee')->where('employee_id', $employee_id)->first();
        $employee_id = $Employee ? $Employee->employee_id : '';
        $LEAVE = DB::table('leave_application')->where('employee_id', $employee_id)->whereRaw("((application_from_date BETWEEN '$date' AND '$date') OR (application_to_date BETWEEN '$date' AND '$date'))")->where('status', LeaveStatus::$APPROVE)->first();
        $RHLEAVE = DB::table('restricted_holiday_application')->where('employee_id', $employee_id)->where('holiday_date', $date)->where('status', LeaveStatus::$APPROVE)->first();
        if ($LEAVE || $RHLEAVE) {
            if ($LEAVE) {
                $this->LEAVE = $LEAVE;
            } else {
                $this->LEAVE = '';
            }
            if ($RHLEAVE) {
                $this->RHLEAVE = $RHLEAVE;
            } else {
                $this->RHLEAVE = '';
            }
            return AttendanceStatus::$LEAVE;
        } else {
            $this->LEAVE = '';
            $this->RHLEAVE = '';
        }
    }

    public function autoGenReport($date_from, $date_to, $finger_id, $reRun)
    {

        \set_time_limit(0);
        $results = [];
        $dataSet = [];
        $attendance_data = [];

        $generate_date = date('Y-m-d', strtotime($date_from));
        // $results_in = DB::table('ms_sql')->whereRaw("DATE(`datetime`)='$generate_date'")
        //     ->where('ID', $finger_id->finger_id)
        //     ->where('type', 'IN')
        //     ->orderby('datetime', 'ASC')
        //     ->first();
        // $results_out = DB::table('ms_sql')->whereRaw("DATE(`datetime`)='$generate_date'")
        //     ->where('ID', $finger_id->finger_id)
        //     ->where('type', 'OUT')
        //     ->orderby('datetime', 'DESC')
        //     ->first();

        if ($reRun) {
            // Plant In, Out not show properly issue $results = DB::table('ms_sql')->whereRaw("datetime >= '" . $date_from . "' AND datetime <= '" . $date_to . "'") // $date_from = 2023-12-01 09:00:00, $date_to = 2023-12-02 00:00:00
            $results = DB::table('ms_sql')->whereRaw("DATE(`datetime`)='$generate_date'")
                ->where('ID', $finger_id->finger_id)
                ->orderby('datetime', 'ASC')
                ->get();
        } else {
            // Plant In, Out not show properly issue $results = DB::table('ms_sql')->whereRaw("datetime >= '" . $date_from . "' AND datetime <= '" . $date_to . "'") // $date_from = 2023-12-01 09:00:00, $date_to = 2023-12-02 00:00:00
            $results = DB::table('ms_sql')->whereRaw("DATE(`datetime`)='$generate_date'")
                ->where('ID', $finger_id->finger_id)
                ->where('status', '!=', null)
                ->orderby('datetime', 'ASC')
                ->get();
        }

        if (isset($results[0])) {
            $results_in = $results[0];
        }
        if (isset($results[count($results) - 1])) {
            $results_out = $results[count($results) - 1];
        }

        $check_date = date('Y-m-d', strtotime($date_from));
        $employee = DB::table('employee')->where('finger_id', $finger_id->finger_id)->first();
        $LeavePermissionCase = LeavePermissionCase::where('employee_id', $employee->employee_id)->where('leave_permission_date', $check_date)->where('status', LeaveStatus::$APPROVE)->first();
        $addPermissionMinutes = 0;

        if ($LeavePermissionCase) {
            $addPermissionMinutes = convertHoursMinuteToMinute($LeavePermissionCase->permission_duration);
        }
        $attendance_data['halfday_status'] = null;

        $attendance_data['work_shift_id'] = $employee->work_shift_id ?? null;
        $ManualAttendanceIn = ManualAttendanceCase::where('manual_date', $check_date)->where('ID', $finger_id->finger_id)->where('type', 'IN')->first();
        $ManualAttendanceOut = ManualAttendanceCase::where('manual_date', $check_date)->where('ID', $finger_id->finger_id)->where('type', 'OUT')->first();
        if ($finger_id->finger_id == 'TEST01') {
            // dd('$ManualAttendanceIn', $ManualAttendanceIn, 'ManualAttendanceOut', $ManualAttendanceOut);
        }
        $manual_found = false;
        if ($ManualAttendanceIn && $ManualAttendanceOut) {
            $manual_found = true;
            $results_in = new MsSql;
            $results_in->device_name = 'Manual';
            $results_out = new MsSql;
            $results_out->device_name = 'Manual';
        }
        if ($ManualAttendanceIn && isset($results_in)) {
            $results_in->datetime = $ManualAttendanceIn->datetime;
        }
        if ($ManualAttendanceOut && isset($results_out)) {
            $results_out->datetime = $ManualAttendanceOut->datetime;
        }
        // first check manual attendance found
        if ($manual_found) {
            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['branch_id'] = $results_in->branch_id;
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results_in->datetime));
            $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results_out->datetime));
            $attendance_data['working_time'] = $this->workingtime($results_in->datetime, $results_out->datetime);
            $attendance_data['working_hour'] = $this->workingtime($results_in->datetime, $results_out->datetime);
            $attendance_data['device_name'] = $results_in->device_name;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = null;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = $this->in_out_time($results);
            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
            $dataSet['over_time'] = $this->timeCalc($attendance_data);
            if ($finger_id->finger_id == 'TEST01') {
                // dd(__LINE__.': came here', 'dataSet', $dataSet);
            }
        } else if (count($results) == 0) {
            if ($finger_id->finger_id == 'TEST01') {
                // dd(__LINE__.': came here');
            }
            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['branch_id'] = null;
            $attendance_data['in_time'] = null;
            $attendance_data['out_time'] = null;
            $attendance_data['working_time'] = null;
            $attendance_data['working_hour'] = null;
            $attendance_data['device_name'] = null;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = AttendanceStatus::$ABSENT;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = null;

            $dataSet = $attendance_data;
        } elseif (count($results) == 1 && $results_in) {
            if ($finger_id->finger_id == 'TEST01') {
                // dd(__LINE__.': came here');
            }
            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['branch_id'] = $results_in->branch_id;
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results_in->datetime));
            $attendance_data['out_time'] = null;
            $attendance_data['working_time'] = null;
            $attendance_data['working_hour'] = null;
            $attendance_data['device_name'] = $results_in->device_name;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = AttendanceStatus::$ONETIMEINPUNCH;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = date('d/m/y H:i', strtotime($results_in->datetime)) . ":" . ('IN');

            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
            $dataSet['over_time'] = $this->timeCalc($attendance_data);
        } elseif (count($results) >= 2 && $results_in && $results_out) {
            if ($finger_id->finger_id == 'TEST01') {
                // dd(__LINE__.': came here');
            }
            $attendance_data['date'] = date('Y-m-d', strtotime($date_from));
            $attendance_data['finger_print_id'] = $finger_id->finger_id;
            $attendance_data['branch_id'] = $results_in->branch_id;
            $attendance_data['in_time'] = date('Y-m-d H:i:s', strtotime($results_in->datetime));
            $attendance_data['out_time'] = date('Y-m-d H:i:s', strtotime($results_out->datetime));
            $attendance_data['working_time'] = $this->workingtime($results_in->datetime, $results_out->datetime);
            $attendance_data['working_hour'] = $this->workingtime($results_in->datetime, $results_out->datetime);
            $attendance_data['device_name'] = $results_in->device_name;
            $attendance_data['status'] = 1;
            $attendance_data['attendance_status'] = null;
            $attendance_data['created_at'] = date('Y-m-d H:i:s');
            $attendance_data['updated_at'] = date('Y-m-d H:i:s');
            $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
            $attendance_data['in_out_time'] = $this->in_out_time($results);
            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
            $dataSet['over_time'] = $this->timeCalc($attendance_data);
        }

        $dataSet['halfday_status'] = null;
        if (isset($dataSet['work_shift_id']) && $dataSet['work_shift_id']) {
            $WorkShift = DB::table('work_shift')->where('work_shift_id', $dataSet['work_shift_id'])->first();
            if ($WorkShift) {
                $SHIFT_HOURS = timeDiffInHoursFormat($WorkShift->start_time, $WorkShift->end_time);
                $LATE_TIME = timeDiffInHoursFormat($WorkShift->start_time, $WorkShift->late_count_time);
                $SHIFT_HOURS['MINUTE'] = convertHoursMinuteToMinute($LATE_TIME['TOTAL_HOURS']);
                $SHIFT_HOURS['ALLOW_TOTAL'] = date('H:i:s', (strtotime($SHIFT_HOURS['TOTAL_HOURS']) - (($SHIFT_HOURS['MINUTE'] + $addPermissionMinutes) * 60)));
            } else {
                $SHIFT_HOURS['SHIFT'] = null;
                $SHIFT_HOURS['MINUTE'] = convertHoursMinuteToMinute(AttendanceRepository::WORK_LATE_ALLOW);
                $SHIFT_HOURS['ALLOW_TOTAL'] = date('H:i:s', (strtotime(AttendanceRepository::WORK_HOURS) - (($SHIFT_HOURS['MINUTE'] + $addPermissionMinutes) * 60)));
            }
        }
        if (isset($dataSet['in_time']) && isset($dataSet['out_time']) && $dataSet['in_time'] && $dataSet['out_time']) {
            if (!isset($dataSet['working_time']) || !$dataSet['working_time']) {
                $dataSet['working_time'] = $this->workingtime($dataSet['in_time'], $dataSet['out_time']);
                $dataSet['working_hour'] = $this->workingtime($dataSet['in_time'], $dataSet['out_time']);
            }

            $WORKED_HOURS = timeDiffInHoursFormat($dataSet['in_time'], $dataSet['out_time']);
            $WORKED_HOURS['MINUTE'] = convertHoursMinuteToMinute(AttendanceRepository::WORK_LATE_ALLOW);
            $publicHoliday = DB::table('holiday_details')->where('from_date', '>=', $dataSet['date'])->where('to_date', '<=', $dataSet['date'])->first();
            $DAY_NAME = date('l', strtotime($dataSet['date']));
            if ($finger_id->finger_id == 'TEST01') {
                // dd(__LINE__.': WORKED_HOURS', $WORKED_HOURS, 'SHIFT_HOURS', $SHIFT_HOURS, 'addPermissionMinutes', $addPermissionMinutes);
            }
            if (isset($WORKED_HOURS['TOTAL_HOURS']) && isset($SHIFT_HOURS['ALLOW_TOTAL'])) {
                if ($WORKED_HOURS['TOTAL_HOURS'] >= $SHIFT_HOURS['ALLOW_TOTAL']) {
                    $dataSet['attendance_status'] = AttendanceStatus::$PRESENT;
                    $dataSet['halfday_status'] = null;
                } else if (isset($WORKED_HOURS['TOTAL_HOURS']) && $WORKED_HOURS['TOTAL_HOURS'] >= AttendanceRepository::HALFDAY_HOURS) {
                    $dataSet['attendance_status'] = AttendanceStatus::$PRESENT;
                    $dataSet['halfday_status'] = 0.5;
                } else {
                    $dataSet['attendance_status'] = AttendanceStatus::$ABSENT;
                }
            } else {
                $dataSet['attendance_status'] = AttendanceStatus::$ABSENT;
            }
            // week of worked - added to comp credit status
            if ($DAY_NAME == Config::get('leave.weekly_holiday') && $dataSet['attendance_status'] = AttendanceStatus::$PRESENT) {
                $dataSet['comp_off_status'] = isset($dataSet['halfday_status']) && $dataSet['halfday_status'] == 0.5 ? $dataSet['halfday_status'] : 1;
            } else if ($publicHoliday) {
                $dataSet['comp_off_status'] = isset($dataSet['halfday_status']) && $dataSet['halfday_status'] == 0.5 ? $dataSet['halfday_status'] : 1;
            }
        } else {
            // set default absent in not in/out time avaialbel, check one by one othe status if found this will cahnged
            $dataSet['attendance_status'] = AttendanceStatus::$ABSENT;
            $dataSet['halfday_status'] = null;
            // check if have any leave change the attendance leave status
            $DAY_NAME = date('l', strtotime($attendance_data['date']));
            if ($employee) {
                $FROM_DATE = date('Y-m-d', strtotime($date_from));
                $publicHoliday = DB::table('holiday_details')->where('from_date', '>=', $FROM_DATE)->where('to_date', '<=', $date_to)->first();
                $date = new DateTime($date_from);
                $from_month = $date->format('Y-m');
                $date = new DateTime($date_to);
                $to_month = $date->format('Y-m');
                $weeklyHolidays = DB::table('weekly_holiday')->whereRaw("employee_id='" . $employee->employee_id . "' AND status=1 AND (month='" . $from_month . "' OR month='" . $to_month . "')")->get();
                $weeklyHolidayDates = [];
                foreach ($weeklyHolidays as $key => $eachMonth) {
                    $weekoff_dates = json_decode($eachMonth->weekoff_days);
                    if (is_array($weekoff_dates)) {
                        $weeklyHolidayDates = array_merge($weeklyHolidayDates, $weekoff_dates);
                    }
                }
                if ($publicHoliday) {
                    $dataSet['attendance_status'] = AttendanceStatus::$HOLIDAY;
                } else if ($weeklyHolidayDates) {
                    if ($DAY_NAME == Config::get('leave.weekly_holiday')) {
                        $dataSet['attendance_status'] = AttendanceStatus::$WEEKOFF;
                    }
                }
            }
        }
        if ($finger_id->finger_id == 'TEST01') {
            // dd(__LINE__.': attendance_data', $attendance_data, 'dataSet', $dataSet);
            // dd(__LINE__.': dataSet', $dataSet);
        }
        return $dataSet;
    }

    public function timeCalc($data_format)
    {

        if ($data_format != [] && isset($data_format['working_time'])) {
            // employee worked hours
            $working_time = $data_format['working_time'];

            $WorkShift = DB::table('work_shift')->where('work_shift_id', $data_format['work_shift_id'])->first();
            if ($WorkShift) {
                $SHIFT_HOURS = timeDiffInHoursFormat($WorkShift->start_time, $WorkShift->end_time);
                $SHIFT_HOURS = isset($SHIFT_HOURS['TOTAL_HOURS']) ? $SHIFT_HOURS['TOTAL_HOURS'] : AttendanceRepository::WORK_HOURS;
            } else {
                $SHIFT_HOURS = AttendanceRepository::WORK_HOURS;
            }
            if ($working_time) { //  && $working_time<='23:59:59'
                // dd($data_format);
                $startTime = Carbon::parse($SHIFT_HOURS);
                $finishTime = Carbon::parse($working_time);
                $OVERTIME_HOURS_FORMAT = $finishTime->format('H:i:s');
                if ($OVERTIME_HOURS_FORMAT > $SHIFT_HOURS) {
                    $startTime = Carbon::parse(AttendanceRepository::WORK_HOURS);
                    $finishTime = Carbon::parse($working_time);
                    $overTimes = $finishTime->diffInMinutes($startTime);
                    $hours = $overTimes / 60;
                    $this->OVERTIME = sprintf('%02d:%02d', (int) $hours, fmod($hours, 1) * 60);
                    if ($data_format['finger_print_id'] = 'TEST01') {
                        // dd(__FUNCTION__, $data_format, $this->OVERTIME, "$OVERTIME_HOURS_FORMAT > $SHIFT_HOURS");
                    }
                    return $this->OVERTIME;
                }
            }
        }
        $this->OVERTIME = '';
        return null;
    }

    public function manualAttendanceReport($fdatetime, $tdatetime, $date, $finger_id, $shift = null, $day = null)
    {
        $attendance_data = [];
        $dataSet = [];
        $working_time = $this->workingtime($fdatetime, $tdatetime);

        if ($shift != null) {
            $shiftData = WorkShift::where('work_shift_id', $shift->$day)->first();
        }

        $rawData = [
            'date' => date('Y-m-d', strtotime($date)),
            'finger_print_id' => $finger_id,
            'in_time' => date('Y-m-d H:i:s', strtotime($fdatetime)),
            'out_time' => date('Y-m-d H:i:s', strtotime($tdatetime)),
            'shift_name' => $shift != null ? $shiftData->shift_name : null,
            'work_shift_id' => $shift != null ? $shiftData->work_shift_id : null,
            'working_time' => $working_time,
            'working_hour' => null,
            'device_name' => 'Manual',
            'over_time' => null,
            'attendance_status' => null,
            'in_out_time' => date('d/m/y H:i', strtotime($fdatetime)) . ":" . ('IN,') . ' ' . date('d/m/y H:i', strtotime($tdatetime)) . ":" . ('OUT'),
        ];

        $attendance_data = $this->reportDataFormat($rawData);

        $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
        $dataSet['over_time'] = $this->timeCalc($attendance_data);
        return $dataSet;
    }

    public function shiftBasedReport($shift, $date, $month, $day, $finger_id)
    {
        info('Shift Based Report function.....................');

        $attendance_data = [];
        $dataSet = [];

        $dailyShiftData = WorkShift::where('work_shift_id', $shift->$day)->first();

        $shiftStartTime = $date . ' ' . $dailyShiftData->start_time;
        $shiftEndTime = $date . ' ' . $dailyShiftData->end_time;

        if ($dailyShiftData->start_time > $dailyShiftData->end_time) {
            $nature = 'Night';
            $fdatetime = date('Y-m-d H:i:s', strtotime('-1 hours', strtotime($shiftStartTime)));
            $tdatetime = date('Y-m-d H:i:s', strtotime('+1 days +4 hours', strtotime($shiftEndTime)));
        } else {
            $nature = 'Day';
            $fdatetime = date('Y-m-d H:i:s', strtotime('-1 hours', strtotime($shiftStartTime)));
            $tdatetime = date('Y-m-d H:i:s', strtotime('+4 hours', strtotime($shiftEndTime)));
        }

        $results = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
            ->where('ID', $finger_id)->get();

        if (count($results) == 1) {
            $inTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->min('datetime');
        } else {
            $inTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->min('datetime');
            $outTime = DB::table('ms_sql')->whereRaw("datetime >= '" . $fdatetime . "' AND datetime <= '" . $tdatetime . "'")
                ->where('ID', $finger_id)->max('datetime');
        }

        if ($inTime != null && isset($outTime)) {

            $working_time = $this->workingtime($inTime, $outTime);
            $hour = explode(':', $working_time);

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => date('Y-m-d H:i:s', strtotime($inTime)),
                'out_time' => date('Y-m-d H:i:s', strtotime($outTime)),
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => $working_time,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => null,
                'attendance_status' => null,
                'in_out_time' => date('d/m/y H:i', strtotime($inTime)) . ":" . 'IN' . ', ' . date('d/m/y H:i', strtotime($outTime)) . ":" . 'OUT',
            ];

            $attendance_data = $this->reportDataFormat($rawData);
            $dataSet = $this->overtimeLateEarlyCalc($attendance_data);
            $dataSet['over_time'] = $this->timeCalc($attendance_data);
        } elseif ($inTime != null) {

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => date('Y-m-d H:i:s', strtotime($inTime)),
                'out_time' => null,
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => null,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => null,
                'attendance_status' => AttendanceStatus::$ONETIMEINPUNCH,
                'in_out_time' => date('d/m/y H:i', strtotime($inTime)) . ":" . 'IN',
            ];

            $dataSet = $this->reportDataFormat($rawData);
        } else {

            $rawData = [
                'date' => date('Y-m-d', strtotime($date)),
                'finger_print_id' => $finger_id,
                'in_time' => null,
                'out_time' => null,
                'shift_name' => shiftList()[$shift->$day],
                'work_shift_id' => $shift->$day,
                'working_time' => null,
                'working_hour' => null,
                'device_name' => null,
                'over_time' => null,
                'attendance_status' => AttendanceStatus::$ABSENT,
                'in_out_time' => null,
            ];

            $dataSet = $this->reportDataFormat($rawData);
        }

        return $dataSet;
    }

    public function reportDataFormat($data)
    {
        $attendance_data = [];
        $dataSet = [];

        $attendance_data['date'] = $data['date'];
        $attendance_data['finger_print_id'] = $data['finger_print_id'];
        $attendance_data['in_time'] = $data['in_time'];
        $attendance_data['out_time'] = $data['out_time'];
        $attendance_data['shift_name'] = $data['shift_name'];
        $attendance_data['work_shift_id'] = $data['work_shift_id'];
        $attendance_data['working_time'] = $data['working_time'];
        $attendance_data['working_hour'] = $data['working_hour'];
        $attendance_data['device_name'] = $data['device_name'];
        $attendance_data['over_time'] = $data['over_time'];
        $attendance_data['in_out_time'] = $data['in_out_time'];
        $attendance_data['attendance_status'] = $data['attendance_status'];
        $attendance_data['early_by'] = isset($data['early_by']) ? $data['early_by'] : null;
        $attendance_data['late_by'] = isset($data['late_by']) ? $data['late_by'] : null;
        $attendance_data['status'] = GeneralStatus::$OKEY;
        $attendance_data['created_at'] = date('Y-m-d H:i:s');
        $attendance_data['updated_at'] = date('Y-m-d H:i:s');
        $attendance_data['created_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;
        $attendance_data['updated_by'] = isset(auth()->user()->user_id) ? auth()->user()->user_id : null;

        $dataSet = $attendance_data;

        return $dataSet;
    }

    public function overtimeLateEarlyCalc($data_format)
    {
        $dataSet = [];
        $tempArray = [];

        if ($data_format != []) {
            // find employee early or late time and shift name
            if (isset($data_format['work_shift_id']) && $data_format['work_shift_id'] != null) {

                $shift_list = DB::table('work_shift')->where('work_shift_id', $data_format['work_shift_id'])->first();
                if (!$shift_list) {
                    $shift_list = DB::table('work_shift')->orderBy('start_time')->first();
                }

                $login_time = date('H:i:s', \strtotime($data_format['in_time']));
                $in_datetime = new DateTime($data_format['in_time']);
                $start_datetime = new DateTime($data_format['date'] . ' ' . $shift_list->start_time);
                $late_count_time = date('H:i', strtotime($shift_list->late_count_time));
                if ($in_datetime >= $start_datetime) {

                    $interval = $in_datetime->diff($start_datetime);
                    $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                    $tempArray['work_shift_id'] = $shift_list->work_shift_id;
                    $tempArray['shift_name'] = $shift_list->shift_name;
                    $tempArray['start_time'] = $shift_list->start_time;
                    $tempArray['end_time'] = $shift_list->end_time;
                    $tempArray['early_by'] = null;
                    $tempArray['late_by'] = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                } elseif ($in_datetime <= $start_datetime) {

                    $interval = $start_datetime->diff($in_datetime);
                    $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                    $tempArray['work_shift_id'] = $shift_list->work_shift_id;
                    $tempArray['shift_name'] = $shift_list->shift_name;
                    $tempArray['start_time'] = $shift_list->start_time;
                    $tempArray['end_time'] = $shift_list->end_time;
                    $tempArray['early_by'] = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                    $tempArray['late_by'] = null;
                }
            } else {
                $emp = DB::table('employee')->select('branch_id')->where('finger_id', $data_format['finger_print_id'])->first();
                $shift_list = DB::table('work_shift')->where('branch_id', $emp->branch_id)->orderBy('start_time', 'ASC')->get();
                if (!$shift_list) {
                    $shift_list = DB::table('work_shift')->orderBy('start_time', 'ASC')->get();
                }
                if (isset($data_format['in_time']) && $data_format['in_time'] != null) {
                    $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                    $tempArray['work_shift_id'] = null;
                    $tempArray['shift_name'] = null;
                    $tempArray['start_time'] = null;
                    $tempArray['end_time'] = null;
                    $tempArray['early_by'] = null;
                    $tempArray['late_by'] = null;

                    $interval = ShiftConstant::$SHIFT_BUFFER_INT;

                    $retry = 1;
                    while ($tempArray['work_shift_id'] == null) {
                        foreach ($shift_list as $key => $value) {

                            $in_time = new DateTime($data_format['in_time']);
                            $login_time = date('H:i:s', \strtotime($data_format['in_time']));
                            $start_time = new DateTime($data_format['date'] . ' ' . $value->start_time);
                            $late_count_time = new DateTime($data_format['date'] . ' ' . $value->late_count_time);
                            $late_time = $late_count_time->diff($start_time);
                            $late_time = $late_time->format('%H') . ":" . $late_time->format('%I') . ":" . $late_time->format('%S');

                            $buffer_start_time = Carbon::createFromFormat('H:i:s', $value->start_time)->subMinutes($interval)->format('H:i:s');
                            $buffer_end_time = Carbon::createFromFormat('H:i:s', $value->start_time)->addMinutes($interval)->format('H:i:s');
                            $emp_shift = $this->shift_timing_array($login_time, $buffer_start_time, $buffer_end_time);

                            if ($emp_shift == \true) {
                                if ($in_time >= $start_time) {
                                    $interval = $in_time->diff($start_time);
                                    $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                                    $tempArray['work_shift_id'] = $value->work_shift_id;
                                    $tempArray['shift_name'] = $value->shift_name;
                                    $tempArray['start_time'] = $value->start_time;
                                    $tempArray['end_time'] = $value->end_time;
                                    $late_by = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                                    $tempArray['late_by'] = strtotime($late_by) > strtotime($late_time) ? $late_by : null;
                                } elseif ($in_time <= $start_time) {
                                    $interval = $start_time->diff($in_time);
                                    $tempArray['finger_print_id'] = $data_format['finger_print_id'];
                                    $tempArray['work_shift_id'] = $value->work_shift_id;
                                    $tempArray['shift_name'] = $value->shift_name;
                                    $tempArray['start_time'] = $value->start_time;
                                    $tempArray['end_time'] = $value->end_time;
                                    $early_by = $interval->format('%H') . ":" . $interval->format('%I') . ":" . $interval->format('%S');
                                    $tempArray['early_by'] = strtotime($early_by) > strtotime($late_time) ? $early_by : null;
                                }

                                break;
                            } else {
                                $interval += 30; //30
                            }
                            if ($retry > 23) {
                                break;
                            }
                            $retry++;
                        }

                        if ($retry > 23) {
                            break;
                        }
                    }
                }
            }


            // find employee over time
            if (isset($tempArray['work_shift_id']) && $tempArray['work_shift_id'] != null && isset($data_format['working_time']) && $data_format['working_time'] != null) {
                $shiftStartTime = new DateTime(date('H:i:s', strtotime($tempArray['start_time'])));
                $shiftEndTime = new DateTime(date('H:i:s', strtotime($tempArray['end_time'])));
                $shiftEndTimeForAtt = new DateTime(date('H:i:s', strtotime('-5 minutes', strtotime($tempArray['end_time']))));
                $shiftEndTimeForAtt = date('H:i:s', strtotime('-5 minutes', strtotime($tempArray['end_time'])));

                if ($shiftStartTime < $shiftEndTime) {
                    $employeeOutTime = new DateTime(date('H:i:s', strtotime($data_format['out_time'])));
                } else {
                    $endDate = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($data_format['date'] . ' ' . $tempArray['end_time'])));
                    $shiftEndTime = new DateTime(date('Y-m-d H:i:s', strtotime($endDate)));
                    $employeeOutTime = new DateTime($data_format['out_time']);
                    $employeeOutTime = date('H:i:s', strtotime($data_format['out_time']));
                }

                if ($employeeOutTime >= $shiftEndTimeForAtt) {
                    $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                } else {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                }

                if ($employeeOutTime > $shiftEndTime) {

                    $over_time = $shiftEndTime->diff($employeeOutTime);
                    $tempArray['over_time'] = $this->check_overtime($over_time);
                } else {
                    $tempArray['over_time'] = null;
                }
            } else if (!isset($tempArray['work_shift_id']) || $tempArray['work_shift_id'] == null) {

                $workingTime = new DateTime($data_format['working_time']);
                $naShiftDuration = new DateTime(ShiftConstant::$NA_OVERTIME_HOUR_TIME);

                if ($workingTime >= $naShiftDuration) {
                    $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                } else if ($workingTime >= $naShiftDuration) {
                    $tempArray['attendance_status'] = AttendanceStatus::$LESSHOURS;
                }

                if ($workingTime > $naShiftDuration) {

                    $over_time = $naShiftDuration->diff($workingTime);

                    $tempArray['over_time'] = $this->check_overtime($over_time);
                } else {
                    $tempArray['over_time'] = null;
                }
            }
            // over time save in store method place
            unset($tempArray['over_time']);
            $dataSet = array_merge($data_format, $tempArray);
            unset($dataSet['start_time']);
            unset($dataSet['end_time']);
            return $dataSet;
        }
    }

    public function check_overtime($over_time)
    {
        $roundMinutes = (int) $over_time->i >= OvertimeStatus::$OT_MIN_START_INT ? OvertimeStatus::$OT_MIN_START_INT : '00';
        $roundHours = (int) $over_time->h >= OvertimeStatus::$OT_HOUR_START_INT ? sprintf("%02d", ($over_time->h)) : '00';

        if ($over_time->h >= OvertimeStatus::$OT_HOUR_START_INT) {
            $overtime = $roundHours . ':' . $roundMinutes;
        } else {
            $overtime = null;
        }
        return $overtime;
    }

    public function over_time($working_time, $shift_time)
    {
        $workingTime = new DateTime($working_time);
        $actualTime = new DateTime($shift_time);
        $overTime = null;

        if ($workingTime > $actualTime) {
            $over_time = $actualTime->diff($workingTime);
            $roundMinutes = (int) $over_time->i >= 30 ? '30' : '00';
            $roundHours = (int) $over_time->h >= 1 ? sprintf("%02d", ($over_time->h)) : '00';

            if ($over_time->h >= 1) {
                $overTime = $roundHours . ':' . $roundMinutes;
            }
        }

        return $overTime;
    }

    public function in_out_time($array)
    {
        $result = [];
        $count = count($array);

        foreach ($array as $key => $value) {
            if ($key == 0) {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'IN';
            } elseif ($key == ($count - 1)) {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'OUT';
            } else {
                $result[] = date('d/m/y H:i', strtotime($value->datetime)) . ':' . 'BTW';
            }
        }

        $str = json_encode($result);
        $str = str_replace('[', '', $str);
        $str = str_replace(']', '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace("\/", '/', $str);

        return $str;
    }

    public function calculate_hours_mins($datetime1, $datetime2)
    {
        $interval = $datetime1->diff($datetime2);
        return $interval->format('%h') . ":" . $interval->format('%i') . ":" . $interval->format('%s');
    }

    public function calculate_total_working_hours($at)
    {
        $total_seconds = 0;
        for ($i = 0; $i < count($at); $i++) {
            $seconds = 0;
            $timestr = $at[$i]['subtotalhours'];

            $parts = explode(':', $timestr);

            $seconds = ($parts[0] * 60 * 60) + ($parts[1] * 60) + $parts[2];
            $total_seconds += $seconds;
        }
        return gmdate("H:i:s", $total_seconds);
    }

    public function find_closest_time($dates, $first_in)
    {

        function closest($dates, $findate)
        {
            $newDates = array();

            foreach ($dates as $date) {
                $newDates[] = strtotime($date);
            }

            sort($newDates);

            foreach ($newDates as $a) {
                if ($a >= strtotime($findate)) {
                    return $a;
                }
            }
            return end($newDates);
        }

        $values = closest($dates, date('Y-m-d H:i:s', \strtotime($first_in)));
    }

    public function shift_timing_array($in_time, $start_shift, $end_shift)
    {
        $shift_status = $in_time <= $end_shift && $in_time >= $start_shift;
        return $shift_status;
    }

    public function workingtime($from, $to)
    {
        $date1 = new DateTime($to);
        $date2 = $date1->diff(new DateTime($from));
        $hours = ($date2->days * 24);
        $hours = $hours + $date2->h;

        return $hours . ":" . sprintf('%02d', $date2->i) . ":" . sprintf('%02d', $date2->s);
    }

    public function ifCompanyHoliday($compHolidays, $date)
    {

        $comp_holidays = [];
        foreach ($compHolidays as $holidays) {
            $start_date = $holidays->fdate;
            $end_date = $holidays->tdate;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $comp_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($comp_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }

        return false;
    }

    public function ifHoliday($govtHolidays, $date)
    {
        $ph = [];

        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $ph[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($ph as $val) {
            if ($val == $date) {
                return true;
            }
        }
        return false;
    }

    public function ifEmployeeWasLeave($leave, $employee_id, $date)
    {

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
                    $leaveRecord[] = $temp;
                    $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                }
            }
        }

        foreach ($leaveRecord as $val) {
            if (($val['employee_id'] == $employee_id && $val['date'] == $date)) {
                return $val['leave_type_name'];
            }
        }

        return false;
    }
}
