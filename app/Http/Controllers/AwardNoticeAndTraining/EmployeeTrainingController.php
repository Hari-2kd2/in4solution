<?php

namespace App\Http\Controllers\AwardNoticeAndTraining;

use App\Model\Employee;

use App\Model\TrainingLog;

use App\Model\TrainingInfo;

use Illuminate\Support\Str;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CommonRepository;
use App\Http\Requests\EmployeeTrainingRequest;
use App\Lib\Enumerations\UserStatus;

class EmployeeTrainingController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }



    public function index()
    {
        $results = TrainingInfo::with(['employee', 'trainingType'])->orderBy('training_info_id', 'DESC')->get();

        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $employee = Employee::query();

        if ($roleId == 1) {
            $employee = $employee->where('status', UserStatus::$ACTIVE)->get();
        } else {
            $employee = $employee->where('status', UserStatus::$ACTIVE)->where('branch_id', $branchId)->get();
        }


        return view('admin.training.employeeTraining.index', ['results' => $results, 'employee' => $employee]);
    }



    public function create()
    {
        $employeeList      = $this->commonRepository->nonMandatoryEmployeeList();
        $trainingTypeList  = $this->commonRepository->trainingTypeList();
        return view('admin.training.employeeTraining.form', ['employeeList' => $employeeList, 'trainingTypeList' => $trainingTypeList]);
    }



    public function store(EmployeeTrainingRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = Auth::user()->user_id;
        $input['updated_by'] = Auth::user()->user_id;

        // $input['start_date'] = dateConvertFormtoDB($request->start_date);
        // $input['end_date'] = dateConvertFormtoDB($request->end_date);

        $photo = $request->file('certificate');

        if ($photo) {
            $fileName = md5(Str::random(30) . time() . '_' . $request->file('certificate')) . '.' . $request->file('certificate')->getClientOriginalExtension();
            $request->file('certificate')->move('uploads/employeeTrainingCertificate/', $fileName);
            $input['certificate'] = $fileName;
        }

        $input['employee_id'] = json_encode($request->employee_id);

        try {
            TrainingInfo::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = $e->getMessage();
        }

        if ($bug == 0) {
            return redirect('trainingInfo')->with('success', 'Employee training successfully saved.');
        } else {
            return redirect('trainingInfo')->with('error', 'Something Error Found !, Please try again.' . $bug);
        }
    }



    public function edit($id)
    {
        $editModeData = TrainingInfo::FindOrFail($id);
        $employeeList      = $this->commonRepository->employeeList();
        $trainingTypeList  = $this->commonRepository->trainingTypeList();
        return view('admin.training.employeeTraining.form', ['employeeList' => $employeeList, 'trainingTypeList' => $trainingTypeList, 'editModeData' => $editModeData]);
    }



    public function update(EmployeeTrainingRequest $request, $id)
    {
        $photo = $request->file('certificate');
        $data = TrainingInfo::FindOrFail($id);

        $input = $request->all();
        $input['created_by'] = Auth::user()->user_id;
        $input['updated_by'] = Auth::user()->user_id;
        // $input['start_date'] = dateConvertFormtoDB($request->start_date);
        // $input['end_date'] = dateConvertFormtoDB($request->end_date);

        if ($photo) {
            $fileName = md5(Str::random(30) . time() . '_' . $request->file('certificate')) . '.' . $request->file('certificate')->getClientOriginalExtension();
            $request->file('certificate')->move('uploads/employeeTrainingCertificate/', $fileName);
            if (file_exists('uploads/employeeTrainingCertificate/' . $data->certificate) and !empty($data->certificate)) {
                unlink('uploads/employeeTrainingCertificate/' . $data->certificate);
            }
            $input['certificate'] = $fileName;
        }

        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Employee training successfully updated.');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }



    public function show($id)
    {
        $isRead =  TrainingLog::where('training_info_id', $id)->where('employee_id', session('logged_session_data.employee_id'))->first();
        $result = TrainingInfo::with(['employee', 'trainingType'])->where('training_info_id', $id)->first();
        return view('admin.training.employeeTraining.details', ['result' => $result, 'is_read' => $isRead]);
    }



    public function destroy($id)
    {
        try {
            $data = TrainingInfo::FindOrFail($id);

            if (!is_null($data->certificate)) {
                if (file_exists('uploads/employeeTrainingCertificate/' . $data->certificate) and !empty($data->certificate)) {
                    unlink('uploads/employeeTrainingCertificate/' . $data->certificate);
                }
            }
            $data->delete();
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

    public function markAsRead($id)
    {
        try {
            $data = TrainingLog::updateOrCreate(['training_info_id' => $id, 'employee_id' => session('logged_session_data.employee_id'), 'read_at' => now()]);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        return redirect()->back();

        // if ($bug == 0) {
        //     return redirect()->back()->with('success', 'Video marked as read.');
        // } else {
        //     return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        // }
    }
}
