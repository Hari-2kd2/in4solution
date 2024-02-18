<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyPolicyRequest extends FormRequest
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
            'branch_id'   => 'nullable',
            'policy_type'       => 'required',
            'title'       => 'required|string',
            'file'        => 'required|mimes:jpeg,jpg,png,pdf,xlsx,doc,docx,ppt,pptx|max:5120',
        ];
    }
}
