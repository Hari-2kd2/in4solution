<?php

namespace App\Http\Controllers\Attendance;

use Carbon\Carbon;
use App\Model\CompOff;
use App\Model\Employee;
use App\Model\IpSetting;
use Carbon\CarbonPeriod;
use App\Model\Department;
use App\Model\ExcelEmployee;
use App\Model\WhiteListedIp;
use Illuminate\Http\Request;
use App\Model\ManualAttendance;
use App\Model\EmployeeInOutData;
use App\Model\EmployeeAttendance;
use Illuminate\Support\Facades\DB;
use App\Model\ManualAttendanceCase;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\EmployeeInOutDataCase;
use App\Repositories\CommonRepository;
use App\Lib\Enumerations\AttendanceStatus;
use App\Repositories\AttendanceRepository;
use Illuminate\Support\Facades\Request as RequestIP;

class ManualAttendanceController extends Controller
{
    protected $generateReportController;
    protected $attendanceRepository;
    protected $commonRepository;

    public function __construct(GenerateReportController $generateReportController, AttendanceRepository $attendanceRepository, CommonRepository $commonRepository)
    {
        $this->generateReportController = $generateReportController;
        $this->attendanceRepository = $attendanceRepository;
        $this->commonRepository = $commonRepository;
    }

    public function manualAttendance()
    {
        $departmentList = Department::get();
        return view('admin.attendance.manualAttendance.index', ['departmentList' => $departmentList]);
    }

    public function filterData(Request $request)
    {

        $data = dateConvertFormtoDB($request->get('date'));
        if (!$data) {
            return redirect('manualAttendance')->with('error', 'Manual attendance update required date field.');
        }
        $department     = $request->get('department_id');
        $departmentList = Department::get();
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $rawSql = ' employee.employee_id > 1 ';

        if ($department) {
            $rawSql .= " AND employee.department_id='$department' ";
        }
        if ($roleId == 1) {
            // dd('data', $data);
            $attendanceData = Employee::select(
                'employee.finger_id',
                'employee.employee_id',
                DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) as fullName'),
                DB::raw('(SELECT DATE_FORMAT(MIN(view_employee_in_out_data.in_time), \'%Y-%m-%d %H:%i:%s\')  FROM view_employee_in_out_data WHERE view_employee_in_out_data.date = "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS inTime'),
                DB::raw('(SELECT DATE_FORMAT(MAX(view_employee_in_out_data.out_time), \'%Y-%m-%d %H:%i:%s\') FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS outTime'),
                DB::raw('(SELECT view_employee_in_out_data.employee_attendance_id FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS employee_attendance_id'),
                DB::raw('(SELECT view_employee_in_out_data.created_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdBy'),
                DB::raw('(SELECT view_employee_in_out_data.updated_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedBy'),
                DB::raw('(SELECT view_employee_in_out_data.created_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdAt'),
                DB::raw('(SELECT view_employee_in_out_data.updated_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedAt'),
                DB::raw('(SELECT view_employee_in_out_data.shift_name FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS shiftName'),
                DB::raw('(SELECT view_employee_in_out_data.working_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS workingTime'),
                DB::raw('(SELECT view_employee_in_out_data.over_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS overTime'),
                DB::raw('(SELECT view_employee_in_out_data.early_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS earlyBy'),
                DB::raw('(SELECT view_employee_in_out_data.late_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS lateBy')
            )
                ->where('employee.status', UserStatus::$ACTIVE)
                ->orderBy('finger_id')
                ->whereRaw($rawSql)
                ->get();
            return view('admin.attendance.manualAttendance.index', ['departmentList' => $departmentList, 'attendanceData' => $attendanceData, 'results' => []]);
        } else {
            $LoggedEmployee = Employee::loggedEmployee();
            $supervisorIds = $LoggedEmployee->supervisorIds();
            $attendanceData = Employee::select(
                'employee.finger_id',
                'employee.employee_id',
                DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) as fullName'),
                DB::raw('(SELECT DATE_FORMAT(MIN(view_employee_in_out_data.in_time), \'%Y-%m-%d %H:%i:%s\')  FROM view_employee_in_out_data WHERE view_employee_in_out_data.date = "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS inTime'),
                DB::raw('(SELECT DATE_FORMAT(MAX(view_employee_in_out_data.out_time), \'%Y-%m-%d %H:%i:%s\') FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id ) AS outTime'),
                DB::raw('(SELECT view_employee_in_out_data.employee_attendance_id FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS employee_attendance_id'),
                DB::raw('(SELECT view_employee_in_out_data.created_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdBy'),
                DB::raw('(SELECT view_employee_in_out_data.updated_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedBy'),
                DB::raw('(SELECT view_employee_in_out_data.created_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS createdAt'),
                DB::raw('(SELECT view_employee_in_out_data.updated_at FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS updatedAt'),
                DB::raw('(SELECT view_employee_in_out_data.shift_name FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS shiftName'),
                DB::raw('(SELECT view_employee_in_out_data.working_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS workingTime'),
                DB::raw('(SELECT view_employee_in_out_data.over_time FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS overTime'),
                DB::raw('(SELECT view_employee_in_out_data.early_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS earlyBy'),
                DB::raw('(SELECT view_employee_in_out_data.late_by FROM view_employee_in_out_data WHERE view_employee_in_out_data.date =  "' . $data . '" AND view_employee_in_out_data.finger_print_id = employee.finger_id) AS lateBy')
            )
                ->whereIn('employee.employee_id', $supervisorIds)
                ->where('employee.status', UserStatus::$ACTIVE)
                ->where('employee.branch_id', $branchId)
                ->orderBy('finger_id')
                ->whereRaw($rawSql)
                ->get();

