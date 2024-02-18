<?php

namespace App\Model;

use App\Components\Common;
use Illuminate\Database\Eloquent\Model;

class ExcelEmployee extends Model
{
    protected $table = 'payroll_upload';
    protected $primaryKey = 'payroll_upload_id';

    // Half Day present (0.5 P) (Any time between 4:00 hours & 8:20 hours is a Half day)
    // Less than 4:00 hours - it is LOP (though they have punches)
    // Full Day Present (1 P)- should be equal or greater than 8:20 hours

    // const SHIFT_HOURS = '08:30:00';
    const PAYROLL_START_DATE = 26;
    const PAYROLL_END_DATE = 25;

    public function Employee()
    {
        return $this->hasOne('App\Model\Employee', 'emp_code', 'emp_code');
    }

    public function statmentEmployee()
    {
        return $this->hasOne('App\Model\Employee', 'emp_code', 'emp_code'); //->where('branch_id', $branch_id);
    }
}
