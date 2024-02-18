<?php

namespace App\Http\Requests;

use App\Model\Employee;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (isset($this->employee)) {
            $result = Employee::where('employee_id', $this->employee)->first();
            return [
                'role_id'        => 'required',
                'user_name'      => 'required|unique:user,user_name,' . $result->user_id . ',user_id',
                'first_name'     => 'required',
                'finger_id'      => 'required|unique:employee,finger_id,' . $this->employee . ',employee_id',
                'emp_code'       => 'required|unique:employee,emp_code,' . $this->employee . ',employee_id',
                'department_id'  => 'required',
                'designation_id' => 'required',
                'work_shift'     => 'required',
                'salary_ctc'     => 'required',
                'salary_gross'   => 'required',
                'overtime_status'=> 'required',
                'marital_status' => 'required',
                'pf_status'      => 'required',
                'gender'         => 'required',
                'status'         => 'required',
                'photo'          => 'mimes:jpeg,jpg,png|max:200',
                'salary_revision'=> 'nullable',
                'supervisor_id'  => 'nullable',
                'date_of_birth'     => 'required',
                'date_of_joining'   => 'required',
                'email'          => 'nullable|email|unique:employee,email,'.$this->employee.',employee_id',
                
                // pf_account_number
                // esi_card_number
                // bank_account
                // bank_ifsc
                // emp_code
                // 'designation.*'  => 'required',
                // 'salary_limit'   => 'required',
                // 'email'             => 'nullable|unique:employee,email,'.$this->employee.',employee_id',
                // 'phone'             => 'required',
                // 'institute.*'       => 'required',
                // 'board_university.*'=> 'required',
                // 'degree.*'          => 'required',
                // 'passing_year.*'    => 'required',
                // 'organization_name.*' => 'required',
                // 'from_date.*'      => 'required',
                // 'to_date.*'        => 'required',
                // 'responsibility.*' => 'required',
                // 'skill.*'          => 'required',
            ];
        }
        return [
            'role_id'        => 'required',
            'user_name'      => 'required|unique:user',
            'password'       => 'required|confirmed',
            'first_name'     => 'required',
            'finger_id'      => 'required|unique:employee',
            'emp_code'       => 'required|unique:employee',
            'department_id'  => 'required',
            'designation_id' => 'required',
            'work_shift'     => 'required',
            'salary_ctc'     => 'required',
            'salary_gross'   => 'required',
            'marital_status' => 'required',
            'pf_status'      => 'required',
            'overtime_status'=> 'required',
            'gender'         => 'required',
            'status'         => 'required',
            'photo'          => 'mimes:jpeg,jpg,png|max:200',
            'salary_revision'=> 'nullable',
            'supervisor_id'  => 'nullable',
            'email'          => 'nullable|email|unique:employee',
            'date_of_birth'     => 'required',
            'date_of_joining'   => 'required',
        ];
    }

    public function messages()
    {
        return [
            'role_id.required'            => 'The role field is required.',
            'user_name.required'          => 'The user name is required.',
            'password.required'           => 'The password field is required.',
            'first_name.required'         => 'The first name field is required.',
            'finger_id.required'          => 'The fingerprint no field is required.',
            'emp_code.required'           => 'The Emp code field is required.',
            'department_id.required'      => 'The department field is required.',
            'designation_id.required'     => 'The designation field is required.',
            'work_shift.required'         => 'The work shift field is required.',
            'salary_ctc.required'         => 'The CTC field is required.',
            'salary_gross.required'       => 'The Gross field is required.',
            'marital_status.required'     => 'The marital status field required.',
            'overtime_status.required'    => 'The overtime allowed field required.',
            'gender.required'             => 'The gender field is required.',
            'status.required'             => 'The status field is required.',
            'photo.mimes'                 => 'Photo allow only the jpeg, jpg, png file types',
        ];
    }
}
