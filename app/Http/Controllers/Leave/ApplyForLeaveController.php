<?php

namespace App\Http\Controllers\Leave;

use Response;
use App\User;
use DateTime;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Components\Common;
use App\Model\calanderYear;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Model\EmployeeLeaves;
use App\Model\LeaveEncashment;
use Illuminate\Support\Carbon;
use App\Model\LeaveApplication;
use App\Model\RhApplicationCase;
use App\Model\LeaveApplicationCase;
use App\Model\PaidLeaveApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use App\Repositories\SalaryRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Notifications\LeaveNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ApplyForLeaveRequest;
use Illuminate\Support\Facades\Notification;

class ApplyForLeaveController extends Controller
{

    protected $commonRepository;
    protected $leaveRepository;
    public $api = false;

    public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository = $leaveRepository;
    }

    public function index()
    {
        $results = LeaveApplication::with(['employee', 'leaveType', 'approveBy', 'rejectBy', 'approveByFunctionalHead', 'rejectByFunctionalHead'])
            ->where('employee_id', session('logged_session_data.employee_id'))
            ->orderBy('leave_application_id', 'desc')
            ->paginate();

        return view('admin.leave.applyForLeave.index', ['results' => $results]);
    }

    public function create()
    {
        $leaveType = LeaveType::select('leave_type_id', 'leave_type_name', 'num_of_day', 'created_at', 'updated_at')
            ->get();
        $leaveTypeList[''] = '---- Please select ----';
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(Auth::user()->user_id);

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

        return view('admin.leave.applyForLeave.leave_application_form', ['leaveTypeList' => $leaveTypeList, 'getEmployeeInfo' => $getEmployeeInfo, 'certificateFile' => $certificateFile]);
    }


    public function getEmployeeLeaveBalance(Request $request)
    {
        $leave_type_id = $request->leave_type_id;

        $employee_id = $request->employee_id;
        if (!$employee_id) {
            $employee_id = session('logged_session_data.employee_id');
        }

        $employeeLeaveData = LeaveType::where('leave_type_id', $leave_type_id)->first();
        $leaveTaken = LeaveApplication::where('employee_id', $employee_id)->where('leave_type_id', $leave_type_id)->sum('number_of_day');
        return $employeeLeaveData->num_of_day - $leaveTaken;
    }

    public function getEmployeeLeaveStatus(Request $request)
    {
        $leave_type_id = $request->leave_type_id;
        $employee_id = $request->employee_id;
        $application_from_date = date("Y-m-d", strtotime($request->application_from_date));
        $application_to_date = date("Y-m-d", strtotime($request->application_to_date));
        $number_of_day = $request->number_of_day;
        if ($leave_type_id != '' && $application_from_date != '' && $application_to_date != '' && $number_of_day != '') {
            return $this->leaveRepository->calculateEmployeeLeaveStatus($leave_type_id, $employee_id, $application_from_date, $application_to_date, $number_of_day);
        }
    }

    public function applyForTotalNumberOfDays(Request $request)
    {
        $application_from_date = dateConvertFormtoDB($request->application_from_date);
        $application_to_date = dateConvertFormtoDB($request->application_to_date);
        $days = $this->leaveRepository->calculateTotalNumberOfLeaveDays($application_from_date, $application_to_date);
        $daysActual = $days;
        $balance = $this->getEmployeeLeaveBalance($request);

        $data = [];
        $data['balance'] = $balance;

        if ($this->api) {
            if (!isset($data['days'])) {
                if ($days == -101) {
                    $days = $daysActual;
                }
                $data['number_of_day'] = $days;
            }
            return $data;
        }
        return response($days)
            ->header('Content-Type', 'aplication/json')
            ->header('info', json_encode($data));
    }

    public function checkBeforeAfterDates($FROM_DATE, $TO_DATE)
    {
        // check holiday before after if holiday than move next previous date
        $checkPrev = 0;
        previous_day:
        $checkPrev++;
        $FROM_DATE = operateDays($FROM_DATE, 1, '-');
        info($checkPrev . ') BEFORE DATE=' . $FROM_DATE);
        $beforeHoliday = DB::table('holiday_details')->where('from_date', '>=', $FROM_DATE)->where('to_date', '<=', $FROM_DATE)->first();
        $BeforeDay = date('l', strtotime($FROM_DATE));

        if ($beforeHoliday) {
            info('Yes BEFORE DAY is holiday=' . $FROM_DATE);
            goto previous_day;
        } else if ($BeforeDay == Config::get('leave.weekly_holiday')) {
            info('Yes BEFORE DAY is weekoff=' . $FROM_DATE);
            goto previous_day;
        }

        $nextDay = 0;
        next_day:
        $nextDay++;
        $TO_DATE = operateDays($TO_DATE, 1);
        info($nextDay . ') AFTER DATE=' . $TO_DATE);
        $afterHoliday = DB::table('holiday_details')->where('from_date', '>=', $TO_DATE)->where('to_date', '<=', $TO_DATE)->first();
        $afterDay = date('l', strtotime($TO_DATE));
        if ($afterHoliday) {
            info('Yes NEXT DAY is holiday=' . $TO_DATE);
            goto next_day;
        } else if ($afterDay == Config::get('leave.weekly_holiday')) {
            info('Yes NEXT DAY is weekoff=' . $TO_DATE);
            goto next_day;
        }

        return ['PREV_DATE' => $FROM_DATE, 'NEXT_DATE' => $TO_DATE];
    }

    public function store(ApplyForLeaveRequest $request)
    {
        // no need to pass employee_id in form hidden field, it may attempt hack employee id 
        $employee_id = session('logged_session_data.employee_id');
        $employee = Employee::where('employee_id', $employee_id)->first();
        $hod = Employee::where('employee_id', $employee->supervisor_id)->first();
        $EmployeeLeaves = $employee->EmployeeLeaves ?? new EmployeeLeaves;
        $currentYear = calanderYear::where('year_status', 0)->first();
        $LeaveApplication = new LeaveApplication;
        $imgName = '';
        $input = $request->all();
        $input['application_from_date'] = $from_date = dateConvertFormtoDB($request->application_from_date);
        $input['application_to_date'] = $to_date = dateConvertFormtoDB($request->application_to_date);
        $input['application_date'] = date('Y-m-d');
        $input['calendar_year'] =  $currentYear->year_id;
        $input['branch_id'] =  $employee->branch_id;
        $input['functional_head_status'] = 1;
        // $input['functional_head_status'] = 1;

        // CL type leave check before after date taken any type should not allow
        if ($ClCheckMessage = $LeaveApplication->CasualLeaveChecks($employee, $request)) {
            return redirect('applyForLeave')->with('error', $ClCheckMessage);
        }

        // insuffient balance, alrady apply same date ranges
        if ($OtherCheckMessage = $LeaveApplication->OtherChecks($employee, $request)) {
            return redirect('applyForLeave')->with('error', $OtherCheckMessage);
        }



        $mfile = $request->file('mfile');
        if ($request->leave_type_id == 2 && $request->number_of_day > 2 && $mfile) {
            $attachedFile = md5(Str::random(30) . '_' . $request->file('mfile')) . '.' . $request->file('mfile')->getClientOriginalExtension();
            $request->file('mfile')->move("uploads/employeeMedicalFile/", $attachedFile);
            $input['medical_file'] = $attachedFile;
        }
        $bug = 0;
        try {
            LeaveApplication::create($input);
            if ($hod->email) {
                // $maildata = Common::mail('emails/mail', $hod->email, 'Leave Request Notification', ['head_name' => $hod->first_name . ' ' . $hod->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . '. have requested for leave (Purpose: ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
            }
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('applyForLeave')->with('success', 'Leave application successfully send.');
        } else {
            return redirect('applyForLeave')->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request)
    {
        $LeaveApplication = LeaveApplication::findOrFail($request->id);
        $result = $LeaveApplication->leaveCancelTransaction();
        return $result ? $request->id : '';
    }

    // admin mehtods starts
    public function EncashmentApplications(Request $request)
    {
        $LeaveEncashment = new LeaveEncashment;
        $LeaveEncashmentList = $LeaveEncashment->LeaveEncashmentList(false);

        return view('admin.leave.applyEncashment.EncashmentApplications', compact('LeaveEncashment', 'LeaveEncashmentList'));
    }

    public function EncashmentDetail(Request $request)
    {
        $LeaveEncashment = LeaveEncashment::findOrFail($request->id);
        $LeaveEncashmentList = $LeaveEncashment->LeaveEncashmentList(false);
        $_POST['enc_days'] = $LeaveEncashment->enc_days;
        $_POST['employee_id'] = $LeaveEncashment->employee_id;
        $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData($LeaveEncashment->employee_id);
        $DATA = $this->EncashmentCalculates($request, $array = true);
        return view('admin.leave.applyEncashment.EncashmentDetail', compact('LeaveEncashment', 'LeaveEncashmentList', 'LeaveEncashmentData', 'DATA'));
    }

    public function EncashmentAction(Request $request)
    {

        try {
            DB::beginTransaction();
            $LeaveEncashment = LeaveEncashment::findOrFail($request->id);
            $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData($LeaveEncashment->employee_id);
            $_POST['enc_days'] = $LeaveEncashment->enc_days;
            $_POST['employee_id'] = $LeaveEncashment->employee_id;
            $DATA = $this->EncashmentCalculates($request, $array = true);
            $AdminEmployee = Employee::find(session('logged_session_data.employee_id'));

            $Employee                                           = $LeaveEncashmentData['Employee'];
            $LeaveEncashment->enc_status                        = $request->enc_status;
            $LeaveEncashment->enc_remark                        = $request->enc_remark;
            $LeaveEncashment->enc_action_by                     = $AdminEmployee->employee_id;
            $LeaveEncashment->enc_action_on                     = date('Y-m-d H:i:s');
            $LeaveEncashment->enc_salary_date                   = nextMonthFirstDate($LeaveEncashment->enc_action_on);
            // leave status update in encashment application
            $LeaveEncashment->update();

            if ($LeaveEncashment->enc_status == $LeaveEncashment::APPROVED) {
                // leave debit update in ledger entry
                $LeaveCreditTransactions = new \App\Model\LeaveCreditTransactions;
                $LeaveCreditTransactions->employee_id               = $LeaveEncashment->employee_id;
                $LeaveCreditTransactions->branch_id                 = $LeaveEncashment->branch_id;
                $LeaveCreditTransactions->trn_credit_on             = $LeaveEncashment->enc_action_on; // used=0, trn_credit_on is consider trn_used_on no extra field added
                $LeaveCreditTransactions->trn_credit_days           = $LeaveEncashment->enc_days; // trn_credit_days is consider trn_used_days no extra field added
                $LeaveCreditTransactions->trn_leave_type_id         = $LeaveEncashment->enc_leave_type_id;
                $LeaveCreditTransactions->trn_type                  = 0; // Debit (Deduct)
                $LeaveCreditTransactions->trn_leave_application_id  = $LeaveEncashment->enc_entry_id;
                $LeaveCreditTransactions->year_id                   = $LeaveEncashment->year_id;
                $LeaveCreditTransactions->trn_remark                = 'Encashment approved by admin and debit transactions';
                $LeaveCreditTransactions->created_at                = $LeaveEncashment->enc_action_on;
                $LeaveCreditTransactions->updated_at                = $LeaveEncashment->enc_action_on;
                $LeaveCreditTransactions->save();

                // PL leave debit update in employee table
                $Employee->EmployeeLeaves->privilege_leave = $Employee->EmployeeLeaves->privilege_leave - $LeaveEncashment->enc_days;
                $Employee->EmployeeLeaves->update();
            } else if ($LeaveEncashment::REJECTED) {
                // no need to deduct in employee leave table so, leave debit/credit transaction entry alos no need
            }
            Session::flash('success', 'PL encashment status updated successfull.');
            DB::commit();

            return '1';
        } catch (\Throwable $th) {
            DB::rollback();
            $bug = $th->getMessage();
            info(__FILE__ . ':' . __LINE__ . ', bug=' . print_r($bug, 1));
            ob_end_flush();
            echo 'error';
        }
        return $request->post();
    }

    // admin mehtods ends

    public function Encashment(Request $request)
    {
        $LeaveEncashment = new LeaveEncashment;
        $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData();
        $LeaveEncashmentList = $LeaveEncashment->LeaveEncashmentList();
        $Employee = $LeaveEncashmentData['Employee'];

        return view('admin.leave.applyEncashment.Encashment', compact('LeaveEncashment', 'LeaveEncashmentList', 'Employee'));
    }

    public function EncashmentApply(Request $request)
    {
        $LeaveEncashment = new LeaveEncashment;
        $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData();
        $Employee = $LeaveEncashmentData['Employee'];
        $calanderYear = $LeaveEncashmentData['calanderYear'];
        $EmployeeLeaves = $Employee->EmployeeLeaves;

        if ($request->post()) {
            $input = Validator::make(
                $request->all(),
                [
                    'enc_days' => 'required|integer|min:1|max:' . $LeaveEncashmentData['CAN_USE_MAX_PL'],
                ],
                [
                    'enc_days.required' => __('leave.enc_days') . ' is required',
                    'enc_days.max' => 'The ' . __('leave.enc_days') . ' may not be greater than ' . $LeaveEncashmentData['CAN_USE_MAX_PL'],
                    'enc_days.min' => 'The ' . __('leave.enc_days') . ' must be at least 1',
                    'enc_days.integer' => 'The ' . __('leave.enc_days') . ' must be an integer.',
                ]
            );
            if ($input->fails()) {
                return redirect(Route('leave.EncashmentApply'))->with('error', $input->errors()->first());
            }

            $LeaveEncashmentExists = LeaveEncashment::where('employee_id', $Employee->employee_id)->where('year_id', $calanderYear->year_id)->first();
            if ($LeaveEncashmentExists) {
                return redirect(Route('leave.Encashment'))->with('error', 'You are already PL encashment applied!');
            }
            try {
                DB::beginTransaction();
                $DATA = $this->EncashmentCalculates($request, $array = true);
                $LeaveEncashment = new LeaveEncashment;
                $LeaveEncashment->employee_id = $Employee->employee_id;
                $LeaveEncashment->branch_id = $Employee->branch_id;
                $LeaveEncashment->year_id = $calanderYear->year_id;
                $LeaveEncashment->enc_leave_type_id = 3; // refer leave_type table 3 = Privilege Leave
                $LeaveEncashment->enc_submit_on = date('Y-m-d H:i:s');
                $LeaveEncashment->enc_open = $EmployeeLeaves->privilege_leave;
                $LeaveEncashment->enc_days = $request->enc_days;
                $LeaveEncashment->enc_close = $EmployeeLeaves->privilege_leave - $request->enc_days;
                $LeaveEncashment->enc_amount = $DATA['LEAVE_ENCASHMENT_AMOUNT'];
                $LeaveEncashment->save();
                DB::commit();

                return redirect(Route('leave.Encashment'))->with('success', 'PL encashment applied successfull');
                $bug = 0;
            } catch (\Exception $e) {
                DB::rollback();
                info($e);
                $bug = 1;
            }
        }

        return view('admin.leave.applyEncashment.apply_form', compact('Employee', 'EmployeeLeaves', 'calanderYear', 'LeaveEncashmentData'));
    }

    public function EncashmentCalculates(Request $request, $array = false)
    {
        if (!$request->enc_days && !isset($_POST['enc_days'])) {
            return '';
        }
        $employee_id = isset($_POST['employee_id']) ? $_POST['employee_id'] : null;
        $LeaveEncashment = new LeaveEncashment;
        $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData($employee_id);
        $Employee = $LeaveEncashmentData['Employee'];
        $calanderYear = $LeaveEncashmentData['calanderYear'];
        $EmployeeLeaves = $Employee->EmployeeLeaves;
        $BASIC          = $Employee->salary_ctc / 100 * Common::PERCENTAGE_BASIC;
        $DATA = $_POST;

        $DATA['CTC'] = $Employee->salary_ctc;
        $DATA['BASIC'] = $BASIC;
        $DATA['LEAVE_PERDAY_AMOUNT'] = round($BASIC / 30, 2);
        $DATA['LEAVE_ENCASHMENT_AMOUNT'] = round($DATA['enc_days'] * $BASIC / 30, 2);
        unset($DATA['_token']);
        if ($array) {
            return $DATA;
        }
        return view('admin.leave.applyEncashment.EncashmentCalculates', compact('DATA', 'Employee', 'EmployeeLeaves', 'calanderYear', 'LeaveEncashmentData'));
    }
} // end class ApplyForLeaveController
