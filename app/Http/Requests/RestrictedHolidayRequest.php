<?php

namespace App\Http\Requests;

use App\Model\calanderYear;
use Illuminate\Foundation\Http\FormRequest;

class RestrictedHolidayRequest extends FormRequest
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
        if ($this->restrictedHoliday) {
            return [
                // 'leave_type_name'  => 'required|unique:leave_type,leave_type_name,'.$this->leaveType.',leave_type_id,branch_id,'.session('logged_session_data.branch_id'),
                'holiday_name'  => 'required|unique:holiday_restricted,holiday_name,'.$this->restrictedHoliday.',holiday_id,year_id,'.$this->year_id.',branch_id,'.session('logged_session_data.branch_id'),
                'holiday_date'  => 'required'
            ];
        }
        return [
            'holiday_name'  => 'required|unique:holiday_restricted,holiday_name,'.$this->restrictedHoliday.',holiday_id,year_id,'.$this->year_id.',branch_id,'.session('logged_session_data.branch_id'),
            'holiday_date'  => 'required'
        ];
    }
}
