<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class KeypeopleRequest extends FormRequest
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
            'key_director_emails'  => 'required|emails:1',
            'key_user_ids'  => 'required|userids:1',
        ];
    }

    public function messages()
    {
        return [
            'key_user_ids.required'            => 'The key people field is required.',
            'key_director_emails.required'            => 'Emails to be shared field is required.',
            'key_director_emails.emails'            => 'Not a valid email list.',
        ];
    }
}
