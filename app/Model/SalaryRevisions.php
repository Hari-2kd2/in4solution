<?php

namespace App\Model;

use App\Model\DepartmentCase;
use App\Model\DesignationCase;
use Illuminate\Database\Eloquent\Model;

class SalaryRevisions extends Model
{

    protected $table = 'esic_salary_revision';
    protected $primaryKey = 'sal_id';
    protected $fillable = ['sal_employee_id', 'sal_reviside_on', 'sal_ctc', 'sal_gross', 'sal_department_id', 'sal_designation_id'];
    const CREATED_AT = 'sal_created_at';
    const UPDATED_AT = 'sal_updated_at';

    public function department()
    {
        return $this->belongsTo(DepartmentCase::class, 'sal_department_id');
    }

    public function designation()
    {
        return $this->belongsTo(DesignationCase::class, 'sal_designation_id');
    }

}
