<?php

namespace App\Http\Controllers\User;

use App\User;
use DateTime;
use App\Model\Role;
use App\Model\MsSql;
use App\Model\Branch;
use App\Model\Device;
use App\Model\Notice;
use App\Model\Warning;
use App\Model\Employee;
use App\Model\PayGrade;
use App\Model\LeaveType;
use App\Model\WorkShift;
use Carbon\CarbonPeriod;
use App\Model\Department;
use App\Components\Common;
use App\Model\Termination;
use App\Model\HourlySalary;
use Illuminate\Support\Str;
use App\Model\EmployeeAward;
use Illuminate\Http\Request;
use App\Model\DepartmentCase;
use App\Model\DesignationCase;
use App\Model\EmployeeProfile;
use App\Model\LeavePermission;
use App\Model\LeaveApplication;
use App\Model\EmployeeExperience;
use App\Model\EmployeePerformance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Lib\Enumerations\LeaveStatus;
use Illuminate\Support\Facades\Artisan;
use App\Repositories\EmployeeRepository;
use App\Repositories\AttendanceRepository;
use App\Model\EmployeeEducationQualification;
use App\Http\Controllers\Attendance\AttendanceReportController;
use App\Model\CompanyPolicy;

class HomeController extends Controller
{

    protected $employeeRepositories, $employeePerformance, $leaveApplication, $notice, $employeeExperience, $department, $employee, $employeeAward, $attendanceRepository, $warning, $termination;

    public function __construct(
        EmployeeRepository $employeeRepositories,
        EmployeePerformance $employeePerformance,
        LeaveApplication $leaveApplication,
        Department $department,
        Employee $employee,
        AttendanceRepository $attendanceRepository
    ) {
        $this->employeePerformance = $employeePerformance;
        $this->leaveApplication = $leaveApplication;
        $this->department = $department;
        $this->employee = $employee;
        $this->attendanceRepository = $attendanceRepository;
        $this->employeeRepositories = $employeeRepositories;
    }

    public function leaveInfo(Request $request, $id)
    {
        $Employee = Employee::findOrFail($id);
        if ($request->date_of_joining != '') {
            $Employee->date_of_joining = $request->date_of_joining;
        }
        return view('admin.user.user.leaveInfo', compact('Employee', 'request'));
    }

    public function leaveTest()
    {
        $offset = 0;
        $limit = 100;
        $EmployeeList = Employee::where('branch_id', Common::activeBranch())->offset($offset * $limit)->take($limit)->get();
        return view('admin.user.user.leaveTest', compact('EmployeeList'));
    }

    public function leaveCredits(Request $request)
    {
        if ($request->post('reset_leaves')) {
            \App\Model\EmployeeLeaves::truncate();
            \App\Model\LeaveCreditTransactions::truncate();
            \App\Model\LeaveCreditSetting::truncate();
            return redirect('leaveCredits')->with('success', 'Reset all leaves');
        } else if ($cron_executed_on = $request->post('cron_executed_on')) {
            $cron_executed_on = dateConvertFormtoDB($cron_executed_on);
            $LOG = Artisan::call('leaves:credit --today="' . $cron_executed_on . '"');
            return redirect('leaveCredits')->with('success', $LOG);
        }
        return view('admin.user.user.leaveCredits');
    }

