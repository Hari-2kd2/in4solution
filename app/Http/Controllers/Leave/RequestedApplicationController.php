<?php

namespace App\Http\Controllers\Leave;

use App\Model\CompOff;
use App\Model\Employee;
use App\Model\Keypeople;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\EmployeeLeaves;
use App\Model\LeaveApplication;
use App\Mail\LeaveApplicationMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Repositories\LeaveRepository;


class RequestedApplicationController extends Controller
{

    protected $leaveRepository;

    public function __construct(LeaveRepository $leaveRepository)
    {
        $this->leaveRepository = $leaveRepository;
    }

    public function index()
    {
        $results = [];

        if (session('logged_session_data.role_id') == 1 || session('logged_session_data.role_id') == 2) {
            $results  = LeaveApplication::with('employee')->orderBy('leave_application_id', 'DESC')->paginate();
        } else {
            $results  =  LeaveApplication::with(['employee' => function ($q) {
                $q->where('supervisior_id', session('logged_session_data.employee_id'))
                    ->orwhere('function_head_id', session('logged_session_data.employee_id'));
            }])->orderBy('status', 'asc')
                ->orderBy('leave_application_id', 'desc')
                ->paginate();
        }

        return view('admin.leave.leaveApplication.leaveApplicationList', ['results' => $results]);
    }

    public function viewDetails($id)
    {
        $leaveApplicationData = LeaveApplication::where('leave_application_id', $id)->where('status', 1)->first();
        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }
        $leaveApplicationData->employee = Employee::find($leaveApplicationData->employee_id);

        $leaveBalanceArr = $this->leaveRepository->calculateEmployeeLeaveBalanceArray($leaveApplicationData->leave_type_id, $leaveApplicationData->employee_id);

