<?php

namespace App\Model;

use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    // use BranchTrait;
    use CrudTrait;

    protected $table = 'work_shift';
    protected $primaryKey = 'work_shift_id';

    protected $fillable = [
        'work_shift_id', 'branch_id', 'shift_name', 'start_time', 'end_time', 'late_count_time',
    ];

    public function shiftDetail() {
        if($this->work_shift_id) {
            $shilfDetail = $this->shift_name;
            $shilfDetail .= PHP_EOL.'(Start: '.$this->start_time .' - End: ' . $this->end_time . ') ';
            $shilfDetail .= PHP_EOL.'Late Allow: '.$this->late_count_time;
            return $shilfDetail;
        }
    }

}
