<?php

namespace App\Imports;

use App\User;
use App\Model\Role;
use App\Model\Branch;
use App\Model\Employee;
use App\Model\WorkShift;
use App\Model\Department;
use App\Components\Common;
use App\Model\Designation;
use Illuminate\Support\Arr;
use App\Model\DepartmentCase;
use App\Model\DesignationCase;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithValidation, WithStartRow, WithLimit
{
    use Importable;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function sanitize()
    {
        $this->data['*.21'] = trim($this->data['*.21']);
    }

    public function rules(): array
    {
        $branch_id = Common::activeBranch();
        return [
            '*.0' => 'required', // SL.NO
            '*.1' => 'required|regex:/^\S*$/u', // USER NAME
            '*.2' => 'required|exists:role,role_name', // ROLE NAME
            '*.3' => 'required|regex:/^\S*$/u', // EMPLOYEE CODE
            '*.4' => 'required', // DEPARTMENT
            '*.5' => 'required', // DESIGNATION
            '*.6' => 'required|exists:branch,branch_name', // BRANCH
            '*.7' => 'nullable|exists:user,user_name', // SUPERVISOR
            '*.8' => 'required|min:10', // PHONE // regex:/[0-9]{9}/
            // '*.8' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10', // PHONE
            '*.9' => 'nullable|email|max:150', // PERSONAL EMAIL
            '*.10' => 'required|max:100', // FIRST NAME
            '*.11' => 'nullable|max:50', // LAST NAME
            '*.12' => 'required', // DATE OF BIRTH
            '*.13' => 'required',     // DATE OF JOINING
            '*.14' => function ($attribute, $value, $onFailure) { // GENDER
                $value = trim($value);
                $arr = ['Male', 'Female'];
                if (!in_array($value, $arr)) {
                    $onFailure('Gender is invalid, it should be Male/Female');
                }
            },
            '*.15' => 'nullable', // RELIGION
            '*.16' => function ($attribute, $value, $onFailure) { // MARITAL STATUS
                $value = trim($value);
                $arr = [null, 'Married', 'Unmarried'];
                if (!in_array($value, $arr)) {
                    $onFailure('Martial Status is invalid, it should be Married/Unmarried');
                }
            },
            '*.16' => 'nullable|in:Married,Unmarried',
            '*.17' => 'nullable', // ADDRESS
            '*.18' => 'nullable', // EMERGENCY CONTACT
            '*.19' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = ['Active', 'Inactive'];
                if (!in_array($value, $arr)) {
                    $onFailure('Status is invalid, it should be Active/Inactive');
                }
            },
            '*.20' => 'nullable|numeric|min:0', // No Of Child
            '*.21' => 'required', // WORK SHIFT
            '*.22' => 'required', // CTC
            '*.23' => 'required', // Gross
            '*.24' => 'nullable|numeric', // basic
            '*.25' => 'nullable|numeric', // hra
            '*.26' => 'nullable|numeric', // da
            '*.27' => 'nullable|numeric', // pf
            '*.28' => 'nullable|numeric', // epf
            '*.29' => 'nullable|numeric', // insurance

            // '*.24' => function ($attribute, $value, $onFailure) {
            //     $value = trim($value);
            //     $arr = ['Applicable', 'Not Applicable'];
            //     if (!$value) {
            //         $onFailure('Overtime status is required.');
            //     } else if ($value!='' && !in_array($value, $arr)) {
            //         $onFailure('Overtime status is invalid, it should be Applicable/Not Applicable');
            //     }
            // }, // Overtime Status
            // '*.25' => 'nullable|max:30',  // ESI CARD NUMBER
            // '*.26' => 'nullable|max:30',  // PF ACCOUNT NUMBER
            // '*.27' => 'nullable|max:100', // BANK NAME
            // '*.28' => 'nullable|max:30', // BANK ACCOUNT
            // '*.29' => 'nullable|max:30', // BANK IFSC
            // '*.30' => 'nullable|max:50', // UAN
            // '*.31' => 'nullable|max:50', // COST CENTRE
            // '*.32' => 'nullable|max:50', // PAN/GIR NO
            // '*.33' => function ($attribute, $value, $onFailure) {
            //     $value = trim($value);
            //     $arr = ['Yes', 'No'];
            //     if ($value!='' && !in_array($value, $arr)) {
            //         $onFailure('Permanent status is invalid, it should be Yes/No');
            //     }
            // }, // PERMANENT STATUS
            // '*.34' => function ($attribute, $value, $onFailure) {
            //     $value = trim($value);
            //     $arr = ['Yes', 'No'];
            //     if (!$value) {
            //         $onFailure('EPF Status is required.');
            //     } else if ($value!='' && !in_array($value, $arr)) {
            //         $onFailure('EPF Status is invalid, it should be Yes/No');
            //     }
            // }, // EPF STATUS

        ];
    }

    public function customValidationMessages()
    {
        // $email = Arr::get($this->data, '9');
        // 0	SL.NO               1	USER NAME               2	ROLE NAME           3	EMPLOYEE CODE           4	DEPARTMENT
        // 5	DESIGNATION         6	BRANCH                  7	SUPERVISOR          8	PHONE                   9	PERSONAL EMAIL
        // 10	FIRST NAME          11	LAST NAME               12	DATE OF BIRTH       13	DATE OF JOINING         14	GENDER
        // 15	RELIGION            16	MARITAL STATUS          17	ADDRESS             18	EMERGENCY CONTACT       19	STATUS
        // 20	NO OF CHILD         21	WORK SHIFT              22	CTC                 23	GROSS                   24	OVERTIME STATUS
        // 25	ESI CARD NUMBER     26	PF ACCOUNT NUMBER       27	BANK NAME           28	BANK ACCOUNT            29	BANK IFSC
        // 30	UAN                 31	COST CENTRE             32	PAN/GIR NO          33	PERMANENT STATUS        34  EPF STATUS      

        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'Username is required',
            '1.regex'  => 'Space not allowed in Username',
            '1.unique' => 'Username should be unique',
            '2.required' => 'Role name is required',
            '2.exists'   => 'Role name should be same as the name provided in Master',
            '3.required' => 'Employee Code is an unique required ',
            '3.regex'  => 'Space not allowed in Employee Code',
            '3.unique' => 'Employee Code should be unique',
            '4.required' => 'Department is required',
            '4.exists' => 'Department should be same as the name provided in Master',
            '5.required' => 'Designation is required',
            '5.exists' => 'Designation should be same as the name provided in Master',
            '6.required' => 'Branch Name is required',
            '6.exists' => 'Branch name should be same as the name provided in Master',
            '7.nullable' => 'HOD Name is optional',
            '7.exists' => 'HOD Username doest not exists',
            '8.required' => 'Phone No is required',
            '8.regex' => 'Phone No is invalid',
            '8.min' => 'Phone No should be min 10 digits',
            '9.email' => 'Email is not valid',
            '9.max' => 'Email should not be greater than 50 characters.',
            '10.required' => 'Employee first name is required',
            '10.max' => 'Employee first name should not be greater than 30 characters.',
            '11.required' => 'Employee last name is required',
            '11.max' => 'Employee last name should not be greater than 30 characters.',
            '12.required' => 'Date of birth is required',
            '12.date_format' => 'Date of birth is does not match the format d/m/Y',
            '13.required' => 'Date of joining is required',
            '13.date_format' => 'Date of joining is does not match the format d/m/Y',
            '14.in' => 'Invalid Gender, can user only Male/Female',
            '15.required' => 'Religion is required',
            '16.in' => 'Invalid Marital status, can user only use Married/Unmarried',
            '17.required' => 'Address is required',
            '18.required' => 'Emergency Contact is required',
            '19.required' => 'Status is required',
            '19.in' => 'Status is invalid, it should be Active/Inactive',
            '20.required' => 'No of Child is required',
            '21.required' => 'Work shift is required',
            '21.in' => 'Work shift is invalid, it should be General/Rotational',
            '22.required' => 'CTC is required',
            '23.required' => 'Gross is required',
            // '24.required' => 'Overtime Status is required',
            // '24.in' => 'Overtime Status is invalid, it should be Applicable/Not Applicable',
            // '25.max' => 'ESI Card Number should not be greater than 30 characters.:',  // ESI CARD NUMBER
            // '26.max' => 'PF Account Number should not be greater than 30 characters.:',  // PF ACCOUNT NUMBER
            // '27.max' => 'Bank Name should not be greater than 100 characters.:', // BANK NAME
            // '28.max' => 'Bank Account should not be greater than 30 characters.:', // BANK ACCOUNT
            // '29.max' => 'Bank IFSC should not be greater than 30 characters.:', // BANK IFSC
            // '30.max' => 'UAN should not be greater than 50 characters.:', // UAN
            // '31.max' => 'Cost Centre should not be greater than 50 characters.:', // COST CENTRE
            // '32.max' => 'PAN/GIR NO should not be greater than 50 characters.:', // PAN/GIR NO
            // '33.in' => 'Permanent status is invalid, it should be Yes/No',
            // '34.in' => 'EPF Status is invalid, it should be Yes/No',
        ];
    }

    public function model(array $row)
    {

        // info($row);

        // 0	1	        2	        3	        4	            5	            6	        7	            8	    9
        // SNO  user_name	role_id	    emp_code	department_id	designation_id	branch_id	supervisor_id	phone	email

        // 10	        11	        12	            13	            14	    15	        16	            17	        18	                19
        // first_name	last_name	date_of_birth	date_of_joining	gender	religion	marital_status	address	    emergency_contacts	status

        // 20	        21	        22	        23	            24	            25	            26	                27	        28	            29
        // no_of_child	work_shift	salary_ctc	salary_gross	overtime_status	esi_card_number	pf_account_number	bank_name	bank_account	bank_ifsc

        // 30	31          32	        33                  34
        // uan	cost_centre	pan_gir_no	permanent_status    pf_status


        $usr_status = UserStatus::$ACTIVE;
        $work_shift = null;
        $row[3] = trim($row[3]);
        $Employee = Employee::where('emp_code', $row[3])->first();
        $User = new User;
        // info('emp_code[3]='.$row[3].', len='.strlen($row[3]));
        if ($Employee) {
            $User = User::where('user_id', $Employee->user_id)->first();
        }

        $dob = null;
        $doj = null;
        $branch_id = null;
        $branch = Branch::where('branch_name', $row[6])->first();
        if ($branch) {
            $branch_id = $branch->branch_id;
        }
        $password = 'demo1234';

        if ($row[12]) {
            try {
                $dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[12])->format('Y-m-d');
            } catch (\Throwable $th) {
                $dob = date('Y-m-d', strtotime($row[12]));
            }
        }

        if ($row[13]) {
            try {
                $doj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[13])->format('Y-m-d');
            } catch (\Throwable $th) {
                $doj = date('Y-m-d', strtotime($row[13]));
            }
        }

        $role = Role::where('role_name', $row[2])->first();
        $department = Department::where('department_name', $row[4])->first();
        $designation = Designation::where('designation_name', $row[5])->first();
        $WorkShift = DB::table('work_shift')->where('shift_name', $row[21])->first();
        $emp = new Employee;
        if (isset($row[7])) {
            $superUser = User::where('user_name', $row[7])->first();
            $emp = Employee::where('user_id', $superUser->user_id)->first();
        }

        $usr_status = UserStatus::$INACTIVE;
        if ($row[19] == 'Active') {
            $usr_status = UserStatus::$ACTIVE;
        }

        $no_of_child = 0;
        if ($row[20] > 0) {
            $no_of_child = $row[20];
        }

        if ($WorkShift) {
            $work_shift = $WorkShift->work_shift_id;
        }

        $esi_card_number = $pf_account_number = $bank_name = $bank_account = $bank_ifsc = $uan = $cost_centre = $pan_gir_no = null;
        $permanent_status = 0;
        $pf_status = 1;

        if ($row[25]) {
            $esi_card_number = $row[25];
        }
        if ($row[26]) {
            $pf_account_number = $row[26];
        }
        if ($row[27]) {
            $bank_name = $row[27];
        }
        if ($row[28]) {
            $bank_account = $row[28];
        }
        if ($row[29]) {
            $bank_ifsc = $row[29];
        }
        // if ($row[30]) {
        //     $uan = $row[30];
        // }
        // if ($row[31]) {
        //     $cost_centre = $row[31];
        // }
        // if ($row[32]) {
        //     $pan_gir_no = $row[32];
        // }
        if ($doj) {
            $TODAY = date('Y-m-d');
            $monthDiffs = monthDiffs($TODAY, $doj);
            if ($monthDiffs >= 6) {
                $permanent_status = 1;
            }
        } else if ($row[33] == 'Yes') {
            $permanent_status = 1;
        }
        // if ($row[34] == 'No') {
        //     $pf_status = 0;
        // } else if ($row[34] == 'Yes') {
        //     $pf_status = 1;
        // }


        // 0	1	        2	        3	        4	            5	            6	        7	            8	    9
        // SNO  user_name	role_id	    emp_code	department_id	designation_id	branch_id	supervisor_id	phone	email

        // 10	        11	        12	            13	            14	    15	        16	            17	        18	                19
        // first_name	last_name	date_of_birth	date_of_joining	gender	religion	marital_status	address	    emergency_contacts	status

        // 20	        21	        22	        23	            24	            25	            26	                27	        28	            29
        // no_of_child	work_shift	salary_ctc	salary_gross	overtime_status	esi_card_number	pf_account_number	bank_name	bank_account	bank_ifsc

        // 30	31          32	        33
        // uan	cost_centre	pan_gir_no	permanent_status

        if ($Employee) {
            $userData = User::where('user_id', $User->user_id)->update([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                'branch_id' => $branch_id,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
            // info('UPDATE emp_code='.$Employee->emp_code);
            $employeeData = Employee::where('employee_id', $Employee->employee_id)->update([
                'user_id' => $User->user_id,
                'finger_id' => $row[3],
                'device_employee_id' => $row[3],
                'emp_code' => $row[3],
                'department_id' => $department ? $department->department_id : null,
                'designation_id' => $designation ? $designation->designation_id : null,
                'branch_id' => $branch_id,
                'supervisor_id' => isset($emp->employee_id) ? $emp->employee_id : null,
                'phone' => $row[8],
                'email' => $row[9],
                'first_name' => $row[10],
                'last_name' => $row[11],
                'date_of_birth' => $dob != '1970-01-01' ? $dob : $Employee->date_of_birth,
                'date_of_joining' => $doj != '1970-01-01' ? $doj : $Employee->date_of_joining,
                'gender' => $row[14],
                'religion' => $row[15],
                'marital_status' => $row[16],
                'address' => $row[17],
                'emergency_contacts' => $row[18],
                'salary_ctc' => $row[22],
                'salary_gross' => $row[23],
                'work_shift' => $work_shift,
                'work_shift_id' => $work_shift,
                'status' => $usr_status,
                'no_of_child' => $no_of_child,
                'esi_card_number' => $esi_card_number,
                'pf_account_number' => $pf_account_number,
                'bank_name' => $bank_name,
                'bank_account' => $bank_account,
                'bank_ifsc' => $bank_ifsc,
                'uan' => $uan,
                'cost_centre' => $cost_centre,
                'pan_gir_no' => $pan_gir_no,
                'permanent_status' => $permanent_status,
                'pf_status' => $pf_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
                'basic' => $row[24],
                'hra' => $row[25],
                'da' => $row[26],
                'pf' => $row[27],
                'epf' => $row[28],
                'insurance' => $row[29],
            ]);
            $addEmployeeLeaves = \App\Components\Common::addEmployeeLeaves($Employee->employee_id);
        } else {
            $userData = User::create([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                'password' => Hash::make($password),
                'org_password' => $password,
                'branch_id' => $branch_id,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
            // info('New emp_code='.$row[3].', user_id='.$userData->user_id);
            $Employee = new Employee;
            $Employee->user_id = $userData->user_id;
            $Employee->finger_id = $row[3];
            $Employee->device_employee_id = $row[3];
            $Employee->emp_code = $row[3];
            $Employee->department_id = $department ? $department->department_id : null;
            $Employee->designation_id = $designation ? $designation->designation_id : null;
            $Employee->branch_id = $branch_id;
            $Employee->supervisor_id = $emp && $emp->employee_id ? $emp->employee_id : null;
            $Employee->phone = $row[8];
            $Employee->email = $row[9];
            $Employee->first_name = $row[10];
            $Employee->last_name = $row[11];
            $Employee->date_of_birth = $dob;
            $Employee->date_of_joining = $doj;
            $Employee->gender = $row[14];
            $Employee->religion = $row[15];
            $Employee->marital_status = $row[16];
            $Employee->address = $row[17];
            $Employee->emergency_contacts = $row[18];
            $Employee->salary_ctc = $row[22];
            $Employee->salary_gross = $row[23];
            $Employee->work_shift = $work_shift;
            $Employee->work_shift_id = $work_shift;
            $Employee->status = $usr_status;
            $Employee->no_of_child = $no_of_child;
            $Employee->esi_card_number = $esi_card_number;
            $Employee->pf_account_number = $pf_account_number;
            $Employee->bank_name = $bank_name;
            $Employee->bank_account = $bank_account;
            $Employee->bank_ifsc = $bank_ifsc;
            $Employee->uan = $uan;
            $Employee->cost_centre = $cost_centre;
            $Employee->pan_gir_no = $pan_gir_no;
            $Employee->permanent_status = $permanent_status;
            $Employee->pf_status = $pf_status;
            $Employee->created_by = auth()->user()->user_id;
            $Employee->updated_by = auth()->user()->user_id;
            $Employee->basic = $row[24];
            $Employee->hra = $row[25];
            $Employee->da = $row[26];
            $Employee->pf = $row[27];
            $Employee->epf = $row[28];
            $Employee->insurance = $row[29];
            $Employee->save();

            $addEmployeeLeaves = \App\Components\Common::addEmployeeLeaves($Employee->employee_id);
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 300;
    }
}
