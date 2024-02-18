<?php

namespace App\Imports;

use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\Department;
use App\Model\Designation;
use App\Model\PayrollUpload;
use App\Model\Employee;
use App\Model\Role;
use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PayrollImport implements ToModel, WithValidation, WithStartRow, WithLimit
{
    use Importable;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function sanitize()
    {
        $this->data['*.21'] = trim($this->data['*.21']);
        dd($this->data);
    }

    public function rules(): array
    {
        return [
            '*.0'  => 'required',                      // SL.NO
            '*.1'  => 'required|regex:/^\S*$/u',       // EMP CODE // max:191|exists:employee,emp_code
            '*.2'  => 'required|numeric|min:0',        // OTHER EARNINGS
            '*.3'  => 'required|numeric|min:0',        // TDS
            '*.4' => 'required|numeric|min:0',         // SALARY ADVANCE
            '*.5' => 'required|numeric|min:0',         // LABOUR WELFARE
            '*.6' => 'required|numeric|min:0',         // PROFESSIONAL TAX
            '*.7' => 'required|numeric|min:0',         // EXCESS TELEPHONE USAGE
        ];
    }

    // other_earnings
    // tds
    // salary_advance
    // labour_welfare
    // professional_tax
    // excess_telephone_usage

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            // '1.required' => 'Salary month is required',
            // '1.date_format' => 'Salary month is does not match the format d/m/Y',
            '1.required' => 'Emp Code is required',
            '1.regex' => 'Space not allow to Emp Code',
            '1.exists' => 'Emp Code does not exist.',
            
            '2.required' => 'Other Earnings value is required',
            '2.numeric' => 'Other Earnings should be number',
            '2.min' => 'Other Earnings should be number grater than 0',
            
            '3.required' => 'TDS value is required',
            '3.numeric' => 'TDS should be number',
            '3.min' => 'TDS should be number grater than 0',
            
            '4.required' => 'Advance Salary is value required',
            '4.numeric' => 'Advance Salary should be number',
            '4.min' => 'Advance Salary should be number grater than 0',
            
            '5.required' => 'Labour Welfare is value required',
            '5.numeric' => 'Labour Welfare should be number',
            '5.min' => 'Labour Welfare should be number grater than 0',
            
            '6.required' => 'Professional Tax value is required',
            '6.numeric' => 'Professional Tax should be number',
            '6.min' => 'Professional Tax should be number grater than 0',
            
            '7.required' => 'Excess Telephone Usage value is required',
            '7.numeric' => 'Excess Telephone Usage should be number',
            '7.min' => 'Excess Telephone Usage should be number grater than 0',

        ];
    }

    public function model(array $row)
    {
        $salary_month = request()->post('salary_month', null);
        $checkEmployeePayroll = PayrollUpload::where('emp_code', $row[1])->where('salary_month', $salary_month)->first();
        $payrollArray = [
            'salary_month'              => $salary_month,
            'emp_code'                  => $row[1],
            'other_earnings'            => $row[2],
            'tds'                       => $row[3],
            'salary_advance'            => $row[4],
            'labour_welfare'            => $row[5],
            'professional_tax'          => $row[6],
            'excess_telephone_usage'    => $row[7],
        ];
        if($checkEmployeePayroll) {
            PayrollUpload::where('payroll_upload_id', $checkEmployeePayroll->payroll_upload_id)->update($payrollArray);
        } else {
            $PayrollUpload = new PayrollUpload;
            $PayrollUpload->salary_month              = $salary_month;
            $PayrollUpload->emp_code                  = $row[1];
            $PayrollUpload->other_earnings            = $row[2];
            $PayrollUpload->tds                       = $row[3];
            $PayrollUpload->salary_advance            = $row[4];
            $PayrollUpload->labour_welfare            = $row[5];
            $PayrollUpload->professional_tax          = $row[6];
            $PayrollUpload->excess_telephone_usage    = $row[7];
            $PayrollUpload->save();
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 300;
    }
}
