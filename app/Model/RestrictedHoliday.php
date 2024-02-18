<?php

namespace App\Model;

use App\Components\Common;
use App\Traits\BranchTrait;
use App\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class RestrictedHoliday extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'holiday_restricted';
    protected $primaryKey = 'holiday_id';

    protected $fillable = [
        'holiday_id', 'year_id', 'holiday_name', 'holiday_date', 'branch_id'
    ];


}
