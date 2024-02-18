<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DepartmentCase extends Model
{

    protected $table = 'department';
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_id', 'branch_id', 'department_name'
    ];


}