        return view('admin.leave.leaveApplication.leaveDetails', ['leaveApplicationData' => $leaveApplicationData, 'leaveBalanceArr' => $leaveBalanceArr]);
    }
    public function viewDetailsFunctionalHead($id)
    {
        // dd($id);
        $leaveApplicationData = LeaveApplication::where('leave_application_id', $id)->first();
        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }
        $leaveApplicationData->employee = Employee::find($leaveApplicationData->employee_id);

        $leaveBalanceArr = $this->leaveRepository->calculateEmployeeLeaveBalanceArray($leaveApplicationData->leave_type_id, $leaveApplicationData->employee_id);
        // dd([$leaveApplicationData]);
        return view('admin.leave.leaveApplication.leaveDetailsFunctionalHead', ['leaveApplicationData' => $leaveApplicationData, 'leaveBalanceArr' => $leaveBalanceArr]);
    }

    public function update(Request $request, $id)
    {
        $LeaveApplication = LeaveApplication::findOrFail($id);
        $input = $request->all();
        $EmployeeLeaves = $LeaveApplication->employee->EmployeeLeaves;
        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['approve_by'] = session('logged_session_data.employee_id');
        } else {
            if ($request->status == 4) {
                $input['pass_date'] = date('Y-m-d');
                $input['pass_by'] = session('logged_session_data.employee_id');
            } else {
                $input['reject_date'] = date('Y-m-d');
                $input['reject_by'] = session('logged_session_data.employee_id');
            }
        }

        try {
            DB::beginTransaction();
            $supervisor = $LeaveApplication->employee->supervisor ?? NULL;
            $employee = $LeaveApplication->employee ?? NULL;
            $LeaveApplication->update($input);
            $LeaveApplication->updateLeaves();

            if ($request->status == 2 && ($employee && $employee->email) || ($supervisor && $supervisor->email)) {
                $raw_data = LeaveApplication::where('leave_application_id', $id)->first()->toArray();
                $update_data = [
                    'approve_by' => $input['approve_by'],
                    'approve_date' => date('Y-m-d'),
                    'approve_name' => $LeaveApplication->approveBy->first_name . ' ' . $LeaveApplication->approveBy->last_name,
                    'remarks' => 'approved',
                    'status' => 2,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'finger_id' => $LeaveApplication->employee->finger_id,
                    'name' => $LeaveApplication->employee->first_name . ' ' . $LeaveApplication->employee->last_name,
                    'date' => $LeaveApplication->application_date,
                    'from' => $LeaveApplication->application_from_date,
                    'to' => $LeaveApplication->application_to_date,
                    'type' => $LeaveApplication->leaveType->leave_type_name,
                    'days' => $LeaveApplication->number_of_day,
                    'url_a' => '',
                    'color' => 'green',
                ];

                $toEMAIL = $employee->email ? $employee->email : $supervisor->email;
                $DATA['body'] = \array_merge($raw_data, $update_data);
                $SUBJECT = 'Leave approved notification (' . $update_data['finger_id'] . ', ' . $update_data['name'] . ' )';
                $OPTIONALS = [];
                $Keypeople = Keypeople::where('branch_id', session('logged_session_data.branch_id'))->first();

                // send all approved leave application to HR if key people setting enabled
                if ($Keypeople) {
                    $hrEmailArray = [];
                    $DirectorsEmailArray = [];
                    // if HR email setting enabled
                    if ($Keypeople->key_hr_emails) {
                        $hrEmailArray = explode(',', $Keypeople->key_hr_emails);
                    }
                    // if directors email setting enabled
                    if ($Keypeople->key_director_emails) {
                        $isKeyEmployee = Keypeople::whereRaw("FIND_IN_SET('" . $LeaveApplication->employee_id . "', key_user_ids)")->first();
                        // if leave take employee is key people organization
                        if ($isKeyEmployee) {
                            $DirectorsEmailArray = explode(',', $Keypeople->key_director_emails);
                        }
                        $OPTIONALS = array_merge($hrEmailArray, $DirectorsEmailArray);
                    }
                }
                \App\Components\Common::mailing('emails.LeaveApplicationApproved', $toEMAIL, $SUBJECT, $DATA, $OPTIONALS = []);
            }
            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
            info($e);
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                return redirect('requestedApplication')->with('success', 'Leave application approved successfully. ');
            } else {
                if ($request->status == 4) {
                    return redirect('requestedApplication')->with('success', 'Leave application Passed successfully. ');
                } else {
                    return redirect('requestedApplication')->with('success', 'Leave application reject successfully. ');
                }
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function approveOrRejectLeaveApplication(Request $request)
    {
        $data = LeaveApplication::findOrFail($request->leave_application_id);
        $input = $request->all();

        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['approve_by'] = session('logged_session_data.employee_id');
        } else {
            if ($request->status == 4) {
                $input['pass_date'] = date('Y-m-d');
                $input['pass_by'] = session('logged_session_data.employee_id');
            } else {
                $input['reject_date'] = date('Y-m-d');
                $input['reject_by'] = session('logged_session_data.employee_id');
            }
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                echo "approve";
            } else {
                if ($request->status == 4) {
                    echo "pass";
                } else {
                    echo "reject";
                }
            }
        } else {
            echo "error";
        }
    }
    public function approveOrRejectFunctionalHeadLeaveApplication(Request $request)
    {
        $data = LeaveApplication::findOrFail($request->leave_application_id);
        $input = $request->all();
        $input['functional_head_remark'] = $input['remarks'];
        unset($input['remarks']);

        if ($request->status == 2) {
            $input['functional_head_status'] = 2;
            $input['functional_head_approve_date'] = date('Y-m-d');
            $input['functional_head_approved_by'] = session('logged_session_data.employee_id');
        } else {
            $input['functional_head_status'] = 3;
            $input['functional_head_reject_date'] = date('Y-m-d');
            $input['functional_head_reject_by'] = session('logged_session_data.employee_id');
        }
        unset($input['status']);

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            info($e);
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                // $data = LeaveApplication::findOrFail($request->leave_application_id)->first();
                // $employee = Employee::where('employee_id', $data->employee_id)->select('supervisor_id')->first();
                // $functionalHead = Employee::where('employee_id', $employee->supervisor_id)->first();
                // if ($functionalHead != '') {
                //     if ($functionalHead->email) {
                //         $maildata = Common::mail('emails/mail', $functionalHead->email, 'Leave Request Notification', ['head_name' => $functionalHead->first_name . ' ' . $functionalHead->last_name, 'request_info' => $employee->first_name . ' ' . $employee->last_name . ', have requested for Leave (Purpose: ' . $request->purpose . ') from ' . ' ' . dateConvertFormtoDB($request->application_from_date) . ' to ' . dateConvertFormtoDB($request->application_to_date), 'status_info' => '']);
                //     }
                // }
                echo "approve";
            } else {
                echo "reject";
            }
        } else {
            echo "error";
        }
    }
}
