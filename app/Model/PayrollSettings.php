<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PayrollSettings extends Model
{
    protected $table = 'payroll_settings';
    protected $primaryKey = 'payset_id';

    protected $fillable = ['basic','hra','da','conveyance_allowance','medical_allowance','children_allowance','lta','category_id'];

    protected $attributes = [
        'category_id' => 1,
        'basic' => 1,
        'hra' => 1,
        'da' => 1,
        'conveyance_allowance' => 1,
        'children_allowance' => 1,
        'medical_allowance' => 1,
        'lta' => 1,
        'created_at' => 1,
        'created_by' => 1,
        'updated_at' => 1,
        'updated_by' => 1,
        'working_days' => 1,
        'day_hour' => 1,
    ];

}
