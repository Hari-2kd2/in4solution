<?php

namespace App\Http\Controllers\Api;

use App\Model\Employee;
use App\Components\Common;
use App\Model\calanderYear;
use App\Model\RhApplication;
use Illuminate\Http\Request;
use App\Model\RestrictedHoliday;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveRepository;
use App\Repositories\CommonRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class ApplyForRhController extends Controller
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
        $calanderYear = calanderYear::currentYear();
        $employee_id = $request->employee_id;
        $today = date('Y-m-d');
        $results = RhApplication::with(['RestrictedHoliday', 'approveBy', 'rejectBy'])
        ->where('employee_id', $employee_id)
        ->orderBy('rh_application_id', 'desc')
        ->get();
        
        $Employee = Employee::find($employee_id);
        if(!$Employee) {
            return $this->controller->custom_error('Employee record not found!');
        }

        // $RhLeaves = RestrictedHoliday::where('branch_id', $Employee->branch_id)->get();
        $RhLeaves = RestrictedHoliday::where('year_id', $calanderYear->year_id)->where('branch_id', $Employee->branch_id)->orderBy('holiday_date')->selectRaw("*, if(`holiday_date`>'$today', 1, 0) AS holiday_status")->get();
        
        $RhList = [];
        foreach ($RhLeaves as $RestrictedHoliday) {
            $row = [];
            $row['holiday_id'] = $RestrictedHoliday->holiday_id;
            $row['branch_id'] = $RestrictedHoliday->branch_id;
            $row['holiday_name'] = dateConvertDBtoForm($RestrictedHoliday->holiday_date) . ' -> ' . $RestrictedHoliday->holiday_name;
            if($today<$RestrictedHoliday->holiday_date) {
                $row['holiday_status'] = true;
            } else {
                $row['holiday_status'] = false;
            }
            $RhList[] = $row;
        }

        return $this->controller->successdualdata("Datas Successfully Received!!!", $results, $RhList);
    }

    public function create(Request $request)
    {
        $auth_user_id = $request->employee_id;
        $today = date('Y-m-d');
        $calanderYear = calanderYear::currentYear();
        $Employee = Employee::find($auth_user_id);
        $RestrictedHolidayList = RestrictedHoliday::where('year_id', $calanderYear->year_id)->where('branch_id', $Employee->branch_id)->orderBy('holiday_date')->selectRaw("holiday_id, branch_id, CONCAT(holiday_name, ' - ', DATE_FORMAT(holiday_date, '%d/%m/%Y')) AS holiday_name, holiday_date, year_id, created_at, updated_at, if(`holiday_date`>'$today', 1, 0) AS holiday_status")->get();
        $RhApplication = new RhApplication;
        $rh_balance=$Employee->rh_balance();
        $RhApplication->employee_id = $Employee->employee_id;
        $RhApplication->branch_id = $Employee->branch_id;
        $RhApplication->year_id = $calanderYear->year_id;
        $data = [
            'rh_balance' => $rh_balance,
            'checkRhLeaveAvailability' => $rh_balance > 0 ? 'Available' : 'Not Available',
            'RestrictedHolidayList' => $RestrictedHolidayList,
            'getEmployeeInfo' => $Employee,
        ];
        return $this->controller->success("Restricted Holiday Details Successfully Received!!!", $data);


        // $data = [
        //     'checkPaidLeaveEligibility' => $checkPaidLeaveEligibility == true ? 'Eligibile' : 'Not Eligibile',
        //     'leaveType' => $leaveType,
        //     'permissableLeave' => $permissableLeave,
        //     'sumOfLeaveTaken' => $sumOfLeaveTaken,
        //     'leaveBalance' => $leaveBalance,
        //     'leaveTypeList' => $leaveTypeList,
        //     'sumOfPaidLeaveTaken' => $sumOfPaidLeaveTaken,
        //     'getEmployeeInfo' => $getEmployeeInfo,
        // ];

        // return $this->controller->success("Leave Details Successfully Received !!!", $data);
    }
    
    public function store(Request $request)
    {
        $auth_user_id = $request->employee_id;
        $calanderYear = calanderYear::currentYear();
        $holiday_id = $request->holiday_id;
        if(!$holiday_id) {
            return $this->controller->custom_error("Restricted holiday field is required.");
        }
        $RestrictedHoliday = DB::table('holiday_restricted')->where('holiday_id', $holiday_id)->first();
        if(!$RestrictedHoliday) {
            return $this->controller->custom_error("Restricted holiday is not found.");
        }
        $today = date('Y-m-d');
        if($today > $RestrictedHoliday->holiday_date) {
            return $this->controller->custom_error("Can not apply to past date Restricted holiday.");
        }
        $Employee = Employee::find($auth_user_id);
        $RestrictedHoliday = RestrictedHoliday::find($request->holiday_id);
        if($Employee) {
            $data['branch_id'] = $Employee->branch_id;
        } else {
            return $this->controller->custom_error('Employee id not found');
        }
        $RestrictedHoliday = DB::table('holiday_restricted')->where('holiday_id', $holiday_id)->where('branch_id', $Employee->branch_id)->first();
        if(!$RestrictedHoliday) {
            return $this->controller->custom_error("Restricted Holiday is not found");
        }

        $rh_balance=$Employee->rh_balance();
        if($rh_balance<=0) {
            return $this->controller->custom_error('Restricted holiday leave balance not available!');
        }
        $data['employee_id'] = $auth_user_id;
        $data['year_id']    = $calanderYear->year_id;
        $data['holiday_date'] = $RestrictedHoliday->holiday_date;
        $data['holiday_id'] = $holiday_id;
        $data['application_date'] = $today;
        $data['created_at'] = Carbon::now();

        $RhApplicationExist=RhApplication::where('year_id',$calanderYear->year_id)->where('holiday_date', $RestrictedHoliday->holiday_date)->where('employee_id', $auth_user_id)->orderByDesc('rh_application_id')->first();
        if(!$RhApplicationExist)
        {
            $RhApplication = RhApplication::create($data);
            return $this->controller->success("Restricted holiday Leave Application Sent Successfully!", $RhApplication);
        }else{
            if($RhApplicationExist->status==1) {
                return $this->controller->custom_error('RH Application process is in Pending');
            } if($RhApplicationExist->status==2) {
                return $this->controller->custom_error('RH Application process is already Approved');
            } if($RhApplicationExist->status==4) {
                return $this->controller->custom_error('RH Application process is already Auto Canceled');
            }
            $RhApplication = RhApplication::create($data);
            return $this->controller->success("Restricted holiday Leave Application Sent Successfully!", $RhApplication);
        }

    }
}
