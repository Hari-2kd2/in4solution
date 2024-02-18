<?php

namespace App\Http\Controllers\Api;

use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\LeavePermission;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use Illuminate\Support\Facades\Validator;

class ApplyForPermissionController extends Controller
{
    protected $commonRepository;
    protected $leaveRepository;
    protected $LeavePermission;

    public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository, LeavePermission $LeavePermission)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository  = $leaveRepository;
        $this->LeavePermission  = $LeavePermission;

    }

    public function index(Request $request)
    {
        $data = [];
        $employee = Employee::where('employee_id', $request->employee_id)->first();
        $permission_data = LeavePermission::with(['employee','approveBy', 'rejectBy','approveByFunctionalHead','rejectByFunctionalHead'])
            ->where('employee_id', $employee->employee_id)
            ->orderBy('leave_permission_date', 'desc')
            ->get();
        $permission_status = "";

        foreach ($permission_data as $permission_row) {

            if ($permission_row->status == 3) {
                $permission_status = "rejected";
            } elseif (($permission_row->status == 2)) {
                $permission_status = "Approved";
            } elseif (($permission_row->status == 1)) {
                $permission_status = "Pending";
            } else {
                $permission_status = "Pending";
            }
            if ($permission_row->functional_head_status == 3) {
                $functional_head__status = "rejected";
            } elseif (($permission_row->functional_head_status == 2)) {
                $functional_head__status = "Approved";
            } elseif (($permission_row->functional_head_status == 1)) {
                $functional_head__status = "Pending";
            } else {
                $functional_head__status = "Pending";
            }

            $data[] = array(
                'leave_permission_date' => date("d-m-Y", strtotime($permission_row->leave_permission_date)),
                'permission_duration' => $permission_row->permission_duration,
                'from_time' => $permission_row->from_time,
                'to_time' => $permission_row->to_time,
                'leave_permission_purpose' => $permission_row->leave_permission_purpose,
                'permission_status' => $permission_status,
                'functional_head__status' => $functional_head__status,
                'p_status' => $permission_row->status,
                'first_name' => $employee->first_name,
                'finger_id' => $employee->finger_id,
                'remark' => $permission_row->remarks,
            );
        }

        if ($data) {
            return response()->json([
                'status' => true,
                'data'         => $data,
                'message'      => 'Permission Request Details Successfully Received',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No Data Found',
            ], 200);
        }
    }

    public function dateChanged(Request $request) {
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',
            'permission_date' => 'required',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }
        $this->LeavePermission->employee_id = $request->employee_id;
        $INFO = $this->LeavePermission->getPermissionsInfo($request);
        return Controller::success("Permission Date Changes Info Successfully Received!", $INFO);
    }

    public function create(Request $request)
    {

        $data = [];
        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo($request->employee_id);
        $this->LeavePermission->employee_id = $request->employee_id;
        $INFO = $this->LeavePermission->getPermissionsInfo($request);
        $data = [
            'permissionBalance' => $INFO['PERMISSION_BALANCE'],
            'checkPermissionAvailability' => $INFO['PERMISSION_BALANCE'] > 0 ? 'Available' : 'Not Available',
            'getEmployeeInfo' => $getEmployeeInfo,
            'INFO' => $INFO,
        ];
        return Controller::success("Permission Details Successfully Received!!!", $data);
    }


    public function store(Request $request)

    {
        $employee_data = Employee::where('employee_id', $request->employee_id)->first();

        $input                              = $request->all();
        $input['leave_permission_date']     = dateConvertFormtoDB($request->permission_date);
        $input['permission_duration']       = convertMinutesToHour(Common::PER_PERMISSION_HOUR * 60);
        $input['leave_permission_purpose']  = $request->purpose;
        $input['branch_id']                 = $employee_data->branch_id;
        $input['department_head']           = $employee_data->supervisor_id;
        $input['status']           =1;
        $input['functional_head_status']           =1;

        if (empty($request->permission_date) || $request->permission_date === '0000-00-00') {
            return $this->responseWithError('Permission Date Not Given');
        }

        if (empty($request->purpose)) {
            return $this->responseWithError('Permission Purpose Not Given');
        }

        $INFO = $this->LeavePermission->getPermissionsInfo($request);
        $messageIs = $this->LeavePermission->allChecks($request, $INFO);
        if($messageIs) {
            return Controller::custom_error($messageIs);
        }

        try {
            $hod = Employee::where('employee_id', $employee_data->supervisor_id)->first();
            LeavePermission::create($input);
            if ($hod && $hod->email) {
                $maildata = Common::mail('emails/mail', $hod->email, 'Permission Request Notification', [
                    'head_name' => $hod->first_name . ' ' . $hod->last_name,
                    'request_info' => $employee_data->first_name . ' ' . $employee_data->last_name . ', have requested for permission (Purpose: ' . $request->purpose . ') On ' . ' ' . dateConvertFormtoDB($request->permission_date),
                    'status_info' => '',
                ]);
            }
            return $this->responseWithSuccess('Permission Request Sent Successfully.');
        } catch (\Throwable $th) {
            return $this->responseWithError($th->getMessage());
        }
    }
    private function responseWithError($message)
    {
        return response()->json([
            'message' => $message,
            'status' => false,
        ], 200);
    }

    private function responseWithSuccess($message)
    {
        return response()->json([
            'message' => $message,
            'status' => true,
        ], 200);
    }
}