    public function index()
    {

        $login_employee = employeeInfo();
        $profileRequest = [];

        $fn_head = $this->employee->find($login_employee[0]->functional_head_id);
        $dept_head = $this->employee->find($login_employee[0]->supervisor_id);

        if (session('logged_session_data.role_id') != 1 && session('logged_session_data.role_id') != 2) {

            $attendanceData = $this->attendanceRepository->getEmployeeMonthlyAttendance(date("Y-m-01"), date("Y-m-d"), session('logged_session_data.employee_id'));

            $hasSupervisorWiseEmployee = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();
            if (count($hasSupervisorWiseEmployee) == 0) {
                $leaveApplication = [];
            } else {
                $leaveApplication = $this->leaveApplication->with(['employee', 'leaveType'])
                    ->whereIn('employee_id', array_values($hasSupervisorWiseEmployee))
                    ->where('status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_application_id', 'desc')
                    ->get();
            }

            $employeeInfo = $this->employee->with('designation')->where('employee_id', session('logged_session_data.employee_id'))->first();

            $employeeTotalLeave = $this->leaveApplication->select(DB::raw('IFNULL(SUM(number_of_day), 0) as totalNumberOfDays'))
                ->where('employee_id', session('logged_session_data.employee_id'))
                ->where('status', 2)
                ->whereBetween('approve_date', [date("Y-01-01"), date("Y-12-31")])
                ->first();

            // date of birth in this month
            $firstDayThisMonth = date('Y-m-d');
            $lastDayThisMonth = date("Y-m-d", strtotime("+1 month", strtotime($firstDayThisMonth)));
            $from_date_explode = explode('-', $firstDayThisMonth);
            $from_day = $from_date_explode[2];
            $from_month = $from_date_explode[1];
            $concatFormDayAndMonth = $from_month . '-' . $from_day;

            $to_date_explode = explode('-', $lastDayThisMonth);
            $to_day = $to_date_explode[2];
            $to_month = $to_date_explode[1];
            $concatToDayAndMonth = $to_month . '-' . $to_day;
            $hasSupervisor = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();
            if (count($hasSupervisor) == 0) {
                $supervisorResults = [];
            } else {
                $supervisorResults  = LeaveApplication::with('employee')
                    ->whereIn('employee_id', array_values($hasSupervisor))
                    ->where('status', 1)
                    ->where('functional_head_status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_application_id', 'desc')
                    ->get();
            }
            $hasfunctionalSupervisor = $this->employee->select('employee_id')->where('functional_head_id', session('logged_session_data.employee_id'))->get()->toArray();

            if (count($hasfunctionalSupervisor) == 0) {
                $functionalSupervisorResults = [];
            } else {
                $functionalSupervisorResults  =  LeaveApplication::with('employee')
                    ->whereIn('employee_id', array_values($hasfunctionalSupervisor))
                    ->whereIn('status', [2, 4])
                    ->where('functional_head_status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_application_id', 'desc')
                    ->get();
            }

            //permission
            $hasSupervisorPer = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();
            if (count($hasSupervisorPer) == 0) {
                $supervisorResultsPer = [];
            } else {
                $supervisorResultsPer  = LeavePermission::with('employee')
                    ->whereIn('employee_id', array_values($hasSupervisorPer))
                    ->where('status', 1)
                    ->where('functional_head_status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_permission_id', 'desc')
                    ->get();
            }
            $hasfunctionalSupervisorPer = $this->employee->select('employee_id')->where('functional_head_id', session('logged_session_data.employee_id'))->get()->toArray();
            if (count($hasfunctionalSupervisorPer) == 0) {
                $functionalSupervisorResultsPer = [];
            } else {
                $functionalSupervisorResultsPer  =  LeavePermission::with('employee')
                    ->whereIn('employee_id', array_values($hasfunctionalSupervisorPer))
                    ->whereIn('status', [2, 4])
                    ->where('functional_head_status', 1)
                    ->orderBy('status', 'asc')
                    ->orderBy('leave_permission_id', 'desc')
                    ->get();
            }

            $upcoming_birtday = $this->employee->orderBy('date_of_birth', 'desc')->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= '" . $concatFormDayAndMonth . "' AND DATE_FORMAT(date_of_birth, '%m-%d') <= '" . $concatToDayAndMonth . "' ")->get();

            $policies = CompanyPolicy::whereNull('branch_id')->orWhere('branch_id', session('logged_session_data.branch_id'))->get();

            $data = [
                'policies' => $policies,
                'attendanceData' => $attendanceData,
                'leaveApplication' => $leaveApplication,
                'employeeInfo' => $employeeInfo,
                'employeeTotalLeave' => $employeeTotalLeave,
                'functionalHeadApplication' => $functionalSupervisorResults,
                'supervisorResultsPer' => $supervisorResultsPer,
                'functionalSupervisorResultsPer' => $functionalSupervisorResultsPer,
                'upcoming_birtday' => $upcoming_birtday,
                'profileRequest' => $profileRequest,
                'dept_head' => $dept_head,
                'fn_head' => $fn_head,
            ];

            return view('admin.generalUserHome', $data);
        }

        //leave
        $hasSupervisor = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();

        if (count($hasSupervisor) == 0) {
            $supervisorResults = [];
        } else {
            $supervisorResults  = LeaveApplication::with('employee')
                ->whereIn('employee_id', array_values($hasSupervisor))
                ->where('status', 1)
                ->where('functional_head_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_application_id', 'desc')
                ->get();
        }
        $hasfunctionalSupervisor = $this->employee->select('employee_id')->where('functional_head_id', session('logged_session_data.employee_id'))->get()->toArray();
        if (count($hasfunctionalSupervisor) == 0) {
            $functionalSupervisorResults = [];
        } else {
            $functionalSupervisorResults  =  LeaveApplication::with('employee')
                ->whereIn('employee_id', array_values($hasfunctionalSupervisor))
                ->whereIn('status', [2, 4])
                ->where('functional_head_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_application_id', 'desc')
                ->get();
        }

        //permission
        $hasSupervisorPer = $this->employee->select('employee_id')->where('supervisor_id', session('logged_session_data.employee_id'))->get()->toArray();
        if (count($hasSupervisorPer) == 0) {
            $supervisorResultsPer = [];
        } else {
            $supervisorResultsPer  = LeavePermission::with('employee')
                ->whereIn('employee_id', array_values($hasSupervisorPer))
                ->where('status', 1)
                ->where('functional_head_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->get();
        }

        $hasfunctionalSupervisorPer = $this->employee->select('employee_id')->where('functional_head_id', session('logged_session_data.employee_id'))->get()->toArray();
        if (count($hasfunctionalSupervisorPer) == 0) {
            $functionalSupervisorResultsPer = [];
        } else {
            $functionalSupervisorResultsPer  =  LeavePermission::with('employee')
                ->whereIn('employee_id', array_values($hasfunctionalSupervisorPer))
                ->whereIn('status', [2, 4])
                ->where('functional_head_status', 1)
                ->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->get();
        }

        $WorkShift = WorkShift::orderBy('start_time', 'ASC')->first();
        $start_time = $WorkShift ? $WorkShift->start_time : '00:00:00';
        $minTime = date('Y-m-d H:i:s', strtotime('0 minutes', strtotime($start_time)));

        $attendanceData = MsSql::where('datetime', '>=', ($minTime))->whereHas('employee')->with('employee')->where('type', 'IN')
            ->groupBy('ms_sql.ID')->orderByDesc('ms_sql.datetime')->get();

        $totalEmployee = $this->employee->count();
        $totalActiveEmployee = $this->employee->where('status', UserStatus::$ACTIVE)->count();
        $totalInActiveEmployee = $this->employee->where('status', UserStatus::$INACTIVE)->whereDate('updated_at', '>=', date('Y-m-d', strtotime('-6 months')))->count();
        $totalResigned = $this->employee->where('status', UserStatus::$INACTIVE)->whereDate('updated_at', '<=', date('Y-m-d', strtotime('-6 months')))->count();
        $totalDevice = Branch::count();
        $newJoining = $this->employee->where('status', UserStatus::$ACTIVE)->whereDate('date_of_joining', '>=', date('Y-m-d', strtotime('-6 months')))->count();

        $totalDepartment = $this->department->count();

        // date of birth in this month
        $firstDayThisMonth = date('Y-m-d');
        $lastDayThisMonth = date("Y-m-d", strtotime("+1 month", strtotime($firstDayThisMonth)));
        $from_date_explode = explode('-', $firstDayThisMonth);
        $from_day = $from_date_explode[2];
        $from_month = $from_date_explode[1];
        $concatFormDayAndMonth = $from_month . '-' . $from_day;

        $to_date_explode = explode('-', $lastDayThisMonth);
        $to_day = $to_date_explode[2];
        $to_month = $to_date_explode[1];
        $concatToDayAndMonth = $to_month . '-' . $to_day;
        $probationNotificationDate = date('Y-m-d', strtotime('-6 months +14 days'));

        $upcoming_birtday = $this->employee->orderBy('date_of_birth', 'desc')->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= '" . $concatFormDayAndMonth . "' AND DATE_FORMAT(date_of_birth, '%m-%d') <= '" . $concatToDayAndMonth . "' ")->get();
        $probationEndingEmployees = $this->employee->orderBy('date_of_joining', 'asc')->where('permanent_status', UserStatus::$PROBATION_PERIOD)->whereRaw("DATE_FORMAT(date_of_joining, '%Y-%m-%d') >= '" . $probationNotificationDate . "' AND DATE_FORMAT(date_of_joining, '%Y-%m-%d') <= '" . date('Y-m-d') . "' ")->get();


        $profileRequest = EmployeeProfile::where('status', 1)->distinct('employee_id')->latest()->get();
        $dailyAttendanceReport =  $this->dailyAttendance();
        $onLeave = LeaveApplication::whereMonth('application_from_date', date('m'))->where('status', LeaveStatus::$APPROVE)->get();

        $leaveCount = 0;

        foreach ($onLeave as $key => $value) {
            $period = CarbonPeriod::create($value->application_from_date, $value->application_to_date);
            foreach ($period as $key => $date) {
                if ($date->format('Y-m-d') == date('Y-m-d')) {
                    $leaveCount++;
                }
            }
        }

        $lateCount = 0;

        $shiftObj = WorkShift::first();

        foreach ($attendanceData as $key => $value) {
            if (date('H:i:s', strtotime($shiftObj->late_count_time)) <= date('H:i:s', strtotime($value->datetime))) {
                $lateCount++;
            }
        }

        $policies = CompanyPolicy::get();

        $data = [
            'policies' => $policies,
            'attendanceData' => $attendanceData,
            'totalEmployee' => $totalEmployee,
            'totalActiveEmployee' => $totalActiveEmployee,
            'totalDepartment' => $totalDepartment,
            'totalAttendance' => count($attendanceData),
            'totalAbsent' => $totalEmployee - count($attendanceData),
            'leaveApplication' => $supervisorResults,
            'functionalHeadApplication' => $functionalSupervisorResults,
            'supervisorResultsPer' => $supervisorResultsPer,
            'functionalSupervisorResultsPer' => $functionalSupervisorResultsPer,
            'upcoming_birtday' => $upcoming_birtday,
            'dailyAttendanceData' => isset($dailyAttendanceData) ? $dailyAttendanceData : 0,
            'dailyAttendanceReport' =>  $dailyAttendanceReport,
            'profileRequest' => $profileRequest,
            'lateCount' => $lateCount,
            'leaveCount' => $leaveCount,
            'totalInActiveEmployee' => $totalInActiveEmployee,
            'totalResigned' => $totalResigned,
            'totalDevice' => $totalDevice,
            'newJoining' => $newJoining,
            'probationEndingEmployees' => $probationEndingEmployees,
        ];

        return view('admin.adminhome', $data);
    }

    public function dailyAttendance()
    {
        \set_time_limit(0);

        $branchId = null;
        $results = [];
        $from_date = date("Y-m-d");
        $to_date = date("Y-m-d");

        if (session('logged_session_data.role_id') != 1) {
            $branchId = session('logged_session_data.branch_id');
        }

        $AttendanceStatusID = $this->attendanceRepository->AttendanceStatusID;
        $AttendanceStatusID['.'] = 'Half Day';
        ksort($AttendanceStatusID);
        $results = $this->attendanceRepository->getEmployeeDateAttendance($from_date, $to_date, null, null, $branchId);
        // dd($results);
        return  json_decode(json_encode($results));
    }

    public function profile()
    {

        $employeeInfo = Employee::where('employee.employee_id', session('logged_session_data.employee_id'))->with('userName')->first();
        $User = User::find($employeeInfo->user_id);
        $employeeExperience = EmployeeExperience::where('employee_id', session('logged_session_data.employee_id'))->get();
        $employeeEducation = EmployeeEducationQualification::where('employee_id', session('logged_session_data.employee_id'))->get();

        return view('admin.user.user.profile', ['employeeInfo' => $employeeInfo, 'User' => $User, 'employeeExperience' => $employeeExperience, 'employeeEducation' => $employeeEducation]);
    }

    public function test()
    {
        $User = Auth::user();
        return view('admin.user.user.test', compact('User'));
    }

    public function mail()
    {

        $user = array(
            'name' => "Learning Laravel",
        );

        Mail::send('emails.mailExample', $user, function ($message) {
            $message->to("hari9578@gmail.com");
            $message->subject('E-Mail Example');
        });

        return "Your email has been sent successfully";
    }

    public function attendanceSummaryReport(Request $request)
    {

        $month = date("Y-m");

        $monthAndYear = explode('-', $month);
        $month_data = $monthAndYear[1];
        $dateObj = DateTime::createFromFormat('!m', $month_data);
        $monthName = $dateObj->format('F');

        $monthToDate = findMonthToAllDate($month);
        $leaveType = LeaveType::get();
        $result = $this->attendanceRepository->findAttendanceSummaryReport($month);

        return ['results' => $result, 'monthToDate' => $monthToDate, 'month' => $month, 'leaveTypes' => $leaveType, 'monthName' => $monthName];
    }

    public function editProfile($id)
    {
        $userList = User::where('status', 1)->get();
        $roleList = Role::get();
        $editModeData = Employee::findOrFail($id);
        $departmentList = DepartmentCase::where('branch_id', $editModeData->branch_id)->get();
        $designationList = DesignationCase::where('branch_id', $editModeData->branch_id)->get();
        $workShiftList = DB::table('work_shift')->where('branch_id', $editModeData->branch_id)->pluck('shift_name', 'work_shift_id');
        $payGradeList = PayGrade::all();
        $hourlyPayGradeList = HourlySalary::all();
        $device_list = Device::where('status', 1)->get();
        $incentive = $this->employeeRepositories->incentive();
        $overTime = $this->employeeRepositories->overTime();
        $salaryLimit = $this->employeeRepositories->salaryLimit();
        $workShift = $this->employeeRepositories->workShift();
        $workShift = $workShiftList;
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');

        $operationManagerList = Employee::with('user')
            ->whereHas('user', function ($query) {
                $query->whereIn('role_id', [1, 2, 3]);
            })
            ->where('status', 1)
            ->get();

        $supervisorList = Employee::join('user', 'user.user_id', 'employee.user_id')
            ->where('employee.employee_id', '!=', session('logged_session_data.employee_id'))
            ->where('employee.status', UserStatus::$ACTIVE)
            ->whereIn('user.role_id', [2, 3])
            ->orderBY('employee.finger_id', 'asc')->get();

        if ($branchId !== null && $roleId !== 1) {
            $branchList = Branch::where('branch_id', session('logged_session_data.branch_id'))->get();
        } elseif ($selectedbranchId !== null && $roleId == 1) {
            $branchList = Branch::get();
        } else {
            $branchList = Branch::where('branch_id', session('logged_session_data.branch_id'))->get();
        }
        $employeeAccountEditModeData = User::where('user_id', $editModeData->user_id)->first();
        $operationManagerList = Employee::with('user')
            ->whereHas('user', function ($query) {
                $query->whereIn('role_id', [1, 2, 3]);
            })
            ->where('status', 1)
            ->get();
        $data = [
            'userList' => $userList,
            'roleList' => $roleList,
            'departmentList' => $departmentList,
            'designationList' => $designationList,
            'branchList' => $branchList,
            'supervisorList' => $supervisorList,
            'workShiftList' => $workShiftList,
            'payGradeList' => $payGradeList,
            'editModeData' => $editModeData,
            'hourlyPayGradeList' => $hourlyPayGradeList,
            'employeeAccountEditModeData' => $employeeAccountEditModeData,
            'device_list' => $device_list,
            'incentive' => $incentive,
            'overTime' => $overTime,
            'salaryLimit' => $salaryLimit,
            'workShift' => $workShift,
            'operationManagerList' => $operationManagerList,
        ];
        // dd($data);
        return view('admin.user.user.edit_employee_profile', $data);
    }

    public function storeProfile(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $employeeDocument = $employeeData = $employeePhoto = $employeeDocument = [];

            $photo = $request->file('photo');
            $document = $request->file('document_file');
            $document2 = $request->file('document_file2');
            $document3 = $request->file('document_file3');
            $document4 = $request->file('document_file4');
            $document5 = $request->file('document_file5');
            $document6 = $request->file('document_file6');

            if ($photo) {
                $imgName = md5(Str::random(30) . time() . '_' . $request->file('photo')) . '.' . $request->file('photo')->getClientOriginalExtension();
                $request->file('photo')->move('uploads/employeePhoto/', $imgName);
                $employeePhoto['photo'] = $imgName;
            }
            if ($document) {
                $document_name = date('Y_m_d_H_i_s') . '_' . $request->file('document_file')->getClientOriginalName();
                $request->file('document_file')->move('uploads/employeeDocuments/', $document_name);
                $employeeDocument['document_name'] = $document_name;
            }
            if ($document2) {
                $document_name2 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file2')->getClientOriginalName();
                $request->file('document_file2')->move('uploads/employeeDocuments/', $document_name2);
                $employeeDocument['document_name2'] = $document_name2;
            }
            if ($document3) {
                $document_name3 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file3')->getClientOriginalName();
                $request->file('document_file3')->move('uploads/employeeDocuments/', $document_name3);
                $employeeDocument['document_name3'] = $document_name3;
            }
            if ($document4) {
                $document_name4 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file4')->getClientOriginalName();
                $request->file('document_file4')->move('uploads/employeeDocuments/', $document_name4);
                $employeeDocument['document_name4'] = $document_name4;
            }
            if ($document5) {
                $document_name5 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file5')->getClientOriginalName();
                $request->file('document_file5')->move('uploads/employeeDocuments/', $document_name5);
                $employeeDocument['document_name5'] = $document_name5;
            }
            if ($document6) {
                $document_name6 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file5')->getClientOriginalName();
                $request->file('document_file6')->move('uploads/employeeDocuments/', $document_name6);
                $employeeDocument['document_name6'] = $document_name6;
            }

            $employeeData = $this->makeEmployeeDataFormat($request->all());
            $employeeData['user_id'] = auth()->user()->user_id;
            $employeeData['employee_id'] = session('logged_session_data.employee_id');
            // $employeeData['created_at'] = now();
            // $employeeData['updated_at'] = now();

            $employeeDataFormat = array_merge($employeeData, $employeePhoto, $employeeDocument);

            // OLD Profile request disabled
            EmployeeProfile::where('employee_id', session('logged_session_data.employee_id'))->update(['status' => 2]);

            // Store
            EmployeeProfile::create($employeeDataFormat);

            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('profile')->with('success', 'Profile request sent successfully.');
        } else {
            return redirect('profile')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function viewProfile($id)
    {
        $employeeInfo = EmployeeProfile::where('employee_id', $id)->latest()->with('userName')->first();
        $User = User::find($employeeInfo->user_id);
        $employeeExperience = EmployeeExperience::where('employee_id', session('logged_session_data.employee_id'))->get();
        $employeeEducation = EmployeeEducationQualification::where('employee_id', session('logged_session_data.employee_id'))->get();
        return view('admin.user.user.view_employee_profile', ['employeeInfo' => $employeeInfo, 'User' => $User, 'employeeExperience' => $employeeExperience, 'employeeEducation' => $employeeEducation]);
    }


    public function acceptProfile(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $profile = EmployeeProfile::where('employee_id', $id)->where('status', 1)->orderByDesc('employee_profile_id')->first();
            $employee = Employee::find($id);
            $rawData =  $this->makeEmployeeDataFormat($profile->toArray(), true);
            // dd([$profile, $rawData]);
            $employee->update($rawData);
            $profile->update(['status' => 0]);
            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('dashboard')->with('success', 'Profile updated successfully.');
        } else {
            return redirect('dashboard')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function rejectProfile(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $profile = EmployeeProfile::where('employee_id', $id)->latest()->first();
            $profile->update(['status' => 2]);
            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('dashboard')->with('success', 'Profile request rejected.');
        } else {
            return redirect('dashboard')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function makeEmployeeDataFormat($data, $updateProfile = null)
    {
        if (isset($data['photo']) && gettype($data['photo']) ==  'string') {
            $employeeData['photo'] = $data['photo'];
        }

        $employeeData['first_name'] = $data['first_name'];
        $employeeData['last_name'] = $data['last_name'];
        $employeeData['finger_id'] = $data['finger_id'];
        $employeeData['device_employee_id'] = $data['finger_id'];
        $employeeData['functional_head_id'] = $data['functional_head_id']  ? $data['functional_head_id'] : null;
        $employeeData['supervisor_id'] = $data['supervisor_id'] ? $data['supervisor_id'] : null;
        // $employeeData['work_shift'] = $data['work_shift'];
        // $employeeData['work_shift_id'] = $data['work_shift'];
        $employeeData['email'] = $data['email'];
        $employeeData['date_of_birth'] = dateConvertFormtoDB($data['date_of_birth']);
        $employeeData['date_of_joining'] = dateConvertFormtoDB($data['date_of_joining']);
        $employeeData['date_of_leaving'] = isset($data['date_of_leaving']) && $data['date_of_leaving'] ? dateConvertFormtoDB($data['date_of_leaving']) : null;
        $employeeData['relieving_reason'] = isset($data['relieving_reason']) && $data['date_of_leaving'] ? $data['date_of_leaving'] : null;
        // a employee relieving reason input than the employee status will be changed as Inactive
        if (isset($data['relieving_reason']) && $data['relieving_reason']) {
            $data['status'] = UserStatus::$INACTIVE;
        } else if ($data['status'] == UserStatus::$INACTIVE) {
            $data['status'] = UserStatus::$ACTIVE;
        }
        if ($data['date_of_joining']) {
            $TODAY = date('Y-m-d');
            $monthDiffs = monthDiffs($TODAY, dateConvertFormtoDB($data['date_of_joining']));
            if ($monthDiffs >= 6) {
                $employeeData['permanent_status'] = 1;
            }
        }
        $employeeData['salary_revision'] = dateConvertFormtoDB($data['salary_revision']);
        $employeeData['marital_status'] = $data['marital_status'];
        $employeeData['pf_status'] = $data['pf_status'];
        $employeeData['overtime_status'] = $data['overtime_status'];
        $employeeData['address'] = $data['address'];
        $employeeData['emergency_contacts'] = $data['emergency_contacts'];
        $employeeData['gender'] = $data['gender'];
        $employeeData['religion'] = $data['religion'];
        $employeeData['phone'] = $data['phone'];
        $employeeData['status'] = $data['status'];
        // $employeeData['department_id'] = $data['department_id'];
        // $employeeData['designation_id'] = $data['designation_id'];
        $employeeData['salary_ctc'] = $data['salary_ctc'];
        $employeeData['salary_gross'] = $data['salary_gross'];
        $employeeData['uan'] = $data['uan'];
        $employeeData['cost_centre'] = $data['cost_centre'];
        $employeeData['pan_gir_no'] = $data['pan_gir_no'];
        $employeeData['pf_account_number'] = $data['pf_account_number'];
        $employeeData['esi_card_number'] = $data['esi_card_number'];
        $employeeData['bank_name'] = $data['bank_name'];
        $employeeData['bank_account'] = $data['bank_account'];
        $employeeData['bank_ifsc'] = $data['bank_ifsc'];
        $employeeData['emp_code'] = $data['emp_code'];
        $employeeData['branch_id'] = $data['branch_id'];



        $employeeData['document_title'] = $data['document_title'];
        $employeeData['document_title2'] = $data['document_title2'];
        $employeeData['document_title3'] = $data['document_title3'];
        $employeeData['document_title4'] = $data['document_title4'];
        $employeeData['document_title5'] = $data['document_title5'];
        $employeeData['document_title6'] = $data['document_title6'];

        if ($updateProfile) {
            if ($data['document_name']) {
                $employeeData['document_name'] = $data['document_name'];
            }
            if ($data['document_name2']) {
                $employeeData['document_name2'] = $data['document_name2'];
            }
            if ($data['document_name3']) {
                $employeeData['document_name3'] = $data['document_name3'];
            }
            if ($data['document_name4']) {
                $employeeData['document_name4'] = $data['document_name4'];
            }
            if ($data['document_name5']) {
                $employeeData['document_name5'] = $data['document_name5'];
            }
            if ($data['document_name6']) {
                $employeeData['document_name6'] = $data['document_name6'];
            }
        }

        if ($salary_revision = $employeeData['salary_revision']) {
            $date = new \DateTime($salary_revision);
            $month = $date->format('n');
            $year = $date->format('Y');
            if ($month == 4 || $month == 10) {
                $d = strtotime("$year-$month-01");
                $stops = date('Y-m-d', $d);
                $employeeData['salary_esi_stop'] = $stops;
            } else {
                if ($month >= 1 && $month <= 3) {
                    $d = strtotime("March 01 $year");
                    $stops = date('Y-m-d', $d);
                } else if ($month >= 5 && $month <= 9) {
                    $d = strtotime("September 01 $year");
                    $stops = date('Y-m-d', $d);
                } else if ($month >= 11 && $month <= 12) {
                    $d = strtotime("March 01 " . ($year + 1));
                    $stops = date('Y-m-d', $d);
                }
                $employeeData['salary_esi_stop'] = $stops;
            }
        }



        $employeeData['updated_by'] = auth()->user()->user_id;

        return $employeeData;
    }
}
