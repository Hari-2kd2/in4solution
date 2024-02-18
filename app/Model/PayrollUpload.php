<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PayrollUpload extends Model
{
    protected $table = 'payroll_upload';
    protected $primaryKey = 'payroll_upload_id';

    protected $fillable = ['payroll_upload_id', 'salary_month', 'emp_code', 'ctc', 'gross_salary', 'over_time', 'days_leaves', 'days_absents', 'other_earnings', 'tds', 'salary_advance', 'labour_welfare', 'professional_tax', 'excess_telephone_usage'];

    public static function salaryEmployeeMonthList($emp_code) {
        $Year_From = date('Y') - 2;
        $Year_To = date('Y');
        $salaryMonthList = [];
        $PayrollUploadList = self::select('salary_month', 'salary_freeze')->where('emp_code', $emp_code)->where('salary_freeze', 1)->limit(36)->orderBy('created_at', 'DESC')->groupBy('salary_month')->get();
        foreach ($PayrollUploadList as $key => $PayrollUpload) {
            // $monthYear = date('F Y', strtotime($PayrollUpload->salary_month));
            $monthYear = $PayrollUpload->salary_month;
            $salaryMonthList[$PayrollUpload->salary_month] = $monthYear . ($PayrollUpload->salary_freeze==1 ? ' - Freezed' : '');
        }
        return $salaryMonthList;
    }

    public static function salaryMonthList() {
        $Year_From = date('Y') - 2;
        $Year_To = date('Y');
        $salaryMonthList = [];
        $PayrollUploadList = self::select('salary_month', 'salary_freeze')->limit(36)->orderBy('created_at', 'DESC')->groupBy('salary_month')->get();
        foreach ($PayrollUploadList as $key => $PayrollUpload) {
            // $monthYear = date('F Y', strtotime($PayrollUpload->salary_month));
            $monthYear = $PayrollUpload->salary_month;
            $salaryMonthList[$PayrollUpload->salary_month] = $monthYear . ($PayrollUpload->salary_freeze==1 ? ' - Freezed' : '');
        }
        return $salaryMonthList;
    }

    public static function calancerMonthList($year, $to='') {
        $salaryMonthList = [];
        if($year && $to) {
            $PayrollUploadList = self::select('salary_month', 'salary_freeze')->whereRaw('salary_month LIKE "%'.$year.'%" OR salary_month LIKE "%'.$to.'%"')->orderBy('created_at', 'DESC')->groupBy('salary_month')->get();
        } else {
            $PayrollUploadList = self::select('salary_month', 'salary_freeze')->whereRaw('salary_month LIKE "%'.$year.'%"')->orderBy('created_at', 'DESC')->groupBy('salary_month')->get();
        }
        foreach ($PayrollUploadList as $key => $PayrollUpload) {
            // $monthYear = date('F Y', strtotime($PayrollUpload->salary_month));
            $monthYear = $PayrollUpload->salary_month;
            $salaryMonthList[$PayrollUpload->salary_month] = $monthYear . ($PayrollUpload->salary_freeze==1 ? ' - Freezed' : '');
        }
        return $salaryMonthList;
    }
}
