<?php

namespace App\Http\Controllers\Employee;

use App\User;
use DateTime;
use Carbon\Carbon;
use App\Model\Role;
use App\Model\Branch;
use App\Model\Device;
use App\Model\Employee;
use App\Model\PayGrade;
use App\Model\WorkShift;
use App\Model\WorkShiftCase;
use App\Model\Department;
use App\Components\Common;
use App\Model\Designation;
use App\Model\HourlySalary;
use Illuminate\Support\Str;
use App\Model\AccessControl;
use Illuminate\Http\Request;
use App\Model\DepartmentCase;
use Illuminate\Http\Response;
use App\Model\DesignationCase;
use App\Model\EmployeeExperience;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeDetailsExport;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Support\Facades\Storage;
use App\Repositories\EmployeeRepository;
use App\Model\EmployeeEducationQualification;

class EmployeeController extends Controller
{

    protected $employeeRepositories;

    public function __construct(EmployeeRepository $employeeRepositories)
    {
        $this->employeeRepositories = $employeeRepositories;
    }

    public function index(Request $request)
    {
        $branchId = session('logged_session_data.branch_id');
        $filterBranchId = $request->branch_id;
        $roleId = session('logged_session_data.role_id');
        $departmentList = Department::get();
        $designationList = Designation::get();
        $branchList = Branch::get();
        if ($roleId != 1) {
            $branchList = Branch::where('branch_id', $branchId)->get();
        }
        $roleList = Role::get();
        $LoggedEmployee = Employee::loggedEmployee();
        $supervisorIds = $LoggedEmployee->supervisorIds();


        if ($roleId != 1) {
            $results = Employee::where('branch_id', $branchId)->with(['userName' => function ($q) {
                $q->with('role');
            }, 'department', 'designation', 'branch',  'supervisor'])
                ->orderByRaw('CONVERT(finger_id, SIGNED) asc')->paginate();
        } else {
            $results = Employee::with(['userName' => function ($q) {
                $q->with('role');
            }, 'department', 'designation', 'branch',  'supervisor'])
                ->orderByRaw('CONVERT(finger_id, SIGNED) asc')->paginate();
        }

        if (request()->ajax()) {

            if ($roleId != 1) {
                $results = Employee::where('branch_id', $branchId);
            } else {
                $results = Employee::query();
            }

            if ($request->role_id != '') {
                $results = $results->whereHas('userName', function ($q) use ($request) {
                    $q->with('role')->where('role_id', $request->role_id);
                })->with('department', 'designation', 'branch',  'supervisor')->orderByRaw('CONVERT(finger_id, SIGNED) asc');
            } else {
                $results = $results->with(['userName' => function ($q) {
                    $q->with('role');
                }, 'department', 'designation', 'branch', 'supervisor'])->orderByRaw('CONVERT(finger_id, SIGNED) asc');
            }

            if ($request->department_id != '') {
                $results->where('department_id', $request->department_id);
            }

            if ($request->designation_id != '') {
                $results->where('designation_id', $request->designation_id);
            }

            if ($request->branch_id != '') {
                $results->where('branch_id', $request->branch_id);
            }

            if ($request->employee_name != '') {
                $results->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->employee_name . '%')
                        ->orWhere('last_name', 'like', '%' . $request->employee_name . '%');
                });
            }

            $results = $results->paginate();
            return   view('admin.employee.employee.pagination', compact('results'))->render();
        }

        return view('admin.employee.employee.index', ['results' => $results, 'departmentList' => $departmentList, 'designationList' => $designationList, 'roleList' => $roleList, 'branchList' => $branchList]);
    }

    public function create()
    {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');


        $userList = User::where('status', 1)->get();
        $roleList = Role::get();
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        $departmentList = Department::get();
        $designationList = Designation::get();
        $branchList = [];

        if ($branchId !== null && $roleId !== 1) {
            $branchList = Branch::where('branch_id', session('logged_session_data.branch_id'))->get();
        } elseif ($roleId == 1) {
            $branchList = Branch::get();
        }

        // $operationManagerList = Employee::with('user')
        //     ->where('status',  UserStatus::$ACTIVE)
        //     ->get();

        $workShiftList = WorkShift::get();
        $workShift = WorkShift::pluck('shift_name', 'work_shift_id');

        // if ($roleId == 1) {
        //     $supervisorList = Employee::join('user', 'user.user_id', 'employee.user_id')
        //         // ->where('employee.employee_id', '!=', session('logged_session_data.employee_id'))
        //         ->where('employee.status', UserStatus::$ACTIVE)
        //         ->orderBY('employee.finger_id', 'asc')->get();
        // } else {
        //     $supervisorList = Employee::join('user', 'user.user_id', 'employee.user_id')
        //         // ->where('employee.employee_id', '!=', session('logged_session_data.employee_id'))
        //         ->where('employee.status', UserStatus::$ACTIVE)
        //         ->where('employee.branch_id', session('logged_session_data.branch_id'))
        //         ->orderBY('employee.finger_id', 'asc')->get();
        // }

        $payGradeList = PayGrade::all();
        $hourlyPayGradeList = HourlySalary::all();
        $incentive = $this->employeeRepositories->incentive();
        $overTime = $this->employeeRepositories->overTime();
        $salaryLimit = $this->employeeRepositories->salaryLimit();
        // $workShift = $this->employeeRepositories->workShift();

        $data = [
            'userList' => $userList,
            'roleList' => $roleList,
            'employeeList' => $employeeList,
            'departmentList' => $departmentList,
            'designationList' => $designationList,
            'branchList' => $branchList,
            // 'supervisorList' => $supervisorList,
            'workShiftList' => $workShiftList,
            'payGradeList' => $payGradeList,
            'hourlyPayGradeList' => $hourlyPayGradeList,
            // 'operationManagerList' => $operationManagerList,
            'incentive' => $incentive,
            'overTime' => $overTime,
            'salaryLimit' => $salaryLimit,
            'workShift' => $workShift,

        ];

        return view('admin.employee.employee.addEmployee', $data);
    }

    public function store(EmployeeRequest $request)
    {


        $photo = $request->file('photo');
        $document = $request->file('document_file');
        $document2 = $request->file('document_file2');
        $document3 = $request->file('document_file3');
        $document4 = $request->file('document_file4');
        $document5 = $request->file('document_file5');
        $document6 = $request->file('document_file6');

        if ($photo) {
            $imgName = md5(Str::random(30) . time() . '_' . $request->file('photo')) . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move('uploads/employeePhoto/', $imgName);
            $employeePhoto['photo'] = $imgName;
        }
        if ($document) {
            $document_name = date('Y_m_d_H_i_s') . '_' . $request->file('document_file')->getClientOriginalName();
            $request->file('document_file')->move('uploads/employeeDocuments/', $document_name);
            $employeeDocument['document_file'] = $document_name;
        }
        if ($document2) {
            $document_name2 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file2')->getClientOriginalName();
            $request->file('document_file2')->move('uploads/employeeDocuments/', $document_name2);
            $employeeDocument['document_file2'] = $document_name2;
        }
        if ($document3) {
            $document_name3 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file3')->getClientOriginalName();
            $request->file('document_file3')->move('uploads/employeeDocuments/', $document_name3);
            $employeeDocument['document_file3'] = $document_name3;
        }
        if ($document4) {
            $document_name4 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file4')->getClientOriginalName();
            $request->file('document_file4')->move('uploads/employeeDocuments/', $document_name4);
            $employeeDocument['document_file4'] = $document_name4;
        }
        if ($document5) {
            $document_name5 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file5')->getClientOriginalName();
            $request->file('document_file5')->move('uploads/employeeDocuments/', $document_name5);
            $employeeDocument['document_file5'] = $document_name5;
        }
        if ($document6) {
            $document_name6 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file6')->getClientOriginalName();
            $request->file('document_file6')->move('uploads/employeeDocuments/', $document_name6);
            $employeeDocument['document_file6'] = $document_name6;
        }

        $employeeDataFormat = $this->employeeRepositories->makeEmployeePersonalInformationDataFormat($request->all());
        // info('employeeDataFormat = ' . print_r($employeeDataFormat, 1));
        if (isset($employeePhoto)) {
            $employeeData = $employeeDataFormat + $employeePhoto;
        } else {
            $employeeData = $employeeDataFormat;
        }
        try {
            DB::beginTransaction();

            $employeeAccountDataFormat = $this->employeeRepositories->makeEmployeeAccountDataFormat($request->all());
            // dd($employeeAccountDataFormat);
            $parentData = User::create($employeeAccountDataFormat);

            $employeeData['user_id'] = $parentData->user_id;
            $childData = Employee::create($employeeData);
            Employee::where('employee_id', $childData->employee_id)->update($employeeData);
            $childData = Employee::find($childData->employee_id);
            // $childData = DB::table('employee')->create($employeeData);
            // echo '<pre>employeeData='.print_r($employeeData, 1) . ', childData='.print_r($childData->getAttributes(),1).'</pre>';
            // dd('');
            Employee::where('employee_id', $childData->employee_id)->update(['device_employee_id' => $childData->finger_id]);
            // testing purpose credit leaves
            $addEmployeeLeaves = \App\Components\Common::addEmployeeLeaves($childData->employee_id);
            User::where('user_id', $parentData->user_id)->update(['device_employee_id' => $childData->finger_id]);

            $employeeEducationData = $this->employeeRepositories->makeEmployeeEducationDataFormat($request->all(), $childData->employee_id);
            if (count($employeeEducationData) > 0) {
                EmployeeEducationQualification::insert($employeeEducationData);
            }

            $employeeExperienceData = $this->employeeRepositories->makeEmployeeExperienceDataFormat($request->all(), $childData->employee_id);
            if (count($employeeExperienceData) > 0) {
                EmployeeExperience::insert($employeeExperienceData);
            }


            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('employee')->with('success', 'Employee information successfully saved.');
        } else {
            return redirect('employee')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $userList = User::where('status', 1)->get();
        $roleList = Role::get();
        $editModeData = Employee::findOrFail($id);
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        $departmentList = Department::get();
        $designationList = Designation::get();
        $workShiftList = DB::table('work_shift')->pluck('shift_name', 'work_shift_id');
        $payGradeList = PayGrade::all();
        $hourlyPayGradeList = HourlySalary::all();
        $device_list = Device::where('status', 1)->get();
        $incentive = $this->employeeRepositories->incentive();
        $overTime = $this->employeeRepositories->overTime();
        $salaryLimit = $this->employeeRepositories->salaryLimit();
        $workShift = $this->employeeRepositories->workShift();
        $workShift = $workShiftList;
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');

        // $operationManagerList = Employee::with('user')
        //     ->whereHas('user')
        //     ->where('status', UserStatus::$ACTIVE)
        //     ->get();

        // $supervisorList = Employee::join('user', 'user.user_id', 'employee.user_id')
        //     ->where('employee.employee_id', '!=', session('logged_session_data.employee_id'))
        //     ->where('employee.status', UserStatus::$ACTIVE)
        //     ->orderBY('employee.finger_id', 'asc')->get();

        if ($branchId !== null && $roleId !== 1) {
            $branchList = Branch::where('branch_id', session('logged_session_data.branch_id'))->get();
        } else {
            $branchList = Branch::get();
        }

        $employeeAccountEditModeData = User::where('user_id', $editModeData->user_id)->first();
        $educationQualificationEditModeData = EmployeeEducationQualification::where('employee_id', $id)->get();
        $experienceEditModeData = EmployeeExperience::where('employee_id', $id)->get();


        $data = [
            'userList' => $userList,
            'roleList' => $roleList,
            'departmentList' => $departmentList,
            'designationList' => $designationList,
            'branchList' => $branchList,
            // 'supervisorList' => $supervisorList,
            'workShiftList' => $workShiftList,
            'payGradeList' => $payGradeList,
            'editModeData' => $editModeData,
            'hourlyPayGradeList' => $hourlyPayGradeList,
            'employeeAccountEditModeData' => $employeeAccountEditModeData,
            'educationQualificationEditModeData' => $educationQualificationEditModeData,
            'experienceEditModeData' => $experienceEditModeData,
            // 'operationManagerList' => $operationManagerList,
            'employeeList' => $employeeList,
            'device_list' => $device_list,
            'incentive' => $incentive,
            'overTime' => $overTime,
            'salaryLimit' => $salaryLimit,
            'workShift' => $workShift,

        ];

        return view('admin.employee.employee.editEmployee', $data);
    }

    public function update(EmployeeRequest $request, $id)
    {


        $employee = Employee::findOrFail($id);
        $document = $request->file('document_file');
        $document2 = $request->file('document_file2');
        $document3 = $request->file('document_file3');
        $document4 = $request->file('document_file4');
        $document5 = $request->file('document_file5');
        $document6 = $request->file('document_file6');
        $photo = $request->file('photo');

        $imgName = $employee->photo;

        if ($photo) {
            echo 'photo';
            $imgName = md5(Str::random(30) . time() . '_' . $request->file('photo')) . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->move('uploads/employeePhoto/', $imgName);
            if (file_exists('uploads/employeePhoto/' . $employee->photo) and !empty($employee->photo)) {
                unlink('uploads/employeePhoto/' . $employee->photo);
                $employee->update(['photo' => null]);
            }
            $employeePhoto['photo'] = $imgName;
        }



        if ($document) {
            $document_name = date('Y_m_d_H_i_s') . '_' . $request->file('document_file')->getClientOriginalName();
            $request->file('document_file')->move('uploads/employeeDocuments/', $document_name);
            $employeeDocument['document_file'] = $document_name;
        }
        if ($document2) {
            $document_name2 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file2')->getClientOriginalName();
            $request->file('document_file2')->move('uploads/employeeDocuments/', $document_name2);
            $employeeDocument['document_file2'] = $document_name2;
        }
        if ($document3) {
            $document_name3 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file3')->getClientOriginalName();
            $request->file('document_file3')->move('uploads/employeeDocuments/', $document_name3);
            $employeeDocument['document_file3'] = $document_name3;
        }
        if ($document4) {
            $document_name4 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file4')->getClientOriginalName();
            $request->file('document_file4')->move('uploads/employeeDocuments/', $document_name4);
            $employeeDocument['document_file4'] = $document_name4;
        }
        if ($document5) {
            $document_name5 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file5')->getClientOriginalName();
            $request->file('document_file5')->move('uploads/employeeDocuments/', $document_name5);
            $employeeDocument['document_file5'] = $document_name5;
        }
        if ($document6) {
            $document_name6 = date('Y_m_d_H_i_s') . '_' . $request->file('document_file6')->getClientOriginalName();
            $request->file('document_file6')->move('uploads/employeeDocuments/', $document_name6);
            $employeeDocument['document_file6'] = $document_name6;
        }
        $employeeDataFormat = $this->employeeRepositories->makeEmployeePersonalInformationDataFormat($request->all());
        if (isset($employeePhoto)) {
            $employeeData = $employeeDataFormat + $employeePhoto;
        } else {
            $employeeData = $employeeDataFormat;
        }

        try {
            DB::beginTransaction();
            $employeeAccountDataFormat = $this->employeeRepositories->makeEmployeeAccountDataFormat($request->all(), 'update');

            User::where('user_id', $employee->user_id)->update($employeeAccountDataFormat);

            // Update Personal Information
            $employee->update($employeeData);
            $employee->save();

            // Delete education qualification
            EmployeeEducationQualification::whereIn('employee_education_qualification_id', explode(',', $request->delete_education_qualifications_cid))->delete();

            // Update Education Qualification
            $employeeEducationData = $this->employeeRepositories->makeEmployeeEducationDataFormat($request->all(), $id, 'update');
            foreach ($employeeEducationData as $educationValue) {
                $cid = $educationValue['educationQualification_cid'];
                unset($educationValue['educationQualification_cid']);
                if ($cid != "") {
                    EmployeeEducationQualification::where('employee_education_qualification_id', $cid)->update($educationValue);
                } else {
                    $educationValue['employee_id'] = $id;
                    EmployeeEducationQualification::create($educationValue);
                }
            }

            Employee::where('employee_id', $employee->employee_id)->WhereNull('device_employee_id')->update(['device_employee_id' => $employee->finger_id]);
            User::where('user_id', $employee->user_id)->WhereNull('device_employee_id')->update(['device_employee_id' => $employee->finger_id]);

            // Delete experience
            EmployeeExperience::whereIn('employee_experience_id', explode(',', $request->delete_experiences_cid))->delete();

            // Update Education Qualification
            $employeeExperienceData = $this->employeeRepositories->makeEmployeeExperienceDataFormat($request->all(), $id, 'update');
            if (count($employeeExperienceData) > 0) {
                foreach ($employeeExperienceData as $experienceValue) {
                    $cid = $experienceValue['employeeExperience_cid'];
                    unset($experienceValue['employeeExperience_cid']);
                    if ($cid != "") {
                        EmployeeExperience::where('employee_experience_id', $cid)->update($experienceValue);
                    } else {
                        $experienceValue['employee_id'] = $id;
                        EmployeeExperience::create($experienceValue);
                    }
                }
            }

            DB::commit();
            $bug = 0;
            return redirect()->back()->with('success', 'Employee information successfully updated.');
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
            $bug = $e->getMessage();
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.' . $bug);
        }
    }

    public function show($id)
    {

        $employeeInfo = Employee::where('employee.employee_id', $id)->first();
        $User = User::find($employeeInfo->user_id);
        $employeeExperience = EmployeeExperience::where('employee_id', $id)->get();
        $employeeEducation = EmployeeEducationQualification::where('employee_id', $id)->get();
        $employeeConDevice = AccessControl::where('employee', $id)->groupBy('device')->get();

        return view('admin.user.user.profile', ['employeeInfo' => $employeeInfo, 'User' => $User, 'employeeExperience' => $employeeExperience, 'employeeEducation' => $employeeEducation, 'employeeConDevice' => $employeeConDevice]);
    }

    public function destroy($id)
    {
        try {

            DB::beginTransaction();
            $data = Employee::FindOrFail($id);
            $user_data = User::FindOrFail($data->user_id);
            $user_data->delete();

            // $acc_cont = AccessControl::where('employee', $data->employee_id)->get();
            // //dd($acc_cont);

            // if (count($acc_cont)) {
            //     $check_device = \App\Components\Common::restartdevice();
            //     $check_device = json_decode($check_device);
            //     if ($check_device->status == "all_offline_check_cable") {
            //         echo "all_device_offline";
            //         exit;
            //     } elseif (isset($check_device->offline_device) && $check_device->offline_device) {
            //         echo "some_device_offline" . "|||" . $check_device->offline_device;
            //         exit;
            //     }
            // }

            if (!is_null($data->photo)) {
                if (file_exists('uploads/employeePhoto/' . $data->photo) and !empty($data->photo)) {
                    unlink('uploads/employeePhoto/' . $data->photo);
                }
            }
            $result = $data->delete();
            if ($result) {

                // if (count($acc_cont)) {

                //     foreach ($acc_cont as $acc_cont_data) {

                //         $device = Device::findOrFail($acc_cont_data->device);
                //         $remove = [];
                //         $remove[] = ['employeeNo' => (string) $acc_cont_data->device_employee_id];

                //         $rawdata = [
                //             "UserInfoDetail" => [
                //                 "mode" => "byEmployeeNo",
                //                 "EmployeeNoList" =>
                //                 $remove,
                //             ],
                //         ];

                //         //dd(json_encode($rawdata));

                //         $client = new \GuzzleHttp\Client();
                //         $response = $client->request('PUT', 'http://localhost:' . $device->port . '/' . $device->protocol . '/AccessControl/UserInfoDetail/Delete', [
                //             'auth' => [$device->username, $device->password, "digest"],
                //             'query' => ['format' => 'json', 'devIndex' => $device->devIndex],
                //             'json' => $rawdata,
                //         ]);

                //         /* $statusCode = $response->getStatusCode();
                //         $content    = $response->getBody()->getContents();
                //         $data       = json_decode($content);*/

                //         //dd($data);

                //         $rawdata = [
                //             "FaceInfoDelCond" => [
                //                 "EmployeeNoList" =>
                //                 $remove,
                //             ],
                //         ];

                //         //dd(json_encode($rawdata));

                //         $client = new \GuzzleHttp\Client();
                //         $response = $client->request('PUT', 'http://localhost:' . $device->port . '/' . $device->protocol . '/Intelligent/FDLib/FDSearch/Delete', [
                //             'auth' => [$device->username, $device->password, "digest"],
                //             'query' => ['format' => 'json', 'devIndex' => $device->devIndex],
                //             'json' => $rawdata,
                //         ]);
                //     }
                // }

                // DB::table('user')->where('user_id',$data->user_id)->delete();
                DB::table('user')->where('user_id', $data->user_id)->update(['deleted_at' => Carbon::now()]);
                DB::table('employee_education_qualification')->where('employee_id', $data->employee_id)->delete();
                DB::table('employee_experience')->where('employee_id', $data->employee_id)->delete();
                DB::table('employee_attendance')->where('finger_print_id', $data->finger_id)->delete();
                DB::table('employee_award')->where('employee_id', $data->employee_id)->delete();

                DB::table('employee_bonus')->where('employee_id', $data->employee_id)->delete();

                DB::table('promotion')->where('employee_id', $data->employee_id)->delete();

                DB::table('salary_details')->where('employee_id', $data->employee_id)->delete();

                DB::table('training_info')->where('employee_id', $data->employee_id)->delete();

                DB::table('warning')->where('warning_to', $data->employee_id)->delete();

                DB::table('leave_application')->where('employee_id', $data->employee_id)->delete();

                DB::table('employee_performance')->where('employee_id', $data->employee_id)->delete();

                DB::table('termination')->where('terminate_to', $data->employee_id)->delete();

                DB::table('notice')->where('created_by', $data->employee_id)->delete();

                DB::table('employee_access_control')->where('employee', $data->employee_id)->delete();
                DB::table('ms_sql')->where('employee', $data->employee_id)->delete();
                DB::table('weekly_holiday')->where('employee_id', $data->employee_id)->delete();
            }
            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
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

    public function bonusdays($employee_id)
    {

        // $employees = DB::select("call `SP_getEmployeeInfo`('" . $employee_id . "')");
        $employees = Employee::where("created_at", ">=", Carbon::now()->subYears(2))->where('status', 1)->get();

        $dataFormat = [];
        $tempArray = [];
        foreach ($employees as $employee) {
            $tempArray['date_of_joining'] = $employee->date_of_joining;
            $tempArray['date_of_leaving'] = $employee->date_of_leaving;
            $tempArray['employee_id'] = $employee->employee_id;
            $tempArray['designation_id'] = $employee->designation_id;
            $tempArray['first_name'] = $employee->first_name;
            $tempArray['last_name'] = $employee->last_name;
            $tempArray['employee_name'] = $employee->first_name . " " . $employee->last_name;
            $tempArray['phone'] = $employee->phone;
            $tempArray['finger_id'] = $employee->finger_id;
            $tempArray['department_id'] = $employee->department_id;

            $date_of_joining = new DateTime($employee->date_of_joining);
            // ->where("created_at", ">=", Carbon::now()->subDays(15))
            // if(){

            // }

            $dataFormat[$employee->employee_id][] = $tempArray;
        }
        return $dataFormat;
    }

    public function employeeTemplate()
    {
        $file_name =  'templates/employee_details.xlsx';
        // $filePath = storage_path() . '/app/public/' .  $file_name;
        // return response()->download($filePath, 'employee_details.xlsx', ['Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        $file = Storage::disk('public')->get($file_name);
        ob_end_clean();
        ob_start();
        return (new Response($file, 200))
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function t_usr(Request $request)
    {
        \set_time_limit(0);
        try {
            $users = DB::connection('sqlsrv')->table('Employees')->join('Departments', 'Departments.DepartmentId', '=', 'Employees.DepartmentId')
                ->where('Employees.EmployeeName', 'NOT LIKE', '%del%')->orderBy('Employees.EmployeeName')->get();
            $date = Carbon::now()->subDay(0)->format('Y-m-d');

            $tempArrayUser = [];
            $tempArrayEmployee = [];
            $totalDatasUser = [];
            $totalDatasEmployee = [];

            if ($request->action == 'truncate') {
                DB::table('user')->truncate();
                DB::table('employee')->truncate();

                DB::table('user')->insert([
                    'user_name' => 'admin',
                    'role_id' => 1,
                    'password' => Hash::make('123'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                DB::table('employee')->insert([
                    'user_id' => 1,
                    'finger_id' => '1001',
                    'first_name' => 'admin',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            foreach ($users as $key => $employee) {

                $if_employee_exists = DB::table('employee')->where('finger_id', $employee->EmployeeCode)->first();

                if (!$if_employee_exists) {
                    //dd($employee);
                    $tempArrayEmployee['finger_id'] = $employee->EmployeeCode;
                    $tempArrayEmployee['first_name'] = $employee->EmployeeName;
                    $tempArrayUser['user_name'] = $employee->EmployeeName;
                    $tempArrayUser['role_id'] = 3;
                    $totalDatasUser[] = $tempArrayUser;
                    $totalDatasEmployee[] = $tempArrayEmployee;

                    $user_id = DB::table('user')->insertGetID([
                        'user_name' => $employee->EmployeeName,
                        'role_id' => 3,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    $employee_id = DB::table('employee')->insertGetID([
                        'user_id' => $user_id,
                        'finger_id' => $employee->EmployeeCode,
                        'department_id' => $employee->DepartmentId,
                        'first_name' => $employee->EmployeeName,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    $this->pushEmployeeLive([
                        'user_id' => $user_id,
                        'employee_id' => $employee_id,
                        'role_id' => 3,
                        'user_name' => $employee->EmployeeName,
                        'password' => 'demo1234',
                        'status' => 1,
                        'finger_id' => $employee->EmployeeCode,
                        'department_id' => $employee->DepartmentId,
                        'first_name' => $employee->EmployeeName,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // echo "<br>";
            // echo "Success : Imported Successfully";
            // echo "<br>";

            // echo "<pre>";
            // print_r($totalDatasUser);
            // print_r($totalDatasEmployee);
            // echo "<pre>";

            return redirect('employee')->with('success', 'Employee information sync successfully.');
        } catch (\Throwable $th) {
            // dd($th);
            return redirect('employee')->with('error', 'Something went wrong!');
            //throw $th;
        }

        return redirect('employee')->with('success', 'Employee information sync successfully.');
    }

    public function pushEmployeeLive($form_data)
    {

        $data_set = [];
        foreach ($form_data as $key => $value) {
            if ($value) {
                $data_set[$key] = $value;
            } else {
                $data_set[$key] = '';
            }
        }
        Log::info($data_set);
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('POST', Common::liveurl() . "addEmployee", [
            'form_params' => $data_set,
        ]);
    }

    public function export()
    {

        $employees = Employee::where('status', UserStatus::$ACTIVE)->where('employee_id', '>', 1)->with('department', 'branch', 'designation', 'workshift', 'role', 'userName', 'supervisor')->get();
        $branch_id = request()->get('branch_id', null);
        if ($branch_id) {
            $employees = $employees->where('branch_id', $branch_id);
        }
        // dd($employees);

        $extraData = [];
        $inc = 1;
        $supervisor_name = null;
        $dataset = [];
        foreach ($employees as $key => $Data) {
            // dd($Data); 
            $user = User::find($Data->user_id);
            $role = Role::find($user->role_id);

            // SL.NO	USER NAME	ROLE NAME	EMPLOYEE CODE	DEPARTMENT	DESIGNATION	BRANCH	SUPERVISOR	PHONE	PERSONAL EMAIL	FIRST NAME	LAST NAME	DATE OF BIRTH	DATE OF JOINING	GENDER	RELIGION	MARITAL STATUS	ADDRESS	EMERGENCY CONTACT	STATUS	NO OF CHILD	WORK SHIFT	CTC	GROSS	OVERTIME STATUS	ESI CARD NUMBER	PF ACCOUNT NUMBER	BANK NAME	BANK ACCOUNT	BANK IFSC	UAN	COST CENTRE	PAN/GIR NO	PERMANENT STATUS
            $Department = Department::where('department_id', $Data->department_id)->first();
            $Designation = Designation::where('designation_id', $Data->designation_id)->first();
            $WorkShift = WorkShift::where('work_shift_id', $Data->work_shift_id)->first();
            // $WorkShiftName = '';
            // if ($WorkShift) {
            //     $WorkShiftName = $WorkShift->shift_name;
            // } else if ($Data->work_shift_id) {
            //     $WorkShiftName = 'General';
            // }

            $dataset[] = [
                $inc,   // 0
                (string) $Data->userName ? $Data->userName->user_name : '', // 1
                (string) $Data->userName && $Data->userName->role ? $Data->userName->role->role_name : '', // 2
                (string) $Data->emp_code, // 3
                $Department ? $Department->department_name : '', // 4
                $Designation ? $Designation->designation_name : '', // 5
                (string) $Data->branch->branch_name, // 6
                (string) $Data->supervisor ? $Data->supervisor->user_name : '', // 7
                (string) $Data->phone, // 8
                (string) $Data->email, // 9
                (string) $Data->first_name, // 10
                (string) $Data->last_name, // 11
                (string) dateConvertDBtoForm($Data->date_of_birth),  // 12
                (string) dateConvertDBtoForm($Data->date_of_joining), // 13
                (string) $Data->gender, // 14
                (string) $Data->religion, // 15
                (string) $Data->marital_status, // 16
                (string) $Data->address, // 17
                (string) $Data->emergency_contacts, // 18
                $Data->status == 0 ? 'Inactive' : 'Active', // 19
                $Data->no_of_child > 0 ? $Data->no_of_child : '0', // 20
                (string) $WorkShift ? $WorkShift->shift_name : '', // 21
                // (string) ($Data->work_shift_id==1 || $Data->work_shift_id==2) ? 'General' : '', // 21
                $Data->salary_ctc ?? 0, // 22
                $Data->salary_gross ?? 0, // 23
                // $Data->overtime_status == 0 ? 'Not Applicable' : 'Applicable', // 24
                // (string) $Data->esi_card_number, // 25
                // (string) $Data->pf_account_number, // 26
                // (string) $Data->bank_name, // 27
                // (string) $Data->bank_account, // 28
                // (string) $Data->bank_ifsc, // 29
                // (string) $Data->uan, // 30
                // (string) $Data->cost_centre, // 31
                // (string) $Data->pan_gir_no, // 32
                // $Data->permanent_status == 0 ? 'No' : 'Yes', // 33
                // $Data->pf_stauts == 0 ? 'No' : 'Yes', // 34
                (string)$Data->basic > 0 ? $Data->basic : '0',
                (string)$Data->da > 0 ? $Data->da : '0',
                (string)$Data->hra > 0 ? $Data->hra : '0',
                (string)$Data->pf > 0 ? $Data->pf : '0',
                (string)$Data->epf > 0 ? $Data->epf : '0',
                (string)$Data->insurance > 0 ? $Data->insurance  : '0'
            ];

            $inc++;
        }

        $heading = [

            [
                'SL.NO',
                'USER NAME',
                'ROLE NAME',
                'EMPLOYEE CODE',
                'DEPARTMENT',
                'DESIGNATION',
                'BRANCH',
                'SUPERVISOR',
                'PHONE',
                'PERSONAL EMAIL',
                'FIRST NAME',
                'LAST NAME',
                'DATE OF BIRTH',
                'DATE OF JOINING',
                'GENDER',
                'RELIGION',
                'MARITAL STATUS',
                'ADDRESS',
                'EMERGENCY CONTACT',
                'STATUS',
                'NO OF CHILD',
                'WORK SHIFT',
                'CTC',
                'GROSS',
                // 'OVERTIME STATUS',
                // 'ESI CARD NUMBER',
                // 'PF ACCOUNT NUMBER',
                // 'BANK NAME',
                // 'BANK ACCOUNT',
                // 'BANK IFSC',
                // 'UAN',
                // 'COST CENTRE',
                // 'PAN/GIR NO',
                // 'PERMANENT STATUS',
                // 'EPF STATUS',
                'BASIC',
                'DA',
                'HRA',
                'PF',
                'EPF',
                'INSURANCE',
            ],
        ];

        $extraData['heading'] = $heading;

        $filename = 'EmployeeInformation-' . DATE('dmYHis') . '.xlsx';

        // dd($filename, $extraData, $dataset);
        ob_end_clean();
        ob_start();
        return Excel::download(new EmployeeDetailsExport($dataset, $extraData), $filename);
    }

    public function getDesignation(Request $request)
    {
        // $designationList = DesignationCase::where('branch_id', '=', $request->branch_id)->orderBY('designation_id', 'asc')->get();
        $designationList = Designation::orderBY('designation_id', 'asc')->get();
        return json_encode($designationList);
    }

    public function getDepartment(Request $request)
    {
        // $departmentList = DepartmentCase::where('branch_id', '=', $request->branch_id)->orderBY('department_id', 'asc')->get();
        $departmentList = Department::orderBY('department_id', 'asc')->get();
        return json_encode($departmentList);
    }

    public function getShift(Request $request)
    {
        // $workShift = WorkShiftCase::where('branch_id', '=', $request->branch_id)->orderBY('work_shift_id', 'asc')->get();
        $workShift = WorkShift::orderBY('work_shift_id', 'asc')->get();
        return json_encode($workShift);
    }

    public function getSupervisor(Request $request)
    {
        $employee_id = session('logged_session_data.employee_id');
        $Supervisor = [];
        $SupervisorAll = Employee::join('user', 'user.user_id', 'employee.user_id')
            // ->where('employee.branch_id', '=', $request->branch_id)
            // ->whereIn('user.role_id', [2,3])
            // ->where('employee.employee_id', '!=', $employee_id)
            ->orderBY('employee.finger_id', 'asc')->get();
        foreach ($SupervisorAll as $key => $Employee) {
            $Supervisor[] = ['employee_id' => $Employee->employee_id, 'detailname' => $Employee->detailname()];
        }
        return json_encode($Supervisor);
    }

    public function getFunctionalHead(Request $request)
    {
        $employee_id = session('logged_session_data.employee_id');
        $FunctionalHead = [];
        $FunctionalHeadAll = Employee::join('user', 'user.user_id', 'employee.user_id')
            // ->where('employee.branch_id', '=', $request->branch_id)
            // ->whereIn('user.role_id', [2,3])
            // ->where('employee.employee_id', '!=', $employee_id)
            ->orderBY('employee.finger_id', 'asc')->get();
        foreach ($FunctionalHeadAll as $key => $Employee) {
            $FunctionalHead[] = ['employee_id' => $Employee->employee_id, 'detailname' => $Employee->detailname()];
        }
        return json_encode($FunctionalHead);
    }

    public function salaryRevisionRemoe(Request $request)
    {
        // dd('test');
        $sal_id = $request->sal_id;
        return $sal_id;
    }
}
