<?php

namespace App\Http\Controllers\Attendance;

use App\Model\Branch;
use App\Model\Employee;
use App\Components\Common;
use App\Model\calanderYear;
use App\Model\ExcelEmployee;
use Illuminate\Http\Request;
use App\Model\ApproveOverTime;
use App\Model\EmployeeInOutData;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Lib\Enumerations\AppConstant;
use App\Exports\ApproveOvertimeReport;
use App\Repositories\CommonRepository;
use App\Repositories\SalaryRepository;
use App\Imports\ApprovedOvertimeImport;
use App\Http\Requests\ApproveOverTimeRequest;

class FilterController extends Controller{

	 protected $SalaryRepository;

    public function __construct(SalaryRepository $SalaryRepository)
    {
        $this->SalaryRepository = $SalaryRepository;
    }


	public function index(){
		return view('admin.attendance.report.filter');
	}

	public function monthyear(Request $request){
		
		echo '<div class="form-check">
			<input type="checkbox" class="form-check-input" id="exampleCheck1">
			<label class="form-check-label" for="exampleCheck1">MAR - 2023 </label>
		</div>';
	}

	public function monthlist(Request $request){

		$expl=explode("-",$request->year);
		$calanderYear = calanderYear::find($request->year);
		$set='';
		if(!$calanderYear) {
			return '';
		}
		$year = new \DateTime( $calanderYear->year_start );
		$to = new \DateTime( $calanderYear->year_end );
        $year = $year->format('Y');
        $to = $to->format('Y');

		// dd($year.',to='.$to);
		$listMonth = \App\Model\PayrollUpload::calancerMonthList($year, $to);

		foreach($listMonth as $key=>$Data){
			$set.='<div class="form-check">
				<input type="radio" name="month_year" class="form-check-input" id="'.$key.'" value="'.$key.'">
				<label class="form-check-label" for="'.$key.'">'.$Data.'</label>
			</div>';
		}
		echo $set;
	}
	public function results(Request $request){

		$employee_id = $request->employee_id;
        $salary_month = $request->get('month_year', null);
		// $format_salary_month = [];
		// foreach ($salary_month as $key => $date) {
			// $parts = explode('/', $salary_month);
			// $date = $parts[1].'-'.$parts[0].'-01';
			// $format_salary_month = $date;
		// }
		// dd($format_salary_month);
		// dd($salary_month);
        $Employee = new Employee(['salary_month' => $salary_month]);
        $ExcelEmployee = new ExcelEmployee();
        $Employee->branch_id = $branch_id = $request->get('branch_id', '');
        $Employee->salary_month = $salary_month;
        $activeBranch = Common::activeBranch();
        $role_id = session()->get('logged_session_data.role_id') == 1;

		if($branch_id) {
            $employeeList = ExcelEmployee::where('salary_month', $salary_month)
            ->leftJoin('employee', function($leftJoin) use ($branch_id) {
                $leftJoin->on('employee.emp_code', '=', 'payroll_upload.emp_code')->where('employee.branch_id', $branch_id);
            } )->with('statmentEmployee')->orderBy('payroll_upload.emp_code')->get();
        } else {
            $employeeList = ExcelEmployee::where('salary_month', $salary_month)
            ->leftJoin('employee', function($leftJoin) use ($branch_id) {
                $leftJoin->on('employee.emp_code', '=', 'payroll_upload.emp_code');
            } )->with('statmentEmployee')->orderBy('payroll_upload.emp_code')->get();
			// dd(count($employeeList));
        }


		$filterset=$request->get('filterset', []);
		return view('admin.attendance.report.results', compact('employeeList', 'Employee', 'role_id','filterset','salary_month'), ['SalaryRepository' => $this->SalaryRepository]);
		//dd($request->all());
	}
}
