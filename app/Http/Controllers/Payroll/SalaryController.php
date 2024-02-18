<?php

namespace App\Http\Controllers\Payroll;

use Carbon\Carbon;
use App\Model\MsSql;
use App\Model\Branch;
use App\Model\Payroll;
use App\Model\Employee;
use App\Model\Department;
use App\Components\Common;
use App\Model\Designation;
use App\Model\ExcelEmployee;
use Illuminate\Http\Request;
use App\Exports\SalaryReport;
use App\Model\PayrollStatement;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Repositories\SalaryRepository;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\AttendanceRepository;
use Itstructure\GridView\DataProviders\EloquentDataProvider;


class SalaryController extends Controller
{

    protected $SalaryRepository;
    protected $attendanceRepository;

    public function __construct(SalaryRepository $SalaryRepository, AttendanceRepository $attendanceRepository)
    {
        $this->SalaryRepository = $SalaryRepository;
        $this->attendanceRepository = $attendanceRepository;
    }


    public function dynamicReports(Request $request)
    {
        \set_time_limit(0);
        $salary_month = $request->get('salary_month', null);
        $freeze = $request->get('freeze', '');
        $Employee = new Employee(['salary_month' => $salary_month]);
        $ExcelEmployee = new ExcelEmployee();
        $Employee->branch_id = $branch_id = $request->get('branch_id', '');
        $Employee->salary_month = $salary_month;
        $activeBranch = Common::activeBranch();
        $role_id = session()->get('logged_session_data.role_id') == 1;
        $YEARS = request()->get('years', []);
        if ($YEARS) {
            $salary_key = dateConvertFormtoDB('01/' . $salary_month);
            // dd('salary_key', $salary_key);
            $ExcelEmployeeList = ExcelEmployee::whereYear('salary_key', $YEARS)->with('statmentEmployee')->orderBy('payroll_upload.emp_code')->get();
        } else {
            $ExcelEmployeeList = ExcelEmployee::where('salary_month', $salary_month)->with('statmentEmployee')->orderBy('payroll_upload.emp_code')->get();
        }
        return \view('admin.payroll.salary.dynamicReports', compact('ExcelEmployeeList', 'Employee', 'role_id'), ['SalaryRepository' => $this->SalaryRepository]);
    }

    public function statement(Request $request)
    {
        \set_time_limit(0);
        $employee_id = $request->employee_id;
        $salary_month = $request->get('salary_month', null);
        $freeze = $request->get('freeze', '');
        $Employee = new Employee(['salary_month' => $salary_month]);
        $ExcelEmployee = new ExcelEmployee();
        $Employee->branch_id = $branch_id = $request->get('branch_id', '');
        $Employee->salary_month = $salary_month;
        $activeBranch = Common::activeBranch();
        $role_id = session()->get('logged_session_data.role_id') == 1;

        if ($freeze && $salary_month) {
            $isAredayFreeze = ExcelEmployee::where('salary_month', $salary_month)->where('salary_freeze', 1)->first();
            if ($isAredayFreeze) {
                $message = $salary_month . ' month is alrady freezed.';
                return redirect()->route('salary.statement', ['salary_month' => $salary_month])->with('error', $message);
            } else {
                $payrollArray = ['salary_freeze' => 1];
                ExcelEmployee::where('salary_month', $salary_month)->where('salary_freeze', 0)->update($payrollArray);
                PayrollStatement::where('salary_month', dateConvertFormtoDB('01/' . $salary_month))->where('salary_freeze', 0)->update($payrollArray);
                $message = $salary_month . ' month is freeze successfully.';
                Session::flash('info', $message);
                return redirect()->route('salary.statement', ['salary_month' => $salary_month])->with('success', $message);
            }
        }
        $ExcelEmployeeList = ExcelEmployee::where('salary_month', $salary_month)->with('statmentEmployee')->orderBy('payroll_upload.emp_code')->get();

        return \view('admin.payroll.salary.statement_from_excel', compact('ExcelEmployeeList', 'Employee', 'role_id'), ['SalaryRepository' => $this->SalaryRepository]);
    }


    public function viewPayslip(Request $request, $id)
    {
    }

