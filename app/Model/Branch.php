<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    protected $table = 'branch';
    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_id', 'branch_name',
    ];

    // public function users()
    // {
        //     return $this->belongsToMany(User::class);
        // }
        


}