            return view('admin.attendance.manualAttendance.index', ['departmentList' => $departmentList, 'attendanceData' => $attendanceData, 'results' => []]);
        }
    }


    public function individualReport(Request $request)
    {
        try {

            info(array_merge(['user' => auth()->user()->user_id, 'timestamp' => date('Y-m-d H:i:s')], $request->all()));
            $recompute = false;
            $manual = true;

            $delete = ManualAttendance::where('ID', $request->finger_print_id)->whereBetween('datetime', [date("Y-m-d H:i:s", strtotime($request->in_time)), date("Y-m-d H:i:s", strtotime($request->out_time))])->delete();
            info($delete);

            $inData = [
                'ID' => $request->finger_print_id,
                'type' => 'IN',
                'datetime' => $request->in_time ? date("Y-m-d H:i:s", strtotime($request->in_time)) : null,
                'updated_by' => auth()->user()->user_id ?? null,
                'created_by' => auth()->user()->user_id ?? null,
                'status' => $delete == 0 ? 0 : 1,
            ];

            $outData = [
                'ID' => $request->finger_print_id,
                'type' => 'OUT',
                'datetime' => $request->out_time ? date("Y-m-d H:i:s", strtotime($request->out_time)) : null,
                'updated_by' => auth()->user()->user_id ?? null,
                'created_by' => auth()->user()->user_id ?? null,
                'status' => $delete == 0 ? 0 : 1,
            ];

            ManualAttendance::createTrait($inData);
            ManualAttendance::createTrait($outData);

            $results = $this->generateReportController->generateManualAttendanceReport($request->finger_print_id, date('Y-m-d', strtotime($request->in_time)), date('Y-m-d H:i:s', strtotime($request->in_time)), date('Y-m-d H:i:s', strtotime($request->out_time)), $manual, $recompute);

            echo $results ? 'success' : 'error';
        } catch (\Throwable $th) {
            info($th);
            echo $th->getMessage();
        }
    }

    public function store(Request $request)
    {
        $fulldate = new \DateTime(monthConvertFormtoDB($request->date));
        $manualDate = dateConvertFormtoDB($request->date);
        $datePeriod = CarbonPeriod::create($manualDate, $manualDate);
        $startDate = $datePeriod->startDate->format('Y-m-d');
        $endDate = $datePeriod->endDate->format('Y-m-d');


        try {
            DB::beginTransaction();

            $selected_date = dateConvertFormtoDB($request->get('date'));
            $finger_print_ids = $request->get('finger_print_id');
            $recompute = false;
            $manual = true;
            $counter = 0;
            $post = $_POST; // don't remove
            foreach ($finger_print_ids as $key => $finger_print_id) {
                $counter++;
                $ManualAttendanceIn = ManualAttendance::where('manual_date', $selected_date)->where('ID', $finger_print_id)->where('type', 'IN')->first();
                $ManualAttendanceOut = ManualAttendance::where('manual_date', $selected_date)->where('ID', $finger_print_id)->where('type', 'OUT')->first();
                $Employee = Employee::where('finger_id', $finger_print_id)->first() ?? new Employee;
                $attendanceData = EmployeeInOutData::where('finger_print_id', $finger_print_id)->where('date', $selected_date)->first();

                if (isset($post['inTime'][$key]) && isset($post['outTime'][$key])) {
                    $InTime = $post['inTime'][$key] ? $selected_date . ' ' . $post['inTime'][$key] : null;
                    $OutTime = $post['outTime'][$key] ? $selected_date . ' ' . $post['outTime'][$key] : null;

                    $formInMinute = $InTime ? date('Y-m-d H:i', strtotime($InTime)) : null;
                    $formOutMinute = $OutTime ? date('Y-m-d H:i', strtotime($OutTime)) : null;

                    $MinuteIN = $InTime ? date('H:i', strtotime($InTime)) : null;
                    $MinuteOUT = $OutTime ? date('H:i', strtotime($OutTime)) : null;
                    $dbInMinute = null;
                    $dbOutMinute = null;
                    if ($attendanceData) {
                        $dbInMinute = $attendanceData->in_time ? date('Y-m-d H:i', strtotime($attendanceData->in_time)) : null;
                        $dbOutMinute = $attendanceData->out_time ? date('Y-m-d H:i', strtotime($attendanceData->out_time)) : null;
                    }

                    // remove manual attendance if attendanceData found and manual
                    if ((!$MinuteIN || $MinuteIN == '00:00') && (!$MinuteOUT || $MinuteOUT == '00:00')) {
                        // dd($ManualAttendanceIn, $ManualAttendanceOut, $attendanceData);
                        if (($ManualAttendanceIn || $ManualAttendanceOut) && $attendanceData) {
                            $IN_TIME = DB::table('ms_sql')->whereDate("datetime", $selected_date)->where('type', 'IN')->where('ID', $finger_print_id)->min('datetime');
                            $OUT_TIME = DB::table('ms_sql')->whereDate("datetime", $selected_date)->where('type', 'OUT')->where('ID', $finger_print_id)->max('datetime');

                            if ($finger_print_id == 'T0001') {
                                // dd('IN_TIME', $IN_TIME, 'OUT_TIME', $OUT_TIME, 'attendanceData', $attendanceData->getAttributes());
                            }
                            // in case of manaual attendance have get comp off the should be delete (get back the comp off entry and employee leaves table)
                            // we have done alreday freezed month can not use manual attendance, so that we definitely delete if comp off found
                            $employee_id = $Employee->employee_id;
                            $CompOff = CompOff::where('employee_id', $Employee->employee_id)->where('working_date', $selected_date)->first();
                            // not credits or un-used status will be every generate attendance update
                            if ($CompOff) {
                                $EmployeeLeaves = $Employee->EmployeeLeaves;
                                if ($CompOff->status == 0) {
                                    $EmployeeLeaves->comp_off = $EmployeeLeaves->comp_off - ($CompOff->off_days);
                                } elseif ($CompOff->status == 1) {
                                    $EmployeeLeaves->comp_off = $EmployeeLeaves->comp_off - ($CompOff->off_days - $CompOff->used_days);
                                }
                                $EmployeeLeaves->update();
                                $CompOff->delete();
                            }
                            if ($ManualAttendanceIn) {
                                $ManualAttendanceIn->delete();
                            }
                            if ($ManualAttendanceOut) {
                                $ManualAttendanceOut->delete();
                            }

                            $attendanceData->delete(); // delete the manual attendance time based calculation time, after delete generate once regular function it'll take from ms_sql once again
                            // after employee
                            $this->generateReportController->generateAttendanceReport($selected_date, $Employee->employee_id);
                            continue;
                        }
                    }

                    $InData = ['ID' => $finger_print_id, 'type' => 'IN', 'status' => 0, 'device_name' => 'Manual', 'branch_id' => $Employee->branch_id, 'devuid' => 'Manual', 'datetime' => $InTime, 'manual_date' => $InTime, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'updated_by' => auth()->user()->user_id, 'created_by' => auth()->user()->user_id,];
                    $outData = ['ID' => $finger_print_id, 'type' => 'OUT', 'status' => 0, 'device_name' => 'Manual', 'branch_id' => $Employee->branch_id, 'devuid' => 'Manual', 'datetime' => $OutTime, 'manual_date' => $OutTime, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'updated_by' => auth()->user()->user_id, 'created_by' => auth()->user()->user_id,];
                    if ($finger_print_id == 'T0001') {
                        // dd('InData', $InData, 'outData', $outData);
                    }
                    if ($InTime && $OutTime && ($dbInMinute != $formInMinute || $dbOutMinute != $formOutMinute)) {
                        if ($attendanceData) {
                            if ($InTime) {
                                $attendanceData->in_time = $InTime;
                            }
                            if ($OutTime) {
                                $attendanceData->out_time = $OutTime;
                            }
                            if ($finger_print_id == 'T0001') {
                                // dd('InData', $InData, 'outData', $outData, 'attendanceData', $attendanceData->getAttributes());
                            }
                            // the following fields are update by re-compute attendance
                            $attendanceData->attendance_status = null;
                            $attendanceData->halfday_status = null;
                            $attendanceData->comp_off_status = null;
                            $attendanceData->notes = null;
                            $attendanceData->working_time = null;
                            $attendanceData->working_hour = null;
                            $attendanceData->over_time = null;
                            $attendanceData->early_by = null;
                            $attendanceData->late_by = null;
                            $attendanceData->attendance_status = AttendanceStatus::$ABSENT;
                            $attendanceData->device_name = null;
                            $attendanceData->in_out_time = null;
                            $attendanceData->shift_name = null;
                            $attendanceData->work_shift_id = null;
                            $attendanceData->update();
                        }
                        if ($ManualAttendanceIn) {
                            $ManualAttendanceIn->update($InData);
                        } else {
                            ManualAttendanceCase::insert($InData);
                        }

                        if ($ManualAttendanceOut) {
                            $ManualAttendanceOut->update($outData);
                        } else {
                            ManualAttendanceCase::insert($outData);
                        }
                        $this->generateReportController->generateManualAttendanceReport($finger_print_id, dateConvertFormtoDB($request->date), $InTime, $OutTime, $manual, $recompute);
                    }
                }
            }
            DB::commit();
            return redirect('manualAttendance')->with('success', 'Manual attendance saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            $bug = $e->getMessage();
            return redirect('manualAttendance')->with('error', 'Something Error Found !, Please try again. ' . $bug);
        }
    }

    // ip attendance
    public function ipAttendance(Request $request)
    {

        try {

            $finger_id = $request->finger_id;
            $ip_check_status = $request->ip_check_status;
            $user_ip = RequestIP::ip();

            if ($ip_check_status == 0) {
                $att = new EmployeeAttendance;
                $att->finger_print_id = $finger_id;
                $att->in_out_time = date("Y-m-d H:i:s");
                $att->save();

                return redirect()->back()->with('success', 'Attendance updated.');
            } else {
                $check_white_listed = WhiteListedIp::where('white_listed_ip', '=', $user_ip)->count();

                if ($check_white_listed > 0) {

                    $att = new EmployeeAttendance;
                    $att->finger_print_id = $finger_id;
                    $att->in_out_time = date("Y-m-d H:i:s");
                    $att->save();

                    return redirect()->back()->with('success', 'Attendance updated.');
                } else {
                    return redirect()->back()->with('error', 'Invalid Ip Address.');
                }
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    // get to attendance ip setting page

    public function setupDashboardAttendance()
    {
        $ip_setting = IpSetting::orderBy('updated_at', 'desc')->first();
        $white_listed_ip = WhiteListedIp::all();

        return view('admin.attendance.setting.dashboard_attendance', [
            'ip_setting' => $ip_setting,
            'white_listed_ip' => $white_listed_ip,
        ]);
    }

    // post new attendance

    public function postDashboardAttendance(Request $request)
    {

        try {

            DB::beginTransaction();

            $setting = IpSetting::orderBy('id', 'desc')->first();

            $setting->status = $request->status;
            $setting->ip_status = $request->ip_status;
            $setting->update();

            if ($request->ip) {

                WhiteListedIp::orderBy('id', 'desc')->delete();
                foreach ($request->ip as $value) {

                    if ($value != '') {

                        $white_listed_ip = new WhiteListedIp;

                        $white_listed_ip->white_listed_ip = $value;

                        $white_listed_ip->save();
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Employee Attendance Setting Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
