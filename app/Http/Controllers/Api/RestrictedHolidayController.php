<?php

namespace App\Http\Controllers\Api;

use App\Model\Employee;
use App\Model\calanderYear;
use App\Model\RhApplication;
use Illuminate\Http\Request;
use App\Model\RestrictedHoliday;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RestrictedHolidayController extends Controller
{

    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }
    public function index(Request $request)
    {
        $input = Validator::make($request->all(), [
            'employee_id' => 'required',

        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }
        $employee = Employee::where('employee_id', $request->employee_id)->first();
        $employee_id = $employee->employee_id;
        $calanderYear = calanderYear::currentYear();
        $RhApplicationList = RhApplication::with(['employee', 'RestrictedHoliday', 'calanderYear'])
            ->where('employee_id', $employee_id)->where('year_id', $calanderYear->year_id)
            ->orderBy('rh_application_id', 'desc')->get();

        $RestrictedHolidayList = RestrictedHoliday::where('year_id', $calanderYear->year_id)->get();
        return $this->controller->successdualdata('Restricted Holiday application Successfully Received', $RhApplicationList, $RestrictedHolidayList);
    }

    public function store(Request $request)
    {
        try {
            $input = Validator::make($request->all(), [
                'employee_id' => 'required',
                'holiday_id' => 'required',
                'purpose' => 'required|string',
            ]);
            if ($input->fails()) {
                return Controller::custom_error($input->errors()->first());
            }
            $input = $request->all();

            $employee = Employee::where('user_id', $request->employee_id)->first();
            $calanderYear = calanderYear::currentYear();
            $RestrictedHoliday = RestrictedHoliday::find($request->holiday_id);
            $input['employee_id'] = $employee->employee_id;
            $input['branch_id'] = $employee->branch_id;
            $input['year_id'] = $calanderYear->year_id;
            $input['holiday_date'] = $RestrictedHoliday->holiday_date;
            $input['application_date'] = date('Y-m-d');
            $input['purpose'] = $request->purpose;

            $ifExists = RhApplication::where('holiday_date',  $RestrictedHoliday->holiday_date)->where('employee_id', $request->employee_id)->first();
            if ($ifExists) {
                return Controller::custom_error("Restricted holiday application exists. Try different dates.");
            }
            $results = RhApplication::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        if ($bug == 0) {
            return $this->controller->success('Restricted holiday successfully send.', $results,);
        } else {
            return $this->controller->custom_error($e->getMessage());
        }
    }
}