    public function index(Request $request)
    {
        \set_time_limit(0);

        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $activeBranch = Common::activeBranch();
        // $employeeList = Employee::where('generate_salary', 1)->where('branch_id', $activeBranch)->get();
        $employeeList = Employee::where('branch_id', $activeBranch)->get();
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id, $request->branch_id, $request->attendance_status);
        }
        return \view('admin.payroll.salary.index', compact('branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList'));
    }
    public function empindex(Request $request)
    {
        \set_time_limit(0);
        $employee  = Employee::where('employee_id', session('logged_session_data.employee_id'))->first();
        $departmentList = Department::where('department_id', $employee->department_id)->first();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $departmentList->department_id;
        $attendance_status = $request->attendance_status;
        if (session('logged_session_data.role_id') != 1) {
            $employeeList = Employee::where('status', 1)->where('employee_id', session('logged_session_data.employee_id'))->get();
        } else {
            $employeeList = Employee::where('status', 1)->get();
        }
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $departmentList->department_id, $request->branch_id, $request->attendance_status);
        }
        return \view('admin.payroll.salary.empindex', compact('branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList', 'employee'));
    }


    public function details(Request $request)
    {

        $qry = "1 ";
        if ($request->employee)
            $qry .= " AND employee=" . $request->employee;

        if ($request->branch)
            $qry .= " AND branch=" . $request->branch;

        if ($request->department)
            $qry .= " AND department=" . $request->department;

        if ($request->date) {
            $expl = explode("-", $request->date);
            $qry .= " AND ( month=" . $expl[1] . " AND year=" . $expl[0] . " ) ";
        }


        $data = Payroll::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC');
        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return
                    '<a href="' . route('payslip.generation', ['id' => $data->payroll_id]) . '" class="btn btn-xs btn-primary" title="Payslip" target="_blank" data-id="' . $data->payroll_id . '"><i class="fa fa-file-pdf-o"></i></a>';
            })

            ->editColumn('employee', function ($data) {
                $emp = Employee::find($data->employee);
                if ($emp) {
                    return $emp->first_name . " " . $emp->last_name;
                }
            })
            ->editColumn('department', function ($data) {
                $dept = Department::find($data->department);
                if ($dept) {
                    return $dept->department_name;
                }
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . DATE('Y');
                return DATE('M', strtotime($month));
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function empdetails(Request $request)
    {

        $qry = "1 ";

        if ($request->branch)
            $qry .= " AND branch=" . $request->branch;

        if ($request->department)
            $qry .= " AND department=" . $request->department;

        if ($request->date) {
            $expl = explode("-", $request->date);
            $qry .= " AND ( month=" . $expl[1] . " AND year=" . $expl[0] . " ) ";
        }


        $data = Payroll::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->where('employee', session('logged_session_data.employee_id'))->orderBy('created_at', 'DESC');
        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return
                    '<a href="' . route('payslip.generation', ['id' => $data->payroll_id]) . '" class="btn btn-xs btn-primary" title="Payslip" target="_blank" data-id="' . $data->payroll_id . '"><i class="fa fa-file-pdf-o"></i></a>';
            })

            ->editColumn('employee', function ($data) {
                $emp = Employee::find($data->employee);
                if ($emp) {
                    return $emp->first_name . " " . $emp->last_name;
                }
            })
            ->editColumn('department', function ($data) {
                $dept = Department::find($data->department);
                if ($dept) {
                    return $dept->department_name;
                }
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . DATE('Y');
                return DATE('M', strtotime($month));
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function list($value = '')
    {
        // code...
    }

    public function generation(Request $request)
    {
        $employeeList = Employee::where('status', 1)->get();
        return view('admin.payroll.salary.generation', ['employeeList' => $employeeList]);
    }

    public function differenceInHours($startdate, $enddate)
    {
        $starttimestamp = strtotime($startdate);
        $endtimestamp = strtotime($enddate);
        $difference = abs($endtimestamp - $starttimestamp) / 3600;
        return $difference;
    }

    public function sheet(Request $request)
    {
        $total_ot  = 0;
        $tempArray  = [];
        $employee = Employee::find($request->employee_id);
        $expl_month_year = explode("-", $request->month);
        $month = $request->month;
        $start_date =  $request->from_date;
        $end_date = $request->to_date;

        $attendance = DB::table('view_employee_in_out_data')->whereBetween('date', [$start_date, $end_date])->where('finger_print_id', $employee->finger_id)->orderBy('date')->get();

        // $leave = 
        return view('admin.payroll.salary.sheet', ['employee' => $employee, 'request' => $request]);
    }


    public function report(Request $request)
    {
        \set_time_limit(0);
        $dataProvider = new EloquentDataProvider(Payroll::query());

        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::get();
        $results = [];
        if ($_POST) {
            $results = $this->attendanceRepository->getEmployeeDailyAttendance($request->date, $request->department_id, $request->branch_id, $request->attendance_status);
        }
        return \view('admin.payroll.salary.report', compact('dataProvider', 'branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList'));
    }


    public function reportdetails(Request $request)
    {

        $qry = "1 ";
        if ($request->employee)
            $qry .= " AND employee=" . $request->employee;

        if ($request->branch)
            $qry .= " AND branch=" . $request->branch;

        if ($request->department)
            $qry .= " AND department=" . $request->department;

        if ($request->date)
            $qry .= " AND date=" . $request->date;


        $data = Payroll::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC');
        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return
                    '<a href="' . route('genration.index', ['id' => $data->payroll_id]) . '" class="btn btn-xs btn-primary" title="Payslip" target="_blank" data-id="' . $data->payroll_id . '"><i class="fa fa-file-pdf-o"></i></a>';
            })

            ->editColumn('employee', function ($data) {
                $emp = Employee::find($data->employee);
                if ($emp) {
                    return $emp->first_name . " " . $emp->last_name;
                }
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . DATE('Y');
                return DATE('M', strtotime($month));
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function download(Request $request)
    {
        $dataset = [];


        $qry = "1 ";
        if ($request->employee)
            $qry .= " AND employee=" . $request->employee;

        if ($request->branch)
            $qry .= " AND branch=" . $request->branch;

        if ($request->department)
            $qry .= " AND department=" . $request->department;

        if ($request->date)
            $qry .= " AND date=" . $request->date;

        $payroll = Payroll::whereRaw("('" . $qry . "')")->get();

        $inc = 1;
        $empName = "";
        foreach ($payroll as $key => $Data) {
            $empName = $Data->employeeinfo->first_name . " " . $Data->employeeinfo->last_name;
            $designation = Designation::find($Data->employeeinfo->designation_id);

            $dataset[] = [

                $inc,
                $Data->employeeinfo->first_name . " " . $Data->employeeinfo->last_name,
                $designation->designation_name,
                (!is_null($Data->employeeinfo->date_of_birth) && $Data->employeeinfo->date_of_birth != "0000-00-00") ? DATE('d-m-Y', strtotime($Data->employeeinfo->date_of_birth)) : "",
                (!is_null($Data->employeeinfo->date_of_joining) && $Data->employeeinfo->date_of_joining != "0000-00-00") ? DATE('d-m-Y', strtotime($Data->employeeinfo->date_of_joining)) : "",
                $Data->days_in_month,
                $Data->working_days,
                $Data->worked_days,
                $Data->absent,
                $Data->lop,
                $Data->month_salary,
                $Data->worked_salary,
                $Data->basic,
                $Data->da,
                $Data->hra,
                $Data->conveyance,
                $Data->medical,
                $Data->children,
                $Data->lta,
                $Data->special,
                $Data->other,
                $Data->lop_amount,
            ];

            $inc++;
        }

        $filename = 'salary-report-' . DATE('d-m-Y-h-i-A') . '.xlsx';
        $date = $request->monthyear . "-01";
        $extraData = ['subtitle2' => 'Salary Report', 'subtitle3' => 'Month / Year' . DATE('M-Y', strtotime($date)) . ' '];

        $heading = [
            [appName()],
            [$extraData['subtitle2']],
            [$extraData['subtitle3']],
            [
                'Sr.No.',
                'Name',
                'Designation',
                'DOB',
                'DOJ',
                'Day in Month',
                'Woking Days',
                'Woked Days',
                'Absent',
                'LOP Days',
                'Monthly Salary',
                'Worked Salary',
                'Basic',
                'DA',
                'HRA',
                'Conveyance',
                'Medical',
                'Children Education Allowance',
                'LTA',
                'Special',
                'Other Allowance ',
                'LOP ',
            ]
        ];
        $extraData['heading'] = $heading;
        return \Excel::download(new SalaryReport($dataset, $extraData), $filename);
    }


    public function store(Request $request)
    {
        $payroll = Payroll::where('employee', $request->employee)->where('month', $request->month)->where('year', $request->year)->first();

        if ($payroll) {
            $payroll->update($request->all());
            return redirect(route('salary.index'))->with('success', 'Salary generated successfully !.');
        } else {

            $insert_payroll = new Payroll;
            $insert_payroll->create($request->all());
            return redirect(route('salary.index'))->with('success', 'Salary generated successfully !.');
        }
    }
    public function payslipDataList(Request $request)
    {
        \set_time_limit(0);


        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $employeeList = Employee::where('generate_salary', 1)->get();
        $results = [];
        if ($request->employee_id && $request->date) {
            $expl = explode("-", $request->date);
            $payroll = Payroll::where('employee', $request->employee_id)->where('month', $expl[1])->where('year', $expl[0])->orderBy('created_at', 'DESC')->get();
        } elseif ($request->employee_id) {
            $payroll = Payroll::where('employee', $request->employee_id)->where('month', $expl[1])->where('year', $expl[0])->orderBy('created_at', 'DESC')->get();
        } elseif ($request->date) {
            $expl = explode("-", $request->date);
            $payroll = Payroll::where('month', $expl[1])->where('year', $expl[0])->orderBy('created_at', 'DESC')->get();
        } else {
            $payroll = Payroll::where('month', date("m"))->where('year', date("Y"))->orderBy('created_at', 'DESC')->get();
        }

        return \view('admin.payroll.salary.payslipdatalist', compact('branchList', 'departmentList', 'date', 'employeeList', 'payroll'));
    }
}
