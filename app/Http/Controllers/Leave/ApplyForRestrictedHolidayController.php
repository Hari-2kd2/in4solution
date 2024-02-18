<?php

namespace App\Http\Controllers\Leave;

use App\User;
use Carbon\Carbon;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use WpOrg\Requests\Session;
use App\Model\RhApplication;
use Illuminate\Http\Request;
use App\Model\LeaveApplication;
use App\Model\RestrictedHoliday;
use Illuminate\Support\Facades\DB;
use App\Model\PaidLeaveApplication;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use App\Notifications\LeaveNotification;
use App\Http\Requests\ApplyForLeaveRequest;
use App\Http\Requests\RhApplicationRequest;
use Illuminate\Support\Facades\Notification;

class ApplyForRestrictedHolidayController extends Controller
{

    // protected $commonRepository;
    // protected $leaveRepository;

    // public function __construct(CommonRepository $commonRepository, LeaveRepository $leaveRepository)
    // {
    //     $this->commonRepository = $commonRepository;
    //     $this->leaveRepository = $leaveRepository;
    // }

    public function index()
    {
        $calanderYear = calanderYear::currentYear();
        $RhApplicationList = RhApplication::with(['employee', 'RestrictedHoliday', 'calanderYear'])
        ->where('employee_id', session('logged_session_data.employee_id'))->where('year_id', $calanderYear->year_id)
        ->orderBy('rh_application_id', 'desc')
        ->paginate(10);
        $RestrictedHolidayList = RestrictedHoliday::where('year_id', $calanderYear->year_id)->get();
        return view('admin.leave.applyForRestrictedHoliday.index', ['RhApplicationList' => $RhApplicationList, 'RestrictedHolidayList' => $RestrictedHolidayList]);
    }
    
    public function create()
    {
        $calanderYear = calanderYear::currentYear();
        $RestrictedHolidayList = RestrictedHoliday::where('year_id', $calanderYear->year_id)->orderBy('holiday_date')->get();
        $RhApplication = new RhApplication;
        $RhApplication->employee_id = session('logged_session_data.employee_id');
        $RhApplication->branch_id = session('logged_session_data.branch_id');
        $RhApplication->year_id = $calanderYear->year_id;
        return view('admin.leave.applyForRestrictedHoliday.restricted_holiday_application_form', ['RhApplication'=>$RhApplication, 'RestrictedHolidayList' => $RestrictedHolidayList ]);
    }
    
    public function store(RhApplicationRequest $request)
    {
        $input = $request->all();
        $calanderYear = calanderYear::currentYear();
        $RestrictedHoliday = RestrictedHoliday::find($request->holiday_id);
        $input['employee_id'] = session('logged_session_data.employee_id');
        $input['year_id'] = $calanderYear->year_id;
        $input['holiday_date'] = $RestrictedHoliday->holiday_date;
        $input['application_date'] = date('Y-m-d');

        try {
            $RhApplicationExist=RhApplication::where('year_id',$calanderYear->year_id)->where('holiday_date','>=',$RestrictedHoliday->holiday_date)->where('holiday_date','<=',$RestrictedHoliday->holiday_date)->where('status', 1)->where('employee_id', $input['employee_id'])->first();
            if(!$RhApplicationExist)
            {
                RhApplication::createTrait($input);
            }else{                
                return redirect('applyForRestrictedHoliday')->with('error', 'RH Leave Application Already Exist On Selected Date!.');
            }
            $bug = 0;
        } catch (\Exception $e) {
            dd($e->getMessage());
            $bug = 1;
        }
        
        if ($bug == 0) {
            return redirect('applyForRestrictedHoliday')->with('success', 'Restricted holiday successfully saved.');
        } else {
            return redirect('applyForRestrictedHoliday')->with('error', 'Something Error Found !, Please try again.');
        }
    }
    
    public function restrictedHolidayList(Request $request) {
        $results = [];
        $LoggedEmployee = Employee::loggedEmployee();
        $supervisorIds = $LoggedEmployee->supervisorIds();
        $roleId = session('logged_session_data.role_id');
        $branchId = session('logged_session_data.branch_id');
        $selectedbranchId = session('selected_branchId');
        if($roleId==1) {
            $branchId = $selectedbranchId;
        }
        if($roleId==1) {
            $RhApplicationList = DB::table('restricted_holiday_application')
                ->join('employee', 'employee.employee_id', '=', 'restricted_holiday_application.employee_id')
                // ->where('employee.branch_id', $branchId)
                ->orderBy('rh_application_id', 'desc')
                ->orderBy('employee.status', 'asc')
                ->get();
        } else {
            $RhApplicationList = DB::table('restricted_holiday_application')
                ->join('employee', 'employee.employee_id', '=', 'restricted_holiday_application.employee_id')
                ->whereIn('employee.employee_id', $supervisorIds)
                ->where('employee.employee_id', '!=', session('logged_session_data.employee_id'))
                // ->where('employee.branch_id', $branchId)
                ->orderBy('rh_application_id', 'desc')
                ->orderBy('employee.status', 'asc')
                ->get();
        }
    
        return view('admin.leave.applyForRestrictedHoliday.restricted_holiday_application_list', ['RhApplicationList' => $RhApplicationList ]);
    }
    
    public function RhviewDetails($id)
    {
        $RhApplication = \App\Model\RhApplicationCase::where('rh_application_id', $id)->first();
        // dd($RhApplication);
        if (!$RhApplication) {
            return response()->view('errors.404', [], 404);
        }
        return view('admin.leave.RestrictedHolidayLeaveApplication.RhviewDetails', ['RhApplication' => $RhApplication]);
    }

    public function Rhupdate(Request $request, $id)
    {

        $data = \App\Model\RhApplicationCase::findOrFail($id);
        $input = $request->all();
        if ($request->status == 2) {
            $data->status = $input['status'] = LeaveStatus::$APPROVE;
            $data->approve_date = $input['approve_date'] = date('Y-m-d');
            $data->approve_by = $input['approve_by'] = session('logged_session_data.employee_id');
        } else {
            $data->status = $input['status'] = LeaveStatus::$REJECT;
            $data->reject_date = $input['reject_date'] = date('Y-m-d');
            $data->reject_by = $input['reject_by'] = session('logged_session_data.employee_id');
        }
        // dd($input);
        try {
            // $data->update($input);
            $data->update();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            if ($request->status == 2) {
                return redirect(Route('restrictedHolidayList.index'))->with('success', 'RH application approved successfully. ');
            } else {
                return redirect(Route('restrictedHolidayList.index'))->with('success', 'RH application reject successfully. ');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

}
