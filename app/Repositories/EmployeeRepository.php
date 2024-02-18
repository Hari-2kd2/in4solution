<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Model\Employee;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeRepository
{

    public function overTime()
    {
        $results = ['Not Applicable', 'Applicable'];
        // $options = ['' => '---- Please select ----'];
        foreach ($results as $key => $value) {
            $options[$key] = $value;
        }
        return $options;
    }
    public function incentive()
    {
        $results = ['Not Applicable', 'Applicable'];
        $options = ['' => '---- Please select ----'];
        foreach ($results as $key => $value) {
            $options[$key] = $value;
        }
        return $options;
    }
    public function salaryLimit()
    {
        $results = ['< 20000', '> 20000'];
        $options = ['' => '---- Please select ----'];
        foreach ($results as $key => $value) {
            $options[$key] = $value;
        }
        return $options;
    }
    public function workShift()
    {
        $results = ['General', 'Rotational'];
        $options = ['' => '---- Please select ----'];
        foreach ($results as $key => $value) {
            $options[$key] = $value;
        }
        return $options;
    }

    public function makeEmployeeAccountDataFormat($data, $action = false)
    {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');

        $employeeAccountData['role_id'] = $data['role_id'];
        if ($action != 'update') {
            $employeeAccountData['password'] = Hash::make(isset($data['password']) ? $data['password'] : 'demo1234');
        }
        $employeeAccountData['user_name'] = $data['user_name'];

        if ($branchId == null && $roleId == 1 && $selectedbranchId) {
            $employeeAccountData['branch_id'] = $data['branch_id'];
        }
        $employeeAccountData['status'] = $data['status'];

        $employeeAccountData['created_by'] = 1;

        $employeeAccountData['updated_by'] = 1;

        return $employeeAccountData;
    }

    public function makeEmployeePersonalInformationDataFormat($data)
    {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');

        $employeeData['first_name'] = $data['first_name'];
        $employeeData['last_name'] = $data['last_name'];
        $employeeData['finger_id'] = $data['finger_id'];
        $employeeData['device_employee_id'] = $data['finger_id'];
        $employeeData['supervisor_id'] = $data['supervisor_id'] ? $data['supervisor_id'] : null;
        $employeeData['work_shift'] = $data['work_shift'];
        $employeeData['work_shift_id'] = $data['work_shift'];
        $employeeData['email'] = $data['email'];
        $employeeData['date_of_birth'] = dateConvertFormtoDB($data['date_of_birth']);
        $employeeData['date_of_joining'] = dateConvertFormtoDB($data['date_of_joining']);
        $employeeData['date_of_leaving'] = isset($data['date_of_leaving']) && $data['date_of_leaving'] ? dateConvertFormtoDB($data['date_of_leaving']) : null;
        $employeeData['relieving_reason'] = isset($data['relieving_reason']) && $data['date_of_leaving'] ? $data['date_of_leaving'] : null;
        // a employee relieving reason input than the employee status will be changed as Inactive

        if (isset($data['relieving_reason']) && $data['relieving_reason']) {
            $data['status'] = UserStatus::$INACTIVE;
        }
        // else if ($data['status'] == UserStatus::$INACTIVE) {
        //     $data['status'] = UserStatus::$ACTIVE;
        // }
        else {
            $data['status'] = $data['status'];
        }   

        if ($data['date_of_joining'] && $data['permanent_status'] != '1') {
            $TODAY = date('Y-m-d');
            $monthDiffs = monthDiffs($TODAY, dateConvertFormtoDB($data['date_of_joining']));
            if ($monthDiffs >= 6) {
                $employeeData['permanent_status'] = 1;
            }
        }
        $employeeData['salary_revision'] = dateConvertFormtoDB($data['salary_revision']);
        $employeeData['marital_status'] = $data['marital_status'];
        $employeeData['pf_status'] = $data['pf_status'];
        $employeeData['overtime_status'] = $data['overtime_status'];
        $employeeData['address'] = $data['address'];
        $employeeData['emergency_contacts'] = $data['emergency_contacts'];
        $employeeData['gender'] = $data['gender'];
        $employeeData['religion'] = $data['religion'];
        $employeeData['phone'] = $data['phone'];
        $employeeData['status'] = $data['status'];
        $employeeData['blood_group'] = $data['blood_group'];
        $employeeData['permanent_status'] = $data['permanent_status'];
        $employeeData['department_id'] = $data['department_id'];
        $employeeData['designation_id'] = $data['designation_id'];
        $employeeData['salary_ctc'] = $data['salary_ctc'];
        $employeeData['salary_gross'] = $data['salary_gross'];
        $employeeData['uan'] = $data['uan'];
        $employeeData['cost_centre'] = $data['cost_centre'];
        $employeeData['pan_gir_no'] = $data['pan_gir_no'];
        $employeeData['pf_account_number'] = $data['pf_account_number'];
        $employeeData['esi_card_number'] = $data['esi_card_number'];
        $employeeData['bank_name'] = $data['bank_name'];
        $employeeData['bank_account'] = $data['bank_account'];
        $employeeData['bank_ifsc'] = $data['bank_ifsc'];
        $employeeData['emp_code'] = $data['emp_code'];
        $employeeData['basic'] = $data['basic'];
        $employeeData['hra'] = $data['hra'];
        $employeeData['da'] = $data['da'];
        $employeeData['pf'] = $data['pf'];
        $employeeData['epf'] = $data['epf'];
        $employeeData['insurance'] = $data['insurance'];
        $employeeData['functional_head_id'] = $data['functional_head_id'];
        $employeeData['relieving_remark'] = $data['relieving_remark'];


        if ($salary_revision = $employeeData['salary_revision']) {
            $date = new \DateTime($salary_revision);
            $month = $date->format('n');
            $year = $date->format('Y');
            if ($month == 4 || $month == 10) {
                $d = strtotime("$year-$month-01");
                $stops = date('Y-m-d', $d);
                $employeeData['salary_esi_stop'] = $stops;
            } else {
                if ($month >= 1 && $month <= 3) {
                    $d = strtotime("March 01 $year");
                    $stops = date('Y-m-d', $d);
                } else if ($month >= 5 && $month <= 9) {
                    $d = strtotime("September 01 $year");
                    $stops = date('Y-m-d', $d);
                } else if ($month >= 11 && $month <= 12) {
                    $d = strtotime("March 01 " . ($year + 1));
                    $stops = date('Y-m-d', $d);
                }
                $employeeData['salary_esi_stop'] = $stops;
            }
        }

        if (isset($data['document_file'])) {
            $employeeData['document_name'] = date('Y_m_d_H_i_s') . '_' . $data['document_file']->getClientOriginalName();
        }

        if (isset($data['document_file2'])) {
            $employeeData['document_name2'] = date('Y_m_d_H_i_s') . '_' . $data['document_file2']->getClientOriginalName();
        }

        if (isset($data['document_file3'])) {
            $employeeData['document_name3'] = date('Y_m_d_H_i_s') . '_' . $data['document_file3']->getClientOriginalName();
        }

        if (isset($data['document_file4'])) {
            $employeeData['document_name4'] = date('Y_m_d_H_i_s') . '_' . $data['document_file4']->getClientOriginalName();
        }

        if (isset($data['document_file5'])) {
            $employeeData['document_name5'] = date('Y_m_d_H_i_s') . '_' . $data['document_file5']->getClientOriginalName();
        }
        if (isset($data['document_file6'])) {
            $employeeData['document_name6'] = date('Y_m_d_H_i_s') . '_' . $data['document_file6']->getClientOriginalName();
        }

        if ($branchId == null && $roleId == 1 && $selectedbranchId) {
            $employeeData['branch_id'] = $data['branch_id'];
        }

        $employeeData['document_title'] = $data['document_title'];
        $employeeData['document_title2'] = $data['document_title2'];
        $employeeData['document_title3'] = $data['document_title3'];
        $employeeData['document_title4'] = $data['document_title4'];
        $employeeData['document_title5'] = $data['document_title5'];
        $employeeData['document_title6'] = $data['document_title6'];

        // $employeeData['document_expiry'] = $data['document_expiry'];
        // $employeeData['document_expiry2'] = $data['document_expiry2'];
        // $employeeData['document_expiry3'] = $data['document_expiry3'];
        // $employeeData['document_expiry4'] = $data['document_expiry4'];
        // $employeeData['document_expiry5'] = $data['document_expiry5'];
        // $employeeData['document_expiry6'] = $data['document_expiry6'];

        // $employeeData['work_shift_id'] = $data['work_shift_id'];
        // $employeeData['pay_grade_id'] = $data['pay_grade_id'];
        // $employeeData['hourly_salaries_id'] = $data['hourly_salaries_id'];
        // $employeeData['incentive'] = $data['incentive'];
        // $employeeData['salary_limit'] = $data['salary_limit'];

        $employeeData['created_by'] = 1;
        $employeeData['updated_by'] = 1;

        return $employeeData;
    }

    public function makeEmployeeEducationDataFormat($data, $employee_id, $action = false)
    {

        $educationData = [];

        if (isset($data['institute'])) {

            for ($i = 0; $i < count($data['institute']); $i++) {

                $educationData[$i] = [

                    'employee_id' => $employee_id,

                    'institute' => $data['institute'][$i],

                    'board_university' => $data['board_university'][$i],

                    'degree' => $data['degree'][$i],

                    'passing_year' => $data['passing_year'][$i],

                    'result' => $data['result'][$i],

                    'cgpa' => $data['cgpa'][$i],

                ];

                if ($action == 'update') {

                    $educationData[$i]['educationQualification_cid'] = $data['educationQualification_cid'][$i];
                }
            }
        }

        return $educationData;
    }

    public function makeEmployeeExperienceDataFormat($data, $employee_id, $action = false)
    {

        $experienceData = [];

        if (isset($data['organization_name'])) {

            for ($i = 0; $i < count($data['organization_name']); $i++) {

                $experienceData[$i] = [

                    'employee_id' => $employee_id,

                    'organization_name' => $data['organization_name'][$i],

                    'designation' => $data['designation'][$i],

                    'from_date' => dateConvertFormtoDB($data['from_date'][$i]),

                    'to_date' => dateConvertFormtoDB($data['to_date'][$i]),

                    'responsibility' => $data['responsibility'][$i],

                    'skill' => $data['skill'][$i],

                ];

                if ($action == 'update') {

                    $experienceData[$i]['employeeExperience_cid'] = $data['employeeExperience_cid'][$i];
                }
            }
        }

        return $experienceData;
    }

    public function bonusDayEligibility()
    {

        $employees = Employee::select(DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName'), 'designation_name', 'department_name', 'date_of_joining', 'date_of_leaving', 'finger_id', 'employee_id', 'branch_name')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('department', 'department.department_id', '=', 'employee.department_id')
            ->join('branch', 'branch.branch_id', '=', 'employee.branch_id')
            ->where('status', UserStatus::$ACTIVE)->where("date_of_joining", "<=", Carbon::now()->subMonths(24))->orderBy('date_of_joining', 'asc')->get();
        $dataFormat = [];
        $tempArray = [];
        if (count($employees) > 0) {
            foreach ($employees as $employee) {
                $tempArray['date_of_joining'] = $employee->date_of_joining;
                $tempArray['date_of_leaving'] = $employee->date_of_leaving;
                $tempArray['employee_id'] = $employee->employee_id;
                $tempArray['designation_name'] = $employee->designation_name;
                $tempArray['fullName'] = $employee->fullName;
                $tempArray['phone'] = $employee->phone;
                $tempArray['finger_id'] = $employee->finger_id;
                $tempArray['department_name'] = $employee->department_name;
                $tempArray['branch_name'] = $employee->branch_name;

                $dataFormat[$employee->employee_id][] = $tempArray;
            }
        } else {
            $tempArray['status'] = 'No Data Found';
            $dataFormat[] = $tempArray['status'];
        }
        return $dataFormat;
    }
}
