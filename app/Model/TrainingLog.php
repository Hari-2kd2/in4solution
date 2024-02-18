<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class TrainingLog extends Model
{
    protected $table = 'training_logs';
    protected $primaryKey = 'training_log_id';

    protected $fillable = [
        'training_info_id', 'employee_id' , 'read_at'
    ];

    public function trainingInfo()
    {
        return $this->belongsTo(TrainingInfo::class, 'training_info_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
