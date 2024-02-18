<?php

namespace App\Http\Controllers\Leave;

use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Model\LeavePermission;
use App\Model\LeavePermissionCase;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;

class RequestedPermissionApplicationController extends Controller
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
            $results  = LeavePermission::with('employee')->orderBy('leave_permission_id', 'DESC')->paginate();
        } else {

            $results  = LeavePermission::with(['employee' => function ($q) {
                $q->where('supervisior_id', session('logged_session_data.employee_id'))
                    ->orwhere('function_head_id', session('logged_session_data.employee_id'));
            }])->orderBy('status', 'asc')
                ->orderBy('leave_permission_id', 'desc')
                ->paginate();
        }
        // dd($supervisorResults);

        return view('admin.leave.permissionApplication.permissionApplicationList', ['results' => $results]);
    }

    public function viewDetails($id)
    {
        $leaveApplicationData = LeavePermissionCase::with('employee')->where('leave_permission_id', $id)->where('status', 1)->first();

        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }

        return view('admin.leave.permissionApplication.permissionDetails', ['leaveApplicationData' => $leaveApplicationData]);
    }
    public function viewDetailsFunctionalHead($id)
    {
        //  dd($id);
        $leaveApplicationData = LeavePermissionCase::with('employee')->where('leave_permission_id', $id)->first();

        if (!$leaveApplicationData) {
            return response()->view('errors.404', [], 404);
        }

        return view('admin.leave.permissionApplication.permissionDetailsFunctionalHead', ['leaveApplicationData' => $leaveApplicationData]);
    }

    public function update(Request $request, $id)
    {

        $data = \App\Model\LeavePermissionCase::findOrFail($id);
        $input = $request->all(); // dd($input);
        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['approve_by'] = session('logged_session_data.employee_id');
        } else {
            $input['reject_date'] = date('Y-m-d');
            $input['reject_by'] = session('logged_session_data.employee_id');
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            if ($request->status == 2) {
                return redirect('requestedPermissionApplication')->with('success', 'Permission application approved successfully. ');
            } else {
                return redirect('requestedPermissionApplication')->with('error', 'Permission application reject successfully. ');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function approveOrRejectPermissionApplication(Request $request)
    {

        $data = LeavePermission::findOrFail($request->leave_permission_id);
        $input = $request->all();
        if ($request->status == 2) {
            $input['approve_date'] = date('Y-m-d');
            $input['department_approval_status'] = 1;
            $input['approve_by'] = session('logged_session_data.employee_id');
        } else {
            if ($request->status == 4) {
                $input['pass_date'] = date('Y-m-d');
                $input['status'] = 4;
                $input['pass_by'] = session('logged_session_data.employee_id');
            } else {
                $input['reject_date'] = date('Y-m-d');
                $input['department_approval_status'] = 2;
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

    public function approveOrRejectFunctionalHeadPermissionApplication(Request $request)
    {
        $data = LeavePermission::findOrFail($request->leave_permission_id);
        $input = $request->all();
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
            // info($input);
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                echo "approve";
            } else {
                echo "reject";
            }
        } else {
            echo "error";
        }
    }
}
