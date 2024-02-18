<?php

namespace App\Http\Controllers\Api;

use App\Model\Employee;
use App\Model\Keypeople;
use App\Model\RhApplication;
use Illuminate\Http\Request;
use App\Model\EmployeeLeaves;
use App\Model\LeaveApplication;
use App\Mail\LeaveApplicationMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\AuthController;

class RequestedApplicationController extends Controller
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

    public function validateEmployee(Request $request) {
        $employee_id = $request->employee_id;
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $AdminEmployee = Employee::find($employee_id);
        if(!$AdminEmployee) {
            return $this->controller->custom_error("Employee record not found.");
        } else {
            $User = $AdminEmployee->userName;
            if(!$User) {
                return $this->controller->custom_error("Employee user record not found.");
            }
            if($User->role_id>3) {
                return $this->controller->custom_error("Leave application access denied.", $User);
            }
        }
        return $AdminEmployee;
    }

    public function view(Request $request) {
        $employee_id = $request->employee_id;
        
        $AdminEmployee = $this->validateEmployee($request);
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
            'leave_application_id' => 'required|exists:leave_application,leave_application_id',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }
        $results['leave_application_id'] = $request->leave_application_id;
        $results['statusList'] = $this->leaveRepository->statusList;
        $results['processList'] = $this->leaveRepository->processList;
        $LeaveApplication = LeaveApplication::where('leave_application_id', $request->leave_application_id)->with(['employee', 'leaveType'])->first();
        $leaveTransactions = $LeaveApplication->employee->leaveTransactions();
        $EmployeeLeaves = $LeaveApplication->employee->EmployeeLeaves ?? new EmployeeLeaves;
        $EmployeeLeaves->gender = $LeaveApplication->employee->gender;
        
        unset($EmployeeLeaves->leave_id);
        unset($EmployeeLeaves->created_at);
        unset($EmployeeLeaves->updated_at);

        $paternity_leave_ploicy = $LeaveApplication->employee->paternity_leave_ploicy();
        $maternity_leave_ploicy = $LeaveApplication->employee->maternity_leave_ploicy();
        if ($paternity_leave_ploicy && isset($paternity_leave_ploicy['status']) && $paternity_leave_ploicy['status']) {
            $EmployeeLeaves->paternity_leave = $paternity_leave_ploicy['paternity_leave'];
            unset($EmployeeLeaves->maternity_leave);
        } elseif($maternity_leave_ploicy && isset($maternity_leave_ploicy['status']) && $maternity_leave_ploicy['status']) {
            $EmployeeLeaves->maternity_leave = $maternity_leave_ploicy['maternity_leave'];
            unset($EmployeeLeaves->paternity_leave);
        } else {
            unset($EmployeeLeaves->maternity_leave);
            unset($EmployeeLeaves->paternity_leave);
        }

        $results['allTypeBalance'] = $EmployeeLeaves;
        $results['leaveTransactions'] = $leaveTransactions;
        $results['LeaveApplication'] = $LeaveApplication;
        $results['paternity_leave_ploicy'] = $paternity_leave_ploicy;
        $results['maternity_leave_ploicy'] = $maternity_leave_ploicy;
        return $this->controller->success("View leave application records successfully received.", $results);
    }

    // admin management API starts here
    public function rhIndex(Request $request) {
        $employee_id = $request->employee_id;
        $AdminEmployee = $this->validateEmployee($request);

        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $supervisorIds = $AdminEmployee->supervisorIds($AdminEmployee->employee_id);
        $User = $AdminEmployee->userName;
        $results = RhApplication::with(['employee', 'RestrictedHoliday'])
            ->whereIn('employee_id', $supervisorIds)
            ->where('employee_id', '!=', $AdminEmployee->employee_id)
            ->orderBy('rh_application_id', 'desc')
            ->limit(100)
            ->get();

        $resultsAll['statusList'] = $this->leaveRepository->statusList;
        $resultsAll['processList'] = $this->leaveRepository->processList;


        return $this->controller->success("RH Leave application records successfully received..", $results, $resultsAll);
    }
    
    public function rhView(Request $request) {
        $employee_id = $request->employee_id;
        $AdminEmployee = $this->validateEmployee($request);

        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
            'rh_application_id' => 'required|exists:restricted_holiday_application,rh_application_id',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }
        $rh_balance=$AdminEmployee->rh_balance();
        $results['rh_application_id'] = $request->rh_application_id;
        $results['rh_balance'] = $rh_balance;
        $results['checkRhLeaveAvailability'] = $rh_balance > 0 ? 'Available' : 'Not Available';
        $results['statusList'] = $this->leaveRepository->statusList;
        $results['processList'] = $this->leaveRepository->processList;
        $results['LeaveApplication'] = RhApplication::where('rh_application_id', $request->rh_application_id)->with(['employee', 'RestrictedHoliday'])->first();
        $User = $AdminEmployee->userName;
        return $this->controller->success("View leave RH application records successfully received.", $results);

    }
    
    public function rhUpdate(Request $request) {
        $AdminEmployee = $this->validateEmployee($request);
        $input = Validator::make($request->all(), [
            'rh_application_id' => 'required|exists:restricted_holiday_application,rh_application_id',
            'status' => 'required|in:'. implode(',', array_keys($this->leaveRepository->statusList)),
            'remarks' => 'nullable',
        ]);

        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $RhApplication = RhApplication::findOrFail($request->rh_application_id);

        if($RhApplication->status!=1) {
            return Controller::custom_error('Already responded the RH leave application!');
        }

        $RhApplication->status = $request->status;
        if ($request->status == 2) {
            $RhApplication->approve_date = date('Y-m-d');
            $RhApplication->approve_by = $AdminEmployee->employee_id;
        } else {
            $RhApplication->reject_date = date('Y-m-d');
            $RhApplication->reject_by = $AdminEmployee->employee_id;
        }
        try {
            $RhApplication->remarks = $request->remarks;
            $RhApplication->update();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                return $this->controller->success("RH application approved successfully received.", $RhApplication);
            } else {
                return $this->controller->success("RH application rejected successfully received.", $RhApplication);
            }
        } else {
            return $this->controller->custom_error('Something Error Found !, Please try again.', []);
        }
    }

    public function index(Request $request)
    {
        $employee_id = $request->employee_id;
        $AdminEmployee = $this->validateEmployee($request);
        $User = $AdminEmployee->userName;

        $supervisorIds = $AdminEmployee->supervisorIds($AdminEmployee->employee_id);
        $allResult = [];
        $results = LeaveApplication::with(['employee', 'leaveType'])
            ->whereIn('employee_id', $supervisorIds)
            ->where('employee_id', '!=', $AdminEmployee->employee_id)
            ->orderBy('leave_application_id', 'desc')
            ->limit(100)
            ->get();

        $allResult['statusList'] = $this->leaveRepository->statusList;
        $allResult['processList'] = $this->leaveRepository->processList;
        return $this->controller->success("Leave application records successfully received..", $results, $allResult);
    }

    public function update(Request $request)
    { 
        $AdminEmployee = $this->validateEmployee($request);
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
            'remarks' => 'nullable',
            'leave_application_id' => 'required|exists:leave_application,leave_application_id',
            'status' => 'required|in:'. implode(',', array_keys($this->leaveRepository->statusList)),
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $LeaveApplication = LeaveApplication::findOrFail($request->leave_application_id);
        if($LeaveApplication->status!=1) {
            return Controller::custom_error('Already responded the leave application!');
        }
        $LeaveApplication->status = $request->status;
        $LeaveApplication->remarks = $request->remarks;
        $EmployeeLeaves = EmployeeLeaves::where('employee_id',$LeaveApplication->employee_id)->first();
        if ($request->status == 2) {
            $LeaveApplication->approve_date = date('Y-m-d');
            $LeaveApplication->approve_by = $AdminEmployee->employee_id;
        } else {
            $LeaveApplication->reject_date = date('Y-m-d');
            $LeaveApplication->reject_by = $AdminEmployee->employee_id;
        }
        
        try {
            DB::beginTransaction();
            $supervisor = $LeaveApplication->employee && $LeaveApplication->employee->supervisor ? $LeaveApplication->employee->supervisor : NULL;
            $LeaveApplication->update();
            if(isset($EmployeeLeaves)){
                if($request->status == 2){ 
                    if($LeaveApplication->leave_type_id == 1){
                        $employeeLeave['casual_leave'] =  $EmployeeLeaves->casual_leave - $LeaveApplication->number_of_day;
                        $EmployeeLeaves->update($employeeLeave);
                    }elseif($LeaveApplication->leave_type_id == 2){
                        $employeeLeave['sick_leave'] = $EmployeeLeaves->sick_leave - $LeaveApplication->number_of_day;
                        $EmployeeLeaves->update($employeeLeave);
                    }elseif($LeaveApplication->leave_type_id == 3){
                        $employeeLeave['privilege_leave'] = $EmployeeLeaves->privilege_leave - $LeaveApplication->number_of_day;
                        $EmployeeLeaves->update($employeeLeave);
                    }                    
                    
                }
            }
            // $test['LeaveApplication'] = $LeaveApplication;
            // $test['LeaveApplication->employee'] = $LeaveApplication->employee;
            // return $this->custom_error($test);
            if($request->status == 2 && $supervisor && $supervisor->email) {
                $raw_data = LeaveApplication::where('leave_application_id', $request->leave_application_id)->first()->toArray();
                $update_data = [
                    'approve_by' => $input['approve_by'],
                    'approve_date' => date('Y-m-d'),
                    'approve_name' => $LeaveApplication->approveBy->first_name.' '.$LeaveApplication->approveBy->last_name,
                    'remarks' => 'approved',
                    'status' => 2,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'finger_id' => $LeaveApplication->employee->finger_id,
                    'name' => $LeaveApplication->employee->first_name.' '.$LeaveApplication->employee->last_name,
                    'date' => $LeaveApplication->application_date,
                    'from' => $LeaveApplication->application_from_date,
                    'to' => $LeaveApplication->application_to_date,
                    'type' => $LeaveApplication->leaveType->leave_type_name,
                    'days' => $LeaveApplication->number_of_day,
                    'url_a' => '',
                    'color' => 'green',
                ];

                $toEMAIL = $supervisor->email;
                $DATA['body'] = \array_merge($raw_data, $update_data);
                $SUBJECT = 'Leave approved notification ('.$update_data['finger_id'].', '.$update_data['name'].' )';
                $OPTIONALS = [];
                $Keypeople = Keypeople::where('branch_id', $AdminEmployee->branch_id)->first();

                // send all approved leave application to HR if key people setting enabled
                if($Keypeople) {
                    $hrEmailArray = [];
                    $DirectorsEmailArray = [];
                    // if HR email setting enabled
                    if($Keypeople->key_hr_emails) {
                        $hrEmailArray = explode(',', $Keypeople->key_hr_emails);
                    }
                    // if directors email setting enabled
                    if($Keypeople->key_director_emails) {
                        $isKeyEmployee = Keypeople::whereRaw("FIND_IN_SET('".$LeaveApplication->employee->employee_id."', key_user_ids)")->first();
                        // if leave take employee is key people organization
                        if($isKeyEmployee) {
                            $DirectorsEmailArray = explode(',', $Keypeople->key_director_emails);
                        }
                        $OPTIONALS = array_merge($hrEmailArray, $DirectorsEmailArray);
                    }
                }
                \App\Components\Common::mailing('emails.LeaveApplicationApproved', $toEMAIL, $SUBJECT, $DATA, $OPTIONALS=[]);
            }
            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
            return $this->controller->custom_error("Something went wrong.", [$LeaveApplication]);
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                return $this->controller->success("Leave application approved successfully.", $LeaveApplication);
            } else {
                return $this->controller->success("Leave application reject successfully.", $LeaveApplication);
            }
        } else {
            return $this->controller->custom_error('Something Error Found !, Please try again.', []);
        }
    }
    // admin management API ends here
    
}
