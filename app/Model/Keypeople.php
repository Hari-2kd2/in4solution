<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CrudTrait;
use App\Traits\BranchTrait;

class Keypeople extends Model
{
  // use BranchTrait;
  use CrudTrait;

  protected $table = 'keypeople_setting';
  protected $primaryKey = 'key_id';

  protected $fillable = [
    'key_user_ids', 'key_director_emails', 'key_hr_emails', 'branch_id'
  ];

    
}
