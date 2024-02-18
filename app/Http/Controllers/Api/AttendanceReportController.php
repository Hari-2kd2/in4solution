<?php

namespace App\Http\Controllers\Api;

use DateTime;
use App\Model\MsSql;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\WeeklyHoliday;
use Illuminate\Http\Request;
use App\Model\PrintHeadSetting;
use App\Model\EmployeeAttendance;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Lib\Enumerations\LeaveStatus;
use App\Lib\Enumerations\AttendanceStatus;
use App\Repositories\AttendanceRepository;


class AttendanceReportController extends Controller
{
    protected $apiAttendanceRepository;
    protected $controller;
    protected $AttendanceRepository;

    public function __construct(Controller $controller, AttendanceRepository $AttendanceRepository)
    {
        // $this->apiAttendanceRepository = $AttendanceRepository;
        $this->controller = $controller;
        $this->AttendanceRepository = $AttendanceRepository;
    }



    public function myAttendanceReport(Request $request)
    {


        try {
            $emp_id = $request->employee_id;
            $employee = Employee::findOrFail($emp_id);
            $results      = [];
            $to_date = date('Y-m-d', strtotime($request->date .  '+1 days'));
            $from_date = operateDays(date('Y-m-d'), 90, '-'); // restrict last 90 days only can view
            if($request->date<$from_date) {
                return $this->controller->custom_error("Attendance report can be view from " . dateConvertDBtoForm($from_date));
            }
            $results = MsSql::where('ID', $employee->finger_id)->whereBetween('datetime', [date('Y-m-d H:i:s', strtotime($request->date . ' 05:00:00')), date('Y-m-d H:i:s', strtotime($to_date . ' 08:00:00'))])->orderByDesc('datetime')->orderByDesc('primary_id')->get();
            if (!empty($results)) {
                return $this->success("Attendacne details received successfully", $results);
            } else {
                return $this->error();
            }
        } catch (\Throwable $th) {
            info($th->getMessage());
        }
    }

    public function monthlyDropdownList(Request $request) {
        $monthlyDropdownList['year_month'] = monthlyDropdownList();
        return $this->success("Monthly dropdown list received successfully", $monthlyDropdownList);
    }

    // for API /attendance/monthly_attendance?employee_id=2476&year_month=2023-11
    public function findAttendanceSummaryReport(Request $request)
    {
        $employee_id = $request->employee_id;
        $year_month = $request->year_month;

        $date = new DateTime($year_month.'-01');
        $start_date = $date->format('Y-m-d');
        $date->modify('last day of this month');
        $end_date = $date->format('Y-m-d');
        $data = findFromDateToDateToAllDate($start_date, $end_date);
        $Employee = Employee::find($employee_id);
        $attendance = DB::table('view_employee_in_out_data')->select('finger_print_id', 'date', 'in_time', 'shift_name', 'inout_status', 'out_time', 'working_time', 'halfday_status', 'comp_off_status')->whereBetween('date', [$start_date, $end_date])->where('finger_print_id', $Employee->finger_id)->get();

        $employee = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'employee.updated_at', 'gender', 'status', 'department_name',  'designation_name', 'finger_id', 'employee_id', 'branch.branch_id', 'branch.branch_name')
        ->join('designation', 'designation.designation_id', 'employee.designation_id')
        ->join('department', 'department.department_id', 'employee.department_id')
        ->join('branch', 'branch.branch_id', 'employee.branch_id')
        ->orderBy('department.department_id', 'ASC')
        ->where('status', UserStatus::$ACTIVE)
        ->where('employee.employee_id', $employee_id)->first();


