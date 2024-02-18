<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncentiveRequest;
use App\Lib\Enumerations\AppConstant;
use App\Lib\Enumerations\AttendanceStatus;
use App\Model\EmployeeInOutData;
use App\Model\Incentive;
use App\Repositories\CommonRepository;
use Illuminate\Http\Request;

class IncentiveController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        $results = Incentive::with('employee')->orderBy('incentive_details_id', 'desc')->get();
        return view('admin.leave.incentive.index', ['results' => $results]);
    }

    public function create()
    {
        $employeeList = $this->commonRepository->incentiveEmployeeFingerList();
        return view('admin.leave.incentive.form', ['employeeList' => $employeeList]);
    }

    public function store(IncentiveRequest $request)
    {
        $input = $request->all();
        $input['incentive_date'] = dateConvertFormtoDB($input['incentive_date']);
        $input['working_date'] = dateConvertFormtoDB($input['working_date']);
        try {
            $incentiveDetails = Incentive::createTrait($input);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $incentiveDetails->finger_print_id)->where('date', $incentiveDetails->incentive_date)->first();

            if ($employeeInOutData) {
                $employeeInOutData->update(['incentive_details_id' => $incentiveDetails->incentive_details_id, 'attendance_status' => AttendanceStatus::$INCENTIVE]);
                $employeeInOutData->save();
            } else {
                $employeeInOutData = EmployeeInOutData::create([
                    'finger_print_id' => $incentiveDetails->finger_print_id,
                    'date' => $incentiveDetails->incentive_date,
                    'incentive_details_id' => $incentiveDetails->incentive_details_id,
                    'attendance_status' => AttendanceStatus::$INCENTIVE,
                ]);
            }

            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
            info($e->getMessage());
        }

        if ($bug == 0) {
            return redirect('incentive')->with('success', 'Incentive successfully saved.');
        } else {
            return redirect('incentive')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $employeeList = $this->commonRepository->incentiveEmployeeFingerList('incentive', 0);
        $editModeData = Incentive::findOrFail($id);
        return view('admin.leave.incentive.form', ['editModeData' => $editModeData, 'employeeList' => $employeeList]);
    }

    public function update(IncentiveRequest $request, $id)
    {
        $incentiveDetails = Incentive::findOrFail($id);
        $input = $request->all();
        $input['incentive_date'] = dateConvertFormtoDB($input['incentive_date']);
        $input['working_date'] = dateConvertFormtoDB($input['working_date']);
        try {
            $incentiveDetails->update($input);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $incentiveDetails->finger_print_id)->where('date', $incentiveDetails->incentive_date)->update(['incentive_details_id' => $incentiveDetails->incentive_details_id]);

            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Incentive successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $incentiveDetails = Incentive::findOrFail($id);
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $incentiveDetails->finger_print_id)->where('date', $incentiveDetails->incentive_date)->update(['incentive_details_id' => null]);
            $incentiveDetails->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } else {
            echo 'error';
        }
    }

    public function getWorkingtime(Request $request)
    {
        $date = "";
        $time = "";

        $incentive = Incentive::where('finger_print_id', $request->finger_print_id)->where('incentive_date', dateConvertFormtoDB($request->incentive_date))->first();

        if ($incentive) {
            return 'Exists';
        }

        $results = EmployeeInOutData::where('date', dateConvertFormtoDB($request->incentive_date))->where('finger_print_id', $request->finger_print_id)->where('working_time', '>=', AppConstant::$INCENTIVE_HOUR)->get();
        $options = ['' => '--- Select ---'];
        foreach ($results as $key => $value) {
            $options[$value->date] = $value->working_time != null ? date('H:i:s', strtotime($value->working_time)) . ' - ' . dateConvertDBtoForm($value->date) : '00:00' . ' working time' . dateConvertDBtoForm($value->date);
            $date = date('d/m/Y', strtotime($value->date));
            $time = date('H:i:s', strtotime($value->working_time));
        }

        return $date != "" ? $date . ' ' . $time : 'notFound';
    }
}
