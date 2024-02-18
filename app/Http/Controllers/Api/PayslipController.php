<?php

namespace App\Http\Controllers\Api;

use App\Model\User;
use App\Model\Employee;
use App\Model\SalaryDetails;
use Illuminate\Http\Request;
use App\Model\PrintHeadSetting;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Model\SalaryDetailsToLeave;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CommonRepository;
use App\Model\SalaryDetailsToAllowance;
use App\Model\SalaryDetailsToDeduction;
use Illuminate\Support\Facades\Validator;

class PayslipController extends Controller
{
    protected $commonRepository;
    protected $controller;

    public function __construct(Controller $controller, CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->controller  = $controller;
    }


    public function myPayroll(Request $request)
    {
        $employee_id = $request->employee_id;
        if($employee_id) {
            $employee = Employee::where('employee_id', $employee_id)->first();
        } else {
            return Controller::custom_error('Logged employee ID is required');
        }
        $Year_From = date('Y') - 2;
        $Year_To = date('Y');
        $salaryMonthList = [];
        $PayrollUploadList = \App\Model\PayrollUpload::select('payroll_upload_id', 'salary_key', 'salary_month', 'salary_freeze')->where('emp_code', $employee->emp_code)->where('salary_freeze', 1)->limit(36)->orderBy('created_at', 'DESC')->groupBy('salary_month')->get();
        $payroll=[];
        foreach ($PayrollUploadList as $key => $PayrollUpload) {
            $monthYear = date('F Y', strtotime($PayrollUpload->salary_key));
            // $monthYear = $PayrollUpload->salary_month;
            $tempPayoll = [
                'year' =>  date('Y', strtotime($PayrollUpload->salary_key)),
                'month' =>  date('m', strtotime($PayrollUpload->salary_key)),
                'display' =>  date('F Y', strtotime($PayrollUpload->salary_key)),
                'payroll_id' =>  $PayrollUpload->payroll_upload_id,
            ];
            $payroll[] = $tempPayoll;
        }
   
        return response()->json([
            'status' => true,
            'message' => 'My payroll month list detail received successfully.',
            'payroll' => $payroll,
        ], 200);


        // return $this->controller->success("My payroll month list detail received successfully.", $payoll);
    }

    public function payslip(Request $request)
    {

        $employee_id = $request->employee_id;

        $results = SalaryDetails::with(['employee' => function ($query) {
            $query->with(['department', 'payGrade', 'hourlySalaries']);
        }])->where('employee_id', $employee_id)->orderBy('salary_details_id', 'DESC')->get();

        if ($request->month_of_salary) {

            $results = SalaryDetails::with(['employee' => function ($query) {
                $query->with(['department', 'payGrade', 'hourlySalaries']);
            }])->where('employee_id', $request->employee_id)->orderBy('salary_details_id', 'DESC');

            if ($request->month_of_salary != '') {
                $results->where('status', 1)->where('month_of_salary', $request->month_of_salary);
            }

            $results = $results->get();

            if ($results != []) {
                return response()->json([
                    'message' => "My payslip details received successfully.",
                    'data' =>  $results,
                ], 200);
            }else{
                return response()->json([
                    'message' => "No records found.",
                    'data' =>  $results,
                ], 200);
            }
        }

        $departmentList = $this->commonRepository->departmentList();

        if ($results != [] && $departmentList != []) {
            return response()->json([
                'message' => "My payslip details received successfully.",
                'departmentList' =>  $departmentList,
                'data' =>  $results,
            ], 200);
        }else{
            return response()->json([
                'message' => "No records found.",
                'departmentList' =>  $departmentList,
                'data' =>  $results,
            ], 200);
        }

        
    }

    public function downloadMyPayroll(Request $request)
    {

        $employee_id = $request->employee_id;
        $printHeadSetting = PrintHeadSetting::first();

        $results          = SalaryDetails::with(['employee' => function ($query) {
            $query->with('payGrade');
        }])->where('status', 1)->where('employee_id', $employee_id)->orderBy('salary_details_id', 'DESC')->get();

        $data = [
            'results'   => $results,
            'printHead' => $printHeadSetting,
        ];

        $pdf = PDF::loadView('admin.payroll.report.pdf.myPayrollPdf', $data);

        $pdf->setPaper('A4', 'landscape');
        return $pdf->download("my-payroll-Pdf.pdf");
    }

