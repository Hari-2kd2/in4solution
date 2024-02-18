<?php

namespace App\Model;

use App\User;
use App\Traits\CrudTrait;
use App\Components\Common;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;
use ESolution\DBEncryption\Traits\EncryptedAttribute;

class PayrollStatement extends Model
{
    // use BranchTrait;
    // use CrudTrait;
    // use EncryptedAttribute;

    protected $table = 'payroll_statement';
    protected $primaryKey = 'payroll_id';
    protected $fillable = ['employee_id','finger_print_id','emp_code','fullname','salary_month','salary_freeze','LOP','Basic','HRA','LTA','Special_Allowance','EarnedGross','Other_earnings','OT_PER_HOUR','Over_Time','OT_ESI_Employer','OT_ESI_Employee','Nett_Gross','EarnedCTC','PF_Employee','ESI_Employee','TDS','Salary_Advance','Excess_Telephoone_Usage','Labour_Welfare','Professional_Tax','ESI_Employer','PF_Employer','Bonus','Total_Deduction','Net_Salary','payroll_upload_id'];

    public function __construct($params = []){

    }



}
