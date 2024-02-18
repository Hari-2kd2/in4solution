<?php

namespace App\Model;

use App\Traits\BranchTrait;
use App\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
  // use BranchTrait;
  use CrudTrait;
  protected $table = 'leave_balance';
  protected $primaryKey = 'leave_balance_id';

  protected $fillable = [
    'leave_balance_id', 'branch_id', 'employee_id', 'leave_type_id', 'leave_balance', 'status', 'year'
  ];

  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }



  public function leaveType()
  {
    return $this->belongsTo(LeaveType::class, 'leave_type_id');
  }
}