    public function downloadPayslip(Request $request)
    {
        // this project payslip data from an excel file 
        $input = Validator::make($request->all(), [
            'id' => 'required|exists:payroll_upload,payroll_upload_id',
        ]);
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }

        $ExcelEmployee = \App\Model\ExcelEmployee::FindOrFail($request->id);

        $employee = $ExcelEmployee->Employee;
        if($employee->emp_code!=$ExcelEmployee->emp_code) {
            return $this->controller->custom_error("Invalid selection of pay slip month!");
        }
        $view = 'admin.payroll.payrollUpload.payroll_view';
        $pdf = \App::make('dompdf.wrapper');
        // return view($view, ['ExcelEmployee'=>$ExcelEmployee]);
        $pdf->loadView($view, ['ExcelEmployee'=>$ExcelEmployee])->setOptions(['margin' => '0.25', 'charset' => 'UTF-8']); // ->setPaper('a4', 'landscape')
        return $pdf->download();
       

    }

    public function paySlipDataFormat($month_of_salary, $employee_id)
    {
        $printHeadSetting = PrintHeadSetting::first();
        $data = [];

        $salaryDetails    = SalaryDetails::select('salary_details.*', 'employee.employee_id', 'employee.department_id', 'employee.designation_id', 'department.department_name', 'designation.designation_name', 'employee.first_name', 'employee.last_name', 'pay_grade.pay_grade_name', 'employee.date_of_joining')
            ->join('employee', 'employee.employee_id', 'salary_details.employee_id')
            ->join('department', 'department.department_id', 'employee.department_id')
            ->join('designation', 'designation.designation_id', 'employee.designation_id')
            ->join('pay_grade', 'pay_grade.pay_grade_id', 'employee.pay_grade_id')
            // ->where('salary_details_id', $id)
            ->where('salary_details.month_of_salary', (string)$month_of_salary)->where('salary_details.employee_id', $employee_id)
            ->first();

        if ($salaryDetails) {
            $salaryDetailsToAllowance = SalaryDetailsToAllowance::join('allowance', 'allowance.allowance_id', 'salary_details_to_allowance.allowance_id')
                ->where('salary_details_id', $salaryDetails['salary_details_id'])->get();

            $salaryDetailsToDeduction = SalaryDetailsToDeduction::join('deduction', 'deduction.deduction_id', 'salary_details_to_deduction.deduction_id')
                ->where('salary_details_id', $salaryDetails['salary_details_id'])->get();

            $salaryDetailsToLeave = SalaryDetailsToLeave::select('salary_details_to_leave.*', 'leave_type.leave_type_name')
                ->join('leave_type', 'leave_type.leave_type_id', 'salary_details_to_leave.leave_type_id')
                ->where('salary_details_id', $salaryDetails['salary_details_id'])->get();

            $monthAndYear = explode('-', $salaryDetails->month_of_salary);
            $start_year   = $monthAndYear[0] . '-01';
            $end_year     = $salaryDetails->month_of_salary;

            $financialYearTax = SalaryDetails::select(DB::raw('SUM(tax) as totalTax'))
                ->where('status', 1)
                ->where('employee_id', $salaryDetails->employee_id)
                ->whereBetween('month_of_salary', [$start_year, $end_year])
                ->first();

            $data = [
                'salaryDetails'            => $salaryDetails,
                'salaryDetailsToAllowance' => $salaryDetailsToAllowance,
                'salaryDetailsToDeduction' => $salaryDetailsToDeduction,
                'paySlipId'                => $salaryDetails['salary_details_id'],
                'financialYearTax'         => $financialYearTax,
                'salaryDetailsToLeave'     => $salaryDetailsToLeave,
                'printHeadSetting'         => $printHeadSetting,
            ];

            return $data;
        } else {
            return $data;
        }
    }
}
