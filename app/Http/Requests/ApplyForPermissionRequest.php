<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyForPermissionRequest extends FormRequest
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
            'permission_date'           => 'required',
            'purpose'                   => 'required',
        ];
    }

    public function messages()
    {
        return [
            'permission_date.required'  => 'The Permission Date field is required.',
            'purpose.required'          => 'The Purpose field is required.',
        ];
    }
}
