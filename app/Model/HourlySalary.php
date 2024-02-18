<?php

namespace App\Model;

use App\Traits\BranchTrait;
use App\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class HourlySalary extends Model
{

    // use BranchTrait;
    // use CrudTrait;
    protected $table = 'hourly_salaries';
    protected $primaryKey = 'hourly_salaries_id';

    protected $fillable = [
        'hourly_salaries_id', 'hourly_grade', 'hourly_rate'
    ];
}
