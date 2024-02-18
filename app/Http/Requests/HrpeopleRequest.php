<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class HrpeopleRequest extends FormRequest
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
            'key_hr_emails'  => 'required|emails:1',
        ];
    }

    public function messages()
    {
        return [
            'key_hr_emails.required'            => 'Emails to be shared field is required.',
            'key_hr_emails.emails'            => 'Not a valid email list.',
        ];
    }
}
