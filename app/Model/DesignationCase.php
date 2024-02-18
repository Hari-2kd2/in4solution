<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DesignationCase extends Model
{
    protected $table = 'designation';
    protected $primaryKey = 'designation_id';

    protected $fillable = [
        'designation_id', 'branch_id', 'designation_name'
    ];
}
