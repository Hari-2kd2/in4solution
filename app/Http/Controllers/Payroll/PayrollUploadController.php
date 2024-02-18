<?php

namespace App\Http\Controllers\Payroll;

use App\User;
use App\Model\Role;
use App\Model\Employee;
use Carbon\CarbonPeriod;
use App\Components\Common;
use App\Model\PayrollUpload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Imports\PayrollImport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UploadPayrollReports;
use App\Lib\Enumerations\AppConstant;
use App\Exports\EmployeeDetailsExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Attendance\GenerateReportController;


class PayrollUploadController extends Controller
{

    public function index(Request $request)
    {
        $salary_month = $request->get('salary_month', null);
        $payroll_data = [];
        if($salary_month) {
            $payroll_data = PayrollUpload::where('salary_month', $salary_month)->orderBy('emp_code')->get();
        }
        $delete_salary = $request->get('delete_salary');

        if($delete_salary) {
            $checkFreeze = PayrollUpload::where('salary_month', $salary_month)->where('salary_freeze', 1)->first();
            if($checkFreeze) {
                return redirect()->route('upload.payroll_upload')->withInput()->with('error', 'Freezed salary month data can not be delete!');
            } else if(!$salary_month) {
                return redirect()->route('upload.payroll_upload')->withInput()->with('error', 'Please select the salary month to deleted.');
            }
            PayrollUpload::where('salary_month', $salary_month)->delete();
            return redirect()->route('upload.payroll_upload')->withInput()->with('info', $salary_month. ' deleted successfully.');
        }

        return view('admin.payroll.payrollUpload.index',['results' => $payroll_data, 'salary_month' => $salary_month]);
    }
    
    public function generateUploadStatement(Request $request) {
        $SalaryRepository = new \App\Repositories\SalaryRepository;
        $result = [];
        
        $salary_month = $request->salary_month;
        $salaryMonthList = $SalaryRepository->salaryMonths();
        if($request->post()) {
            $GenerateReportController = new GenerateReportController;
            if(!$salary_month) {
                return redirect()->back()->with('error', 'Salaray month is required.');
            }
            $checkFreeze = PayrollUpload::where('salary_key', $salary_month.'-01')->where('salary_freeze', 1)->count();

            if($checkFreeze) {
                return redirect()->route('payroll.generateUploadStatement')->withInput()->with('error', 'Freezed salary month data can not be done Generate Statement!');
            }

            $date = new \DateTime( $salary_month.'-01' );
            $fromDate = subtractMonth($date->format('Y-m-'.$SalaryRepository::PAYROLL_START_DATE), 1);
            $toDate = $date->format('Y-m-'.$SalaryRepository::PAYROLL_END_DATE);
            $dates['from_date'] = $fromDate;
            $dates['to_date'] = $toDate;
            $datePeriod = CarbonPeriod::create(dateConvertFormtoDB($dates['from_date']), dateConvertFormtoDB($dates['to_date']));
            // Employee::select('finger_id', 'employee_id')->where('employee_id', '>', 1)->whereIn('finger_id', ['T0208', 'T0212', 'T0349'])->orderBy('emp_code')->status(UserStatus::$ACTIVE)
            Employee::select('finger_id', 'employee_id')->where('employee_id', '>', 1)->orderBy('emp_code')->status(UserStatus::$ACTIVE)
                ->chunk(5, function ($employeeData) use ($datePeriod, $dates, $GenerateReportController) {
                    foreach ($employeeData as $key => $employee) {
                        $GenerateReportController->generatePayroll($dates, $employee);
                    }
                });
        }
        return view('admin.payroll.payrollUpload.generateUploadStatement', compact('salaryMonthList', 'salary_month'));
    }

    public function payrollTemplate()
    {
        $file_name = 'templates/upload_payroll.xlsx';
        $file = Storage::disk('public')->get($file_name);
        return (new Response($file, 200))->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }


