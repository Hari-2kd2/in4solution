<?php

namespace App\Http\Controllers\Leave;

use App\Exports\ApproveOvertimeReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompOffRequest;
use App\Imports\ApprovedOvertimeImport;
use App\Lib\Enumerations\AppConstant;
use App\Lib\Enumerations\AttendanceStatus;
use App\Model\CompOff;
use App\Model\Employee;
use App\Model\EmployeeInOutData;
use App\Model\WeeklyHoliday;
use App\Repositories\CommonRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; 

class CompOffController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        $results = CompOff::with('employee')->orderBy('comp_off_details_id', 'desc')->get();
        return view('admin.leave.compOff.index', ['results' => $results]);
    }

    public function create()
    {
        $employeeList = $this->commonRepository->employeeFingerList();
        $offTimingList = $this->commonRepository->leaveTimingList();
        return view('admin.leave.compOff.form', ['employeeList' => $employeeList, 'offTimingList' => $offTimingList]);
    }

    public function store(CompOffRequest $request)
    {
        $input = $request->all();
        $input['off_date'] = dateConvertFormtoDB($input['off_date']);
        $input['working_date'] = dateConvertFormtoDB($input['working_date']);

        try {
            $compOffDetails = CompOff::createTrait($input);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $compOffDetails->finger_print_id)->where('date', $compOffDetails->off_date)->first();

            if ($employeeInOutData) {
                $employeeInOutData->update(['comp_off_details_id' => $compOffDetails->comp_off_details_id, 'attendance_status' => AttendanceStatus::$COMPOFF]);
                $employeeInOutData->save();
            } else {
                $employeeInOutData = EmployeeInOutData::create([
                    'finger_print_id' => $compOffDetails->finger_print_id,
                    'date' => $compOffDetails->off_date,
                    'comp_off_details_id' => $compOffDetails->comp_off_details_id,
                    'attendance_status' => AttendanceStatus::$COMPOFF,
                ]);
            }
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
            info($e->getMessage());
        }

        if ($bug == 0) {
            return redirect('compOff')->with('success', 'Comp off successfully saved.');
        } else {
            return redirect('compOff')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $employeeList = $this->commonRepository->employeeFingerList();
        $offTimingList = $this->commonRepository->leaveTimingList();
        $editModeData = CompOff::findOrFail($id);
        return view('admin.leave.compOff.form', ['editModeData' => $editModeData, 'offTimingList' => $offTimingList, 'employeeList' => $employeeList]);
    }

    public function update(CompOffRequest $request, $id)
    {
        $compOffDetails = CompOff::findOrFail($id);
        $input = $request->all();
        $input['off_date'] = dateConvertFormtoDB($input['off_date']);
        $input['working_date'] = dateConvertFormtoDB($input['working_date']);
        try {
            $compOffDetails->update($input);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $compOffDetails->finger_print_id)->where('date', $compOffDetails->off_date)->update(['comp_off_details_id' => $compOffDetails->comp_off_details_id]);

            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Comp off successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $compOffDetails = CompOff::findOrFail($id);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $compOffDetails->finger_print_id)->where('date', $compOffDetails->off_date)->update(['comp_off_details_id' => null]);
            $compOffDetails->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        }
        //  elseif ($bug == 1451) {
        //     echo 'hasForeignKey';
        // }
        else {
            echo 'error';
        }
    }

    public function getWorkingtime(Request $request)
    {
        $compOff = CompOff::where('finger_print_id', $request->finger_print_id)->where('off_date', dateConvertFormtoDB($request->off_date))->first();
        $off_date = dateConvertFormtoDB($request->off_date);
        $start_date = date('Y-m-01', strtotime($off_date));
        $end_date = date('Y-m-d', strtotime($off_date));
        $employee = Employee::where('finger_id', $request->finger_print_id)->first();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        $weeklyHolidaysDates = WeeklyHoliday::where('employee_id', $employee->employee_id)->where('month', date('Y-m', strtotime($start_date)))->first();
        $data = findFromDateToDateToAllDate($start_date, $end_date);
        $dateArr = [];

        foreach ($data as $key => $value) {
            $ifHoliday = $this->ifHoliday($govtHolidays, $value['date'], $employee->employee_id, $weeklyHolidays, $weeklyHolidaysDates);
            if ($ifHoliday) {
                $dateArr[] = $value['date'];
            }
        }

        info($dateArr);

        if ($compOff) {
            return 'Exists';
        }

        $results = EmployeeInOutData::whereIn('date', array_values($dateArr))->where('finger_print_id', $request->finger_print_id)->where('comp_off_details_id', null)->where('working_time', '!=', null)->where('working_time', '>=', '04:00:00')->get();
        $options = ['' => '--- Please Select ---'];

        foreach ($results as $key => $value) {
            $options[dateConvertDBtoForm($value->date)] = $value->working_time != null ? dateConvertDBtoForm($value->date) . ' ' . date('H:i:s', strtotime($value->working_time)) : '00:00' . ' working time on date ' . dateConvertDBtoForm($value->date);
        }

        return count($options) > 1 ? $options : 'notFound';
    }

    public function compOffTemplate(Request $request)
    {
        $date = dateConvertFormtoDB($request->date);
        $inc = 1;
        $dataSet = [];
        $Data = EmployeeInOutData::where('date', $date)->where('working_time', '>=', AppConstant::$HALF_DAY_HOUR)->orderBy('finger_print_id', 'ASC')->get();

        foreach ($Data as $key => $data) {

            $dataSet[] = [
                $inc,
                $data->finger_print_id,
                $data->date,
                $data->working_time,
                $data->working_time,
                'Simple Approval',
            ];

            $inc++;
        }

        $primaryHead = ['SL.NO', 'EMPLOYEE ID', 'DATE', 'ACTUAL Wrk.Hr', 'APPROVED Wrk.Hr', 'REMARK'];
        $heading = [$primaryHead];

        $extraData['heading'] = $heading;
        $filename = 'Employee Overtime Information-' . DATE('d-m-Y His') . '.xlsx';

        return Excel::download(new ApproveOvertimeReport($dataSet, $extraData), $filename);
    }

    public function import(Request $request)
    {
        try {

            $date = dateConvertFormtoDB($request->date);
            $file = $request->file('select_file');
            Excel::import(new ApprovedOvertimeImport($request->all()), $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new ApprovedOvertimeImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }
        return back()->with('success', 'Approve Overtime information imported successfully.');
    }

    public function ifHoliday($govtHolidays, $date, $employee_id, $weeklyHolidays, $weeklyHolidaysDates)
    {

        $govt_holidays = [];
        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }

        $timestamp = strtotime($date);
        $dayName = date("l", $timestamp);
        foreach ($weeklyHolidays as $v) {
            if ($v->day_name == $dayName && $v->employee_id == $employee_id && isset($weeklyHolidaysDates) && $dayName == $weeklyHolidaysDates['day_name']) {
                return true;
            }
        }

        return false;
    }
}
