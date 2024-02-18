<?php

namespace App\Model;

use App\Model\Employee;
use App\Model\SalaryRevisions;

class EmployeeObserver
{
    /**
     * Handle the employee "created" event.
     *
     * @param  \App\Employee  $employee
     * @return void
     */
    public function created(Employee $employee)
    {
        //
    }

    /**
     * Handle the employee "updated" event.
     *
     * @param  \App\Employee  $employee
     * @return void
     */
    public function updated(Employee $employee)
    {
        if($employee->salary_revision) {
            $SalaryRevisions = SalaryRevisions::where(['sal_employee_id' => $employee->employee_id, 'sal_reviside_on' => $employee->salary_revision])->first();
            $data['sal_ctc'] = $employee->salary_ctc;
            $data['sal_gross'] = $employee->salary_gross;
            $data['sal_department_id'] = $employee->department_id;
            $data['sal_designation_id'] = $employee->designation_id;
            if($SalaryRevisions) {
                $SalaryRevisions->update($data);
            } else {
                $data['sal_employee_id'] = $employee->employee_id;
                $data['sal_reviside_on'] = $employee->salary_revision;
                SalaryRevisions::create($data);
            }
        }
        if($employee->status!=$employee->getOriginal('status')) {
            $employee->userName->status = $employee->status;
            $employee->userName->update();
        }
    }

    /**
     * Handle the employee "deleted" event.
     *
     * @param  \App\Employee  $employee
     * @return void
     */
    public function deleted(Employee $employee)
    {
        //
    }

    /**
     * Handle the employee "restored" event.
     *
     * @param  \App\Employee  $employee
     * @return void
     */
    public function restored(Employee $employee)
    {
        //
    }

    /**
     * Handle the employee "force deleted" event.
     *
     * @param  \App\Employee  $employee
     * @return void
     */
    public function forceDeleted(Employee $employee)
    {
        //
    }
}
