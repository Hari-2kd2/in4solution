<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkShiftRequest extends FormRequest
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
        if(isset($this->workShift)){
            return [
                'shift_name'  => 'required|unique:work_shift,shift_name,'.$this->workShift.',work_shift_id,branch_id,'.session('logged_session_data.branch_id'),
            ];
        }
        return [
            'shift_name'  => 'required|unique:work_shift,shift_name,'.$this->workShift.',work_shift_id,branch_id,'.session('logged_session_data.branch_id'),
            'start_time' => 'required',
            'end_time' => 'required',
            'late_count_time' => 'required',
        ];
    }
}
