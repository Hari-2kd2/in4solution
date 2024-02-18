<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalaryTaxRequest extends FormRequest
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
        if (isset($this->taxSlab)) {
            return [
                // 'slab_salary_from'  => 'required|unique:salary_tax_slab,slab_salary_from,'.$this->slab_salary_from.',slab_id,'.$this->slab_id.',branch_id,'.session('logged_session_data.branch_id'),
                // 'slab_salary_to'  => 'required|unique:salary_tax_slab,slab_salary_to,'.$this->slab_salary_from.',slab_id,'.$this->slab_id.',branch_id,'.session('logged_session_data.branch_id'),
                'slab_salary_from'  => 'required',
                'slab_salary_to'  => 'required',
                'slab_percentage_of_tax' => 'required',
            ];
        }
        return [
            // 'slab_salary_from'  => 'required|unique:salary_tax_slab,slab_salary_from,'.$this->department.',slab_id,branch_id,'.session('logged_session_data.branch_id'),
            // 'slab_salary_to'  => 'required|unique:salary_tax_slab,slab_salary_to,'.$this->department.',slab_id,branch_id,'.session('logged_session_data.branch_id'),
            'slab_salary_from'  => 'required',
            'slab_salary_to'  => 'required',
            'slab_percentage_of_tax' => 'required',
        ];
    }
}
