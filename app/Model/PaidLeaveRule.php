<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class PaidLeaveRule extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'paid_leave_rules';
    protected $primaryKey = 'paid_leave_rule_id';

    protected $fillable = [
        'paid_leave_rule_id', 'branch_id', 'for_year', 'day_of_paid_leave'
    ];
}
