<?php

namespace App\Model;

// use App\Traits\CrudTrait;
// use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class CompOff extends Model
{
    // use BranchTrait;
    // use CrudTrait;

    protected $table = 'comp_off';
    protected $primaryKey = 'comp_off_details_id';

    protected $fillable = [
        'comp_off_details_id', 'finger_print_id', 'employee_id', 'branch_id', 'off_date', 'working_date', 'off_timing', 'comment',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'finger_id', 'finger_print_id');
    }
}
