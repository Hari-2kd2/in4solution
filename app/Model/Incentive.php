<?php

namespace App\Model;

use App\Model\Employee;
use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'incentives';
    protected $primaryKey = 'incentive_details_id';

    protected $fillable = [
        'incentive_details_id', 'finger_print_id', 'employee_id', 'branch_id', 'incentive_date', 'working_date',  'comment',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'finger_id', 'finger_print_id');
    }
}