    public function payrollUploadTemplate(Request $request)
    {
        $salary_month = $request->get('salary_month', null);
        $date = dateConvertFormtoDB($request->date);
        $inc = 1;
        $dataSet = [];
        $Data = [];
        $salaryMonthList = PayrollUpload::salaryMonthList();
        if($salary_month) {
            $Data = PayrollUpload::where('salary_month', $salary_month)->get();
        } else if(count($salaryMonthList)) {
            return redirect()->back()->withInput()->with('error', 'Please select "Salary Month" to download data');
        }

        // payroll_upload_id, salary_month, salary_freeze, emp_code, ctc, gross_salary, over_time, days_leaves, days_absents, lop, other_earnings, tds, salary_advance, labour_welfare, professional_tax, excess_telephone_usage, created_at, updated_at

        foreach ($Data as $key => $data) {
            $dataSet[] = [
                $inc,
                (string) $data->emp_code,
                (string) $data->other_earnings,
                (string) $data->tds,
                (string) $data->salary_advance,
                (string) $data->labour_welfare,
                (string) $data->professional_tax,
                (string) $data->excess_telephone_usage,
            ];

            $inc++;
        }

        $primaryHead = ['SL.NO', 'EMP CODE', 'OTHER EARNINGS', 'TDS','SALARY ADVANCE','LABOUR WELFARE','PROFESSIONAL TAX','EXCESS TELEPHONE USAGE'];
        $heading = [$primaryHead];

        $extraData['heading'] = $heading;
        $filename = 'Employee Payroll Data-' . DATE('d-m-Y His') . '.xlsx';
        $filename = 'Employee-Payroll-Data-Template.xlsx';
        return Excel::download(new UploadPayrollReports($dataSet, $extraData), $filename);

    }
    public function PayrollImport(FileUploadRequest $request)
    {
        try {
            // DB::statement('ALTER TABLE payroll_upload AUTO_INCREMENT='.(PayrollUpload::max('payroll_upload_id')+1));
            $salary_month = $request->post('salary_month');
            $date = dateConvertFormtoDB($request->date);
            $file = $request->file('select_file');
            $data = Excel::toArray(new PayrollImport, $file);


            if(isset($data[0])) { // sheet one
                $sheet = $data[0];                      // get sheet one by default
                $eMessage = [];

                // $salaryMonthList = PayrollUpload::salaryMonthList();
                // get email column by unique value to display an error message expect null or empty (means excel 9th column blank)
                // foreach ($salaryMonthList as $salary_month_one => $salary_month_label) {
                $ex = PayrollUpload::where('salary_month', $salary_month)->where('salary_freeze', 1)->first();
                if($ex) {
                    $eMessage[]=$salary_month;
                }

                if(count($eMessage)>0) {
                    $eMessage = 'Already freezed salary month found: ' . implode(', ', $eMessage);
                    return redirect()->back()->withInput()->with('error', $eMessage);
                }
            }

            Excel::import(new PayrollImport($request->all()), $file);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new PayrollImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }
        return back()->with('info', 'Payroll Data imported successfully.');
    }

    public function payrollview($id) {
        $ExcelEmployee = \App\Model\ExcelEmployee::FindOrFail($id);
        $view = 'admin.payroll.payrollUpload.payroll_view';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView($view, ['ExcelEmployee'=>$ExcelEmployee])->setOptions(['margin' => '0.25', 'charset' => 'UTF-8']); // ->setPaper('a4', 'landscape')
        return $pdf->stream();
    }

    public function payrollDownload($id) {
        $ExcelEmployee = \App\Model\ExcelEmployee::FindOrFail($id);
        $view = 'admin.payroll.payrollUpload.payroll_view';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView($view, ['ExcelEmployee'=>$ExcelEmployee])->setOptions(['margin' => '0.25', 'charset' => 'UTF-8']); // ->setPaper('a4', 'landscape')
        return $pdf->download();
    }

    public function destroy($id)
    {
        try {
            $payrollUpload = PayrollUpload::FindOrFail($id);
            $payrollUpload->delete();
            $bug = 0;
            return redirect(Route('upload.payroll_upload'));
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } else {
            echo 'error';
        }
    }
}
