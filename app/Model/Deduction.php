<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    // use BranchTrait;
    use CrudTrait;
    protected $table = 'deduction';
    protected $primaryKey = 'deduction_id';

    protected $fillable = [
        'deduction_id','branch_id', 'deduction_name','deduction_type','percentage_of_basic','limit_per_month'
    ];
}
