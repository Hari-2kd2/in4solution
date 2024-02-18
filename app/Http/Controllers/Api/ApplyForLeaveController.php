<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use Illuminate\Support\Str;
use App\Model\EarnLeaveRule;
use App\Model\PaidLeaveRule;
use Illuminate\Http\Request;
use App\Model\LeaveApplication;
use App\Mail\LeaveApplicationMail;
use Illuminate\Support\Facades\DB;
use App\Model\PaidLeaveApplication;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\LeaveRepository;
use Illuminate\Support\Facades\Route;
use App\Repositories\CommonRepository;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ApplyForLeaveRequest;

class ApplyForLeaveController extends Controller
{
    protected $commonRepository;
    protected $leaveRepository;
    protected $authController;
    protected $controller;

    public function __construct(Controller $controller, CommonRepository $commonRepository, LeaveRepository $leaveRepository, AuthController $authController)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository = $leaveRepository;
        $this->authController = $authController;
        $this->controller = $controller;
    }


    public function index(Request $request)
    {
        $employee_id = $request->employee_id;
        if (!$employee_id) {
            return $this->controller->custom_error("Employee ID field is required!");
        }
        try {
            $tempResults = LeaveApplication::with(['employee', 'leaveType', 'approveBy', 'rejectBy', 'approveByFunctionalHead', 'rejectByFunctionalHead'])
                ->where('employee_id', $employee_id)
                ->orderBy('leave_application_id', 'desc')
                ->limit(200)->get();

            $results = [];

            foreach ($tempResults as $key => $LeaveApplication) {
                $LeaveApplication->cancel = $LeaveApplication->isCancel();
                $results[] = $LeaveApplication;
            }

            $leaveType = LeaveType::select('leave_type_id', 'leave_type_name', 'num_of_day')->get();

            return $this->controller->successdualdata("Datas Successfully Received !!", $results, $leaveType);
        } catch (\Throwable $th) {
            info(__FILE__ . ':' . __LINE__ . $th->getMessage());
            return $this->controller->custom_error("Something went wrong! please try again.");
            $bug = 1;
        }
    }

    public function dateChanged(Request $request)
    {
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
            'leave_type_id' => 'required|exists:leave_type,leave_type_id',
            'application_from_date' => 'required',
            'application_from_date' => 'required',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $ApplyForLeaveController = new \App\Http\Controllers\Leave\ApplyForLeaveController(new CommonRepository, new LeaveRepository);
        $ApplyForLeaveController->api = true;
        // 5 = Maternity Leave, 6 = Paternity Leave addtional information added $result variable to handle UI, please refer in app\Http\Controllers\Leave\ApplyForLeaveController.php method applyForTotalNumberOfDays
        $result = $ApplyForLeaveController->applyForTotalNumberOfDays($request);

        return $this->controller->success("Leave Date Changes Info Successfully Received!", $result);
    }

    public function create(Request $request)
    {
        $auth_user_id = $request->employee_id;
        $getEmployeeInfo = $this->commonRepository->getLimitedEmployeeInfo($auth_user_id);
        $leaveType = LeaveType::select('leave_type_id', 'leave_type_name', 'num_of_day')->orderBy('leave_type_id')->get(); // expect maternity, paternity

        $allTypeBalance = [
            'employee_id' => $getEmployeeInfo->employee_id,
            'branch_id' => $getEmployeeInfo->branch_id,
            'gender' => $getEmployeeInfo->gender,
            'casual_leave' => 0,
            'privilege_leave' => 0,
            'sick_leave' => 0,
        ];

        foreach ($leaveType as $key => $leaveTypeOne) {
            $leaveTypeList[$leaveTypeOne->leave_type_id] = $leaveTypeOne->leave_type_name;
        }

        ksort($leaveTypeList);

        $certificateFile = [
            'size' => LeaveRepository::FILE_SIZE,
            'sizeInBytes' => LeaveRepository::FILE_SIZE,
            'sizeInMB' => round(((LeaveRepository::FILE_SIZE) / 1024 / 1024), 2),
            'type' => LeaveRepository::FILE_TYPE,
        ];

        $data = [
            'certificateFile' => $certificateFile,
            'leaveType' => $leaveType,
            'allTypeBalance' => $allTypeBalance,
            'leaveTypeList' => $leaveTypeList,
            'getEmployeeInfo' => $getEmployeeInfo,
        ];

        return $this->controller->success("Leave Details Successfully Received !!!", $data);
    }

    public function store(Request $request)
    {
        $certificateFile = [
            'size' => LeaveRepository::FILE_SIZE,
            'sizeInBytes' => LeaveRepository::FILE_SIZE,
            'sizeInMB' => round(((LeaveRepository::FILE_SIZE) / 1024), 2),
            'type' => LeaveRepository::FILE_TYPE,
        ];

        $input = Validator::make(
            $request->all(),
            [
                'employee_id' => 'required',
                'application_from_date' => 'required',
                'application_to_date' => 'required',
                'number_of_day' => 'required',
                'leave_type_id' => 'required|exists:leave_type,leave_type_id',
                // 'mfile' => 'nullable|max:'.(LeaveRepository::FILE_SIZE / 1024),
                'mfile' => 'nullable',
                'half_day' => 'nullable|in:0.5',
                'purpose' => 'required',
            ],
            [
                'mfile.max' => 'Upload file size should be less than or equal to ' . $certificateFile['sizeInMB'] . 'MB',
            ]
        );
        if ($input->fails()) {
            $replacedMessage = str_replace('<br>', PHP_EOL,  $input->errors()->first());
            return Controller::custom_error($replacedMessage);
        }

        info('TRACE Parameters');
        info('GET=' . print_r($_GET, 1));
        info('POST=' . print_r($_POST, 1));
        info('FILES=' . print_r($_FILES, 1));

        $Employee = Employee::find($request->employee_id);

        if ($Employee) {
            // don't remove the below line;
            $Employee->api_call = true;
            $branch_id = $Employee->branch_id;
        } else {
            $branch_id = null;
            return $this->controller->custom_error('Employee id not found');
        }

        $LeaveApplication = new LeaveApplication;
        $checkedErrorMessage = '';

        // CL type leave check before after date taken any type should not allow
        if ($ClCheckMessage = $LeaveApplication->CasualLeaveChecks($Employee, $request)) {
            $checkedErrorMessage = $ClCheckMessage;
        }

        // insuffient balance, alrady apply same date ranges
        if ($OtherCheckMessage = $LeaveApplication->OtherChecks($Employee, $request)) {
            $checkedErrorMessage = $OtherCheckMessage;
        }

        $ApplyForLeaveController = new \App\Http\Controllers\Leave\ApplyForLeaveController(new CommonRepository, new LeaveRepository);
        $ApplyForLeaveController->api = true;
        $result = $ApplyForLeaveController->applyForTotalNumberOfDays($request);
        $result['number_of_day'] = $request->half_day > 0 ? ($result['number_of_day'] - $request->half_day) : $result['number_of_day'];
        // cross check from UI half day selection and number days
        if ($result['number_of_day'] != $request->number_of_day) {
            $checkedErrorMessage = 'Number of Day gives something went wrong!';
        }

        if ($checkedErrorMessage) {
            return $this->controller->custom_error($checkedErrorMessage);
        }

        $calendar_year  = calanderYear::currentYear();
        $from_date      = $request->application_from_date;
        $to_date        = $request->application_to_date;
        $number_of_day  = $request->number_of_day;

        $request_data = [
            'application_date' => date('Y-m-d'),
            'application_from_date' => $request->application_from_date,
            'application_to_date' => $request->application_to_date,
            'leave_type_id' => $request->leave_type_id,
            'employee_id' => $request->employee_id,
            'number_of_day' => $result['number_of_day'],
            'branch_id' => $branch_id,
            'calendar_year' => $calendar_year->year_id ?? null,
            'purpose' => $request->purpose,
            'functional_head_status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $mfile = $request->file('mfile');

        if ($mfile) {
            if ($request->postman && $mfile) {
                $attachedFile = md5(Str::random(30) . '_' . $request->file('mfile')) . '.' . $request->file('mfile')->getClientOriginalExtension();
                $request->file('mfile')->move("uploads/employeeMedicalFile/", $attachedFile);
                $request_data['medical_file'] = $attachedFile;
            } else if ($request->post('mfile') && $request->post('mfile_ext')) {
                $attachedFile = md5(Str::random(30) . '_') . '.' . $request->post('mfile_ext');
                file_put_contents("uploads/employeeMedicalFile/" . $attachedFile, base64_decode($request->post('mfile')));
                $request_data['medical_file'] = $attachedFile;
            }
        }

        $leave_application = array_merge(array('number_of_day' => $number_of_day), $request_data);

        try {
            DB::beginTransaction();
            $leave_application_id = DB::table('leave_application')->insertGetID($leave_application);
            DB::commit();
            $leave_data = LeaveApplication::join('leave_type', 'leave_type.leave_type_id', '=', 'leave_application.leave_type_id')
                ->join('employee', 'employee.employee_id', '=', 'leave_application.employee_id')
                ->where('leave_application_id', $leave_application_id)
                ->first();
            $responce = $this->controller->success("Leave Application Sent Successfully!", $leave_application);
        } catch (\Throwable $e) {
            DB::rollback();
            $message = $e->getMessage();
            $responce = $this->controller->custom_error($message);
        } finally {
            return $responce;
        }
    }

    public function update(Request $request)
    {
        try {

            $update_data = [
                'approve_by' => $request->approve_by,
                'reject_by' => $request->reject_by,
                'approve_date' => $request->approve_date,
                'reject_date' => $request->reject_date,
                'remarks' => $request->remarks,
                'status' => $request->status,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first()->toArray();
            $data = \array_merge($raw_data, $update_data);

            DB::beginTransaction();
            $leave_application = LeaveApplication::where('leave_application_id', $request->leave_application_id)->update($data);
            DB::commit();

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first();

            $responce = $this->controller->success("Leave Details Saved Successfully !", $raw_data);
        } catch (\Throwable $e) {

            DB::rollback();
            $message = $e->getMessage();
            $responce = $this->controller->custom_error($message);
        } finally {

            return $responce;
        }
    }

    public function getEmployeeLeaveBalance($leave_type_id, $employee_id)
    {
        if ($leave_type_id != '' && $employee_id != '') {
            return $this->leaveRepository->calculateEmployeeLeaveBalance($leave_type_id, $employee_id);
        }
    }

    public function applyForTotalNumberOfDays($from_date, $to_date)
    {
        $application_from_date = dateConvertFormtoDB($from_date);
        $application_to_date = dateConvertFormtoDB($to_date);
        return $this->leaveRepository->calculateTotalNumberOfLeaveDays($application_from_date, $application_to_date);
    }

    public function applyForLeave(ApplyForLeaveRequest $request)
    {

        // $request->validate([
        //     'application_from_date' => 'required',
        //     'application_to_date' => 'required',
        //     'leave_type_id' => 'required',
        // ]);

        $leave_status = [];

        $fdate = $request->application_from_date;
        $tdate = $request->application_to_date;
        $leave_type_id = $request->leave_type_id;

        $input = $request->all();
        $input['application_from_date'] = dateConvertFormtoDB($fdate);
        $input['application_to_date'] = dateConvertFormtoDB($tdate);
        $input['application_date'] = date('Y-m-d');

        $existing_records = $this->create($request);
        $total_leave_taken = $existing_records['sumOfLeaveTaken'];

        $leave_avaliable = LeaveType::where('leave_type_id', $leave_type_id)->pluck('num_of_day');
        $leave_taken = LeaveApplication::where('leave_type_id', $leave_type_id)->pluck('number_of_day');
        $common_leave_taken = (int) $leave_taken->sum();
        $common_leave_avaliable = (int) $leave_avaliable->sum();
        $leave_balance = $common_leave_avaliable - $common_leave_taken;

        if ($leave_type_id == 1) {

            $month = date('m', \strtotime($fdate));
            $earn_leave_rule = EarnLeaveRule::sum('day_of_earn_leave');
            $leave_status['leave_status'] = $common_leave_taken < ((int) $month * (int) $earn_leave_rule);
        } elseif ($leave_type_id == 2) {

            $paid_leave_rule = PaidLeaveRule::sum('day_of_paid_leave');
            $leave_status['leave_status'] = $total_leave_taken < $paid_leave_rule;
        } else {
            $datetime1 = new DateTime($fdate);
            $datetime2 = new DateTime($tdate);
            $interval = $datetime1->diff($datetime2);
            $common_leave_applied = $interval->format('%a');
            $leave_status['leave_status'] = $common_leave_applied <= $leave_balance;
        }

        if ($leave_status['leave_status']) {
            try {

                DB::beginTransaction();
                $if_exists = LeaveApplication::where('application_from_date', $input['application_from_date'])->where('application_to_date', $input['application_to_date'])
                    ->where('employee_id', $request->employee_id)->where('status', 2)->count();
                $if_exists > 0 ? $bug = 1 : $bug = null && LeaveApplication::create($input);
                DB::commit();
            } catch (\Exception $e) {

                DB::rollback();
                $bug = $e->getMessage();
            }

            if ($bug == \null) {

                return $this->controller->success("Leave application sent successfully.", \array_merge($input, $leave_status));
            } elseif ($bug == 1) {

                return $this->controller->custom_error("Leave application already exists for selected dates.");
            } else {

                return $this->controller->error();
            }
        }

        return $this->controller->custom_error('Leave Balance Not Avaliable For Selected Leave Type.');
    }

    public function sendLeaveMail($leave_application_id, $emp_id, $finger_id, $name, $email, $from, $to, $type, $days, $application_date)
    {

        $data = [
            'url_a' => 'http://localhost:8074/propeople/mail/approve/' . $leave_application_id,
            'url_b' => 'http://localhost:8074/propeople/mail/reject/' . $leave_application_id,
            'date' => $application_date,
            'name' => $name,
            'emp_id' => $emp_id,
            'finger_id' => $finger_id,
            'email' => $email,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'days' => $days,
        ];

        Mail::to($email)->send(new LeaveApplicationMail($body = $data));
    }

    public function approve($leave_application_id)
    {

        $bool = LeaveApplication::where('leave_application_id', $leave_application_id)->where('status', 1)->first();

        $raw_data = LeaveApplication::where('leave_application_id', $leave_application_id)->first()->toArray();

        $body = LeaveApplication::join('employee', 'employee.employee_id', '=', 'leave_application.employee_id')
            ->join('leave_type', 'leave_type.leave_type_id', '=', 'leave_application.leave_type_id')
            ->where('leave_application_id', $leave_application_id)->select(
                'leave_type.*',
                'leave_application.*',
                'employee.first_name',
                'employee.email',
                'employee.employee_id',
                'employee.finger_id'
            )->first();

        $employee_id = session('logged_session_data.employee_id');

        if (isset($employee_id)) {

            $user_data = session('logged_session_data') != null ? (session('logged_session_data')) : [];

            $update_data = [
                'approve_by' => $employee_id,
                'approve_date' => date('Y-m-d'),
                'remarks' => 'approved',
                'status' => 2,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $data = \array_merge($raw_data, $update_data);
        }

        if ($bool && session('logged_session_data') != null && session('logged_session_data.role_id') == 1) {

            LeaveApplication::where('leave_application_id', $leave_application_id)->update($data);

            return view('emails.accepted', ['body' => $body, 'user' => $user_data])->with('status', 'success');
        } elseif ($body->status == 2 && isset($employee_id) && session('logged_session_data.role_id') == 1) {

            return view('emails.accepted', ['body' => $body, 'user' => $user_data])->with('status', 'success');
        } elseif ($employee_id == "" || $employee_id == null) {

            return \view('admin.login');
        } else {

            return \view('errors.404');
        }
    }

    public function reject($leave_application_id)
    {
        $update_data = [
            'reject_by' => 1,
            'reject_date' => date('Y-m-d'),
            'remarks' => 'rejected',
            'status' => 3,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $employee_id = session('logged_session_data.employee_id');

        if (isset($employee_id)) {
            $user_data = (session('logged_session_data'));
        }

        $bool = LeaveApplication::where('leave_application_id', $leave_application_id)->where('status', 1)->first();

        $raw_data = LeaveApplication::where('leave_application_id', $leave_application_id)->first()->toArray();

        $data = \array_merge($raw_data, $update_data);

        $body = LeaveApplication::join('employee', 'employee.employee_id', '=', 'leave_application.employee_id')
            ->join('leave_type', 'leave_type.leave_type_id', '=', 'leave_application.leave_type_id')
            ->where('leave_application_id', $leave_application_id)->select(
                'leave_type.*',
                'leave_application.*',
                'employee.first_name',
                'employee.email',
                'employee.employee_id',
                'employee.finger_id'
            )->first();

        if ($bool && session('logged_session_data.role_id') == 1) {
            LeaveApplication::where('leave_application_id', $leave_application_id)->update($data);
            return view('emails.rejected', ['body' => $body, 'user' => $user_data])->with('status', 'success');
        } elseif ($body->status == 3 && isset($employee_id) && session('logged_session_data.role_id') == 1) {
            return view('emails.rejected', ['body' => $body, 'user' => $user_data])->with('status', 'success');
        } elseif ($employee_id == "" || $employee_id == null) {
            return \view('admin.login');
        } else {
            return \view('errors.404');
        }
    }

    // public function sample(Request $request)
    // {
    //     // $path =  Request::path();
    //     // $getQueryString =  Request::getPathInfo();
    //     // $url = Request::url();
    //     $getFacadeRoot = Route::getFacadeRoot()->current()->uri();
    //     $getCurrentRoute = Route::getCurrentRoute()->getActionName();
    //     $request = $request->is('api/*');

    //     // $array = array($path, $getQueryString, $url);
    //     $array = array($getFacadeRoot, $getCurrentRoute, $request);

    //     return response()->json([
    //         'message' => "API works fine",
    //         'array' => $array,
    //     ], 200);
    // }

    public function approve1(Request $request)
    {
        try {

            $update_data = [
                'approve_by' => 1,
                'approve_date' => date('Y-m-d'),
                'remarks' => 'approved',
                'status' => 2,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first()->toArray();
            $data = \array_merge($raw_data, $update_data);

            DB::beginTransaction();
            LeaveApplication::where('leave_application_id', $request->leave_application_id)->update($data);
            DB::commit();

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first();

            $responce = $this->success("Leave Details Saved Successfully !", $raw_data);
        } catch (\Throwable $e) {

            DB::rollback();
            $message = $e->getMessage();
            $responce = $this->custom_error($message);
        } finally {

            return $responce;
        }
    }

    public function reject1(Request $request)
    {
        try {

            $update_data = [
                'reject_by' => 1,
                'reject_date' => date('Y-m-d'),
                'remarks' => 'rejected',
                'status' => 3,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first()->toArray();
            $data = \array_merge($raw_data, $update_data);

            DB::beginTransaction();
            $leave_application = LeaveApplication::where('leave_application_id', $request->leave_application_id)->update($data);
            DB::commit();

            $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first();

            $responce = $this->success("Leave Details Saved Successfully !", $raw_data);
        } catch (\Throwable $e) {

            DB::rollback();
            $message = $e->getMessage();
            $responce = $this->custom_error($message);
        } finally {

            return $responce;
        }
    }

    public function cancel(Request $request)
    {

        if (!$request->leave_application_id) {
            return $this->controller->custom_error("Leave application ID field is required.");
        }

        if (!$request->employee_id) {
            return $this->controller->custom_error("Logged employeed ID field is required.");
        }

        $LeaveApplication = LeaveApplication::find($request->leave_application_id);
        if (!$LeaveApplication) {
            return $this->controller->custom_error("Leave application ID not exists.");
        }
        if ($LeaveApplication->status == LeaveStatus::$CANCEL) {
            return $this->controller->custom_error("Leave application already canceled!");
        }
        $result = $LeaveApplication->leaveCancelTransaction();
        $LeaveApplication->cancel = $result;
        if ($result) {
            return $this->controller->success("Leave cancellation process is successful!", $LeaveApplication);
        } else {
            return $this->controller->custom_error("Leave cancellation process something went wrong!");
        }
    }
}
