<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RhApplicationRequest extends FormRequest
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
            'holiday_id'         => 'required',
            'purpose'            => 'required',
        ];
    }

    public function messages()
    {
        return [
            'holiday_id.required'         => 'List of restricted holiday field is required.',
        ];
    }
}
