<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class WeeklyHoliday extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'weekly_holiday';
    protected $primaryKey = 'week_holiday_id';

    protected $fillable = [
        'week_holiday_id', 'branch_id', 'day_name', 'status', 'weekoff_days', 'month', 'employee_id', 'created_by', 'updated_by'
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class, 'employee_id', 'employee_id');
    }
}
