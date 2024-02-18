<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyForLeaveBalanceRequest extends FormRequest
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
        return [
            'leave_type_id'   => 'required',
            'employee_id'     => 'required', 
            // 'year'            => 'required|numeric', 
            'leave_balance'   => 'required|numeric', 
        ];
    }

    public function messages()
    {
        return [
            'leave_type_id.required'   => 'The leave type field is required.',
            'employee_id.required'     => 'Select an employee is required.',
            // 'year.required'            => 'The year field is required.',
            'leave_balance.required'   => 'The leave blanace field is required.',
        ];
    }
}