        $leave = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name', 'leave_type.leave_type_id')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $employee_id)
            ->get();
            
            
        $rHleave = DB::table('restricted_holiday_application')->select('restricted_holiday_application.holiday_date', 'restricted_holiday_application.holiday_id', 'employee_id', 'holiday_name')
            ->join('holiday_restricted', 'holiday_restricted.holiday_id', 'restricted_holiday_application.holiday_id')
            ->whereRaw("restricted_holiday_application.holiday_date >= '" . $start_date . "' and restricted_holiday_application.holiday_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $employee_id)
            ->get();


        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        $dataFormat = [];
        $tempArray = [];
        $tempAllArray = [];

            $activeUser = $employee->status;
            $leftUser = $employee->status;
            $weeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();
            $tempAllArray['employeeInfo'] = $employee;
            $tempAllArray['attendance_status_legend'] = $this->AttendanceRepository->AttendanceStatusLegend;
            foreach ($data as $key => $value) {
                $leftDate = date('Y-m-d', strtotime($employee->updated_at));
                $hasAttendance = $this->AttendanceRepository->hasEmployeeAttendance($attendance, $employee->finger_id, $value['date']);
                $tempArray['date'] = $value['date'];
                $tempArray['day'] = $value['day'];
                $tempArray['day_name'] = $value['day_name'];

                $tempArray['halfday_status'] = $hasAttendance['halfday_status'] ?? '';
                $tempArray['comp_off_status'] = $hasAttendance['comp_off_status'] ?? '';

                if ($hasAttendance['status'] == true) {
                    $ifHoliday = $this->AttendanceRepository->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                    if ($ifHoliday['weekly_holiday'] == true) {
                        $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } elseif ($ifHoliday['govt_holiday'] == true) {
                        $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                        $tempArray['gov_day_worked'] = 'yes';
                        $tempArray['leave_type'] = '';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    } else {
                        $tempArray['attendance_status'] = AttendanceStatus::$PRESENT;
                        $tempArray['leave_type'] = '';
                        $tempArray['gov_day_worked'] = 'no';
                        $tempArray['shift_name'] = $hasAttendance['shift_name'];
                        $tempArray['inout_status'] = $hasAttendance['inout_status'];
                    }
                } else {

                    $hasLeave = $this->AttendanceRepository->ifEmployeeWasLeave($leave, $employee->employee_id, $value['date'], $rHleave);
                    // $hasOD = $this->AttendanceRepository->ifEmployeeWasOD($od, $employee->employee_id, $value['date']);
                    $ifApplyLeaveOnHoliday = $this->AttendanceRepository->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);

                    if ($hasLeave) {
                        if ($ifApplyLeaveOnHoliday['weekly_holiday'] == true) {
                            $tempArray['attendance_status'] = AttendanceStatus::$WEEKOFF;
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } elseif ($ifApplyLeaveOnHoliday['govt_holiday'] == true) {
                            $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = '';
                            $tempArray['shift_name'] = '';
                            $tempArray['inout_status'] = '';
                        } else {
                            $tempArray['inout_status'] = '';
                            $tempArray['attendance_status'] = AttendanceStatus::$LEAVE;
                            $tempArray['gov_day_worked'] = 'no';
                            $tempArray['leave_type'] = $hasLeave;
                            $tempArray['leave_name'] = $this->AttendanceRepository->leave_name;
                            $tempArray['leave_type_id'] = $this->AttendanceRepository->leave_type_id;
                            $tempArray['shift_name'] = '';
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
                            $ifHoliday = $this->AttendanceRepository->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
                            if ($ifHoliday['weekly_holiday'] == true) {
                                $tempArray['attendance_status'] = AttendanceStatus::$WEEKOFF;
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } elseif ($ifHoliday['govt_holiday'] == true) {
                                $tempArray['attendance_status'] = AttendanceStatus::$HOLIDAY;
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            } else {
                                $tempArray['attendance_status'] = AttendanceStatus::$ABSENT;
                                $tempArray['gov_day_worked'] = 'no';
                                $tempArray['leave_type'] = '';
                                $tempArray['shift_name'] = '';
                                $tempArray['inout_status'] = '';
                            }
                        }
                    }
                }

                if($value['date']==date('Y-m-d')) {
                    $tempArray['attendance_label'] = '';
                } else if(isset($tempArray['attendance_status']) && $this->AttendanceRepository->AttendanceStatusApi[$tempArray['attendance_status']]) {
                    $tempArray['attendance_label'] = '';
                    if($tempArray['halfday_status']==0.5) {
                        $tempArray['attendance_label'] = 'HD';
                    } else {
                        $tempArray['attendance_label'] = $this->AttendanceRepository->AttendanceStatusApi[$tempArray['attendance_status']];
                    }
                }

                $dataFormat['month_attendance'][] = $tempArray;
            }


        return $this->success("Monthly attendacne details received successfully", $dataFormat);
    }


    public function downloadMyAttendance(Request $request)
    {

        // $request->validate([
        //     'employee_id' => 'required',
        //     'from_date' => 'required',
        //     'to_date' => 'required',
        // ]);

        $emp_id = $request->employee_id;
        $from_date = dateConvertFormtoDB($request->from_date);
        $to_date =  dateConvertFormtoDB($request->to_date);
        $employeeInfo = Employee::with('department')->where('employee_id', $emp_id)->first();
        $printHead    = PrintHeadSetting::first();
        $results      = $this->AttendanceRepository->getEmployeeMonthlyAttendance($from_date, $to_date, $emp_id);
        if (isset($results['status'])) {
            return $results['error'];
        }
        $data         = [
            'results'         => $results,
            'form_date'       => dateConvertFormtoDB($request->from_date),
            'to_date'         => dateConvertFormtoDB($request->to_date),
            'printHead'       => $printHead,
            'employee_name'   => $employeeInfo->first_name . ' ' . $employeeInfo->last_name,
            'department_name' => $employeeInfo->department->department_name,
        ];

        $pdf = PDF::loadView('admin.attendance.report.pdf.mySummaryReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("my-attendance.pdf");
    }

    public function attendanceSummaryReport(Request $request)
    {
        if ($request->month) {
            $month = $request->month;
        } else {
            $month = date("Y-m");
        }

        $monthAndYear = explode('-', $month);
        $month_data   = $monthAndYear[1];
        $dateObj      = DateTime::createFromFormat('!m', $month_data);
        $monthName    = $dateObj->format('F');

        $monthToDate = findMonthToAllDate($month);
        $leaveType   = LeaveType::get();
        $result      = $this->AttendanceRepository->findAttendanceSummaryReport($month);

        return view('admin.attendance.report.summaryReport', ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'leaveTypes' => $leaveType, 'monthName' => $monthName]);
    }

    public function downloadAttendanceSummaryReport($month)
    {
        $monthToDate = findMonthToAllDate($month);
        $leaveType   = LeaveType::get();
        $result      = $this->AttendanceRepository->findAttendanceSummaryReport($month);

        $monthAndYear = explode('-', $month);
        $month_data   = $monthAndYear[1];
        $dateObj      = DateTime::createFromFormat('!m', $month_data);
        $monthName    = $dateObj->format('F');

        $data = [
            'results'     => $result,
            'month'       => $month,
            'monthToDate' => $monthToDate,
            'leaveTypes'  => $leaveType,
            'monthName'   => $monthName,
        ];
        $pdf = PDF::loadView('admin.attendance.report.pdf.attendanceSummaryReportPdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("attendance-summaryReport.pdf");
    }
}
