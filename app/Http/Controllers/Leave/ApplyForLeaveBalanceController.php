<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyForLeaveBalanceRequest;
use App\Model\LeaveBalance;
use App\Repositories\CommonRepository;
use App\Repositories\LeaveRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApplyForLeaveBalanceController extends Controller
{

    protected $commonRepository;
    protected $leaveRepository;

    public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->leaveRepository  = $leaveRepository;
    }



    public function index()
    {

        $results = LeaveBalance::with(['employee', 'leaveType'])
            ->orderBy('leave_balance_id', 'desc')
            ->paginate(10);

        return view('admin.leave.applyLeaveBalance.leave_balance_index', ['results' => $results]);
    }
    public function create()
    {

        $leaveTypeList   = $this->commonRepository->leaveBalanceTypeList();

        $getEmployeeInfo = $this->commonRepository->getEmployeeInfo(Auth::user()->user_id);
        $employeeList = $this->commonRepository->employeeList();
        return view('admin.leave.applyLeaveBalance.leave_balance_form', ['leaveTypeList' => $leaveTypeList, 'getEmployeeInfo' => $getEmployeeInfo, 'employeeList' => $employeeList]);
    }
    public function store(ApplyForLeaveBalanceRequest $request)
    {
        $currentYear = Carbon::now()->year;
        $input                    = $request->all();
        $input['year']            = $currentYear - 1;

        $bug = 0;
        try {
            LeaveBalance::createTrait($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('leaveBalance')->with('success', 'Employee Leave Balance Added Successfully.');
        } else {
            return redirect('leaveBalance')->with('error', $e->getMessage());
        }
    }
    public function edit($id)
    {

        $editModeData = LeaveBalance::with('employee', 'leaveType')->findOrFail($id);
        $employeeList = $this->commonRepository->employeeList();
        $leaveTypeList   = $this->commonRepository->leaveBalanceTypeList();

        return view('admin.leave.applyLeaveBalance.leave_balance_form', ['editModeData' => $editModeData, 'employeeList' => $employeeList, 'leaveTypeList' => $leaveTypeList]);
    }
    public function update(ApplyForLeaveBalanceRequest $request, $id)
    {
        $currentYear = Carbon::now()->year;
        $LeaveBalance = LeaveBalance::where('leave_balance_id', $id)->first();
        $input              = $request->all();
        $input['year']            = $currentYear - 1;

        try {
            $LeaveBalance->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Public holiday successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function destroy($id)
    {
        try {
            $leavebalanceDetails = LeaveBalance::findOrFail($id);
            $leavebalanceDetails->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } elseif ($bug == 1451) {
            echo 'hasForeignKey';
        } else {
            echo 'error';
        }
    }
}
