<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class SalaryTax extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'salary_tax_slab';
    protected $primaryKey = 'slab_id';

    protected $fillable = [
        'slab_id', 'slab_salary_from', 'slab_salary_to', 'slab_percentage_of_tax', 'branch_id'
    ];

}
