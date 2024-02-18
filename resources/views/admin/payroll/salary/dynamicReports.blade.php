@php
    use Carbon\Carbon;
    global $dateFunctions, $relationFunctions, $otherFunctions, $traceData;
    $dateFunctions = keyFields('dateFunctions');
    $relationFunctions = keyFields('relationFunctions');
    $otherFunctions = keyFields('otherFunctions');
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('menu.dynamic_reports')
@endsection
<style>
    /* Filter UI Styles */
    .form-check label{font-weight: normal;}
	.inner-section .panel-heading {background-color: transparent !important;padding: 8px 13px !important;}
	.inner-section .panel-body {height: 250px;overflow: auto;padding:10px;}
    /* Filter UI Styles */
	.salary-statment-table thead > tr > th {
		white-space: nowrap;
	}

    .employeeName {
        position: relative;
    }

    #employee_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
        width: 100%;
        height: 100%;
    }
    table.dataTable tbody tr.highlight {
        background: #ccc !important;
    }
    .semi-salary td{
        color: rgb(248, 53, 151) !important; font-weight: bold;
    }
    td.leaving-salary{
        color: rgb(5, 223, 194) !important; font-weight: bold;
    }
    .above-60 td{
        color: rgb(248, 53, 53) !important; font-weight: bold;
    }
    .esi-revision {
        color: rgba(223, 8, 252, 0.726) !important; font-weight: bold;
    }
    .red-color {
        color:red !important; font-weight: bold;
    }
    .green-color {
        color:green !important; font-weight: bold;
    }
    .blue-color {
        color:darkblue !important; font-weight: bold;
    }
    .yellow-color {
        color:rgb(212, 212, 0) !important; font-weight: bold;
    }
    label[for="checkall1"], label[for="checkall2"], label[for="checkall3"], label[for="checkall4"], label[for="checkall5"], label[for="checkall6"], label[for="checkall7"] {
        cursor: pointer;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor">
                    <a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                    @lang('dashboard.dashboard')</a>
                </li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>
    <hr>
    <div class="row">
        @php
            $request = request();
            $BRANCHS = request()->get('branch_id', []);
            $filterset = request()->get('filterset', []);
            $branches = request()->get('branches', []);
            $YEARS = request()->get('years', []);
            $users = request()->get('users', []);
            // dd($YEARS);
            $select_month_form = $salary_month = request()->get('salary_month', '');
            $getFlag = count($_GET);
            $displayClass =  $getFlag ? '' : ' hide ';
            $listMonth = \App\Model\PayrollUpload::salaryMonthList();
            $branchList = branchList();
            unset($branchList['']);
            $listNull[null] = 'Select Salary Month';
            $listMonth = \App\Model\PayrollUpload::salaryMonthList();
            $listMonth = array_merge($listNull, $listMonth);
            $freeze = request()->get('freeze', '');
            $select_month = request()->get('salary_month', date('m/Y'));
            $select_month = dateConvertFormtoDB(('01/'.$select_month));
            $date = new DateTime( monthConvertFormtoDB($select_month) );
            $select_month_name = $date->format('F');
            $select_month = $date->format('Y-m');
            $Total_Work_Days = count(findMonthToAllDate($select_month));
            $dates = findMonthToAllDate($select_month);
            
            $header=keyFields('header');
            $defaults=keyFields('defaults');
            $classification=keyFields('classifications');
            $payroll=keyFields('payroll');
            $additionals=keyFields('additionals');
            $attendance=keyFields('attendance');
            $leaves=keyFields('leaves');
            
            $filterset = array_merge($defaults, $filterset);
            
            if (env('APP_URL')=='http://localhost/in4solution') {
                // $Employee = \App\Model\Employee::where('employee_id', 2)->with('department')->first();
                // echo $Employee->department->department_name;
                // echo '<pre>'.count($_GET['emps']).'</pre>';
                // echo '<pre>'.print_r($request->branch_id,1).'</pre>';
                // echo '<pre>'.print_r($filterset,1).'</pre>';
                // echo '<pre>'.print_r($users,1).'</pre>';
            }
            // dd($_GET);
            // dd($header);
        @endphp

        {{ Form::open([
            'route' => 'salary.dynamicReports',
            'id' => 'dynamic-reports-form',
            'class' => '',
            'method'=>'GET'
        ]) }}
        
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                    <div class="panel-wrapper collapse in" aria-expanded="true">
                        <div class="row" style="margin: 10px 0 0 0">
                            <div class="col-md-8">
                                <div class="form-group"><br>
                                    <button type="button" class="btn btn-primary" id="inner-section-btn">Key Fields</button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <span><i class="fa fa-calendar"></i></span>
                                    <span for="salary_month" title="{{ request()->salary_month ? ($select_month_name . ' = ' . $Total_Work_Days) : '' }}">Salary Month</span>
                                    {!! Form::select('salary_month', $listMonth, $select_month_form, [
                                        'class' => 'form-control salary_month',
                                        'id' => 'salary_month',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        @php
                        $emp=\App\Model\Employee::where('status', App\Lib\Enumerations\UserStatus::$ACTIVE)->orderBy('emp_code','ASC')->get();
                        if($users) {
                            $emp->whereIn('employee_id', $users);
                        }
                        if($branches) {
                            $emp->whereIn('branch_id', $branches);
                        }
                        @endphp
                        <div class="panel-body inner-section" style="display: none">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall1 ? ' checked ' : '' }} name="checkall1" data-class="checkall1" id="checkall1" value="1"> <label for="checkall1">Employee</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall1">
                                                @php $emp=\App\Model\Employee::where('status',1)->orderBy('first_name','ASC')->get(); @endphp
                                                @foreach($emp as $ekey => $Data)
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" {{ in_array($Data->employee_id, $users) ? ' checked ' : '' }} name="users[]" id="emps-{{$Data->employee_id}}" value="{{$Data->employee_id}}">
                                                    <label class="form-check-label" for="emps-{{$Data->employee_id}}">{{$Data->detail_name()}}</label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall2 ? ' checked ' : '' }} name="checkall2" data-class="checkall2" id="checkall2" value="1"> <label for="checkall2">@lang('common.branch')</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall2">
                                                @foreach($branchList as $bkey=>$branchListData)
                                                    <div class="form-check">
                                                        <input type="checkbox" {{ in_array($bkey, $branches) ? ' checked ' : '' }} name="branches[]" class="form-check-input" id="branch-{{$bkey}}" value="{{$bkey}}">
                                                        <label class="form-check-label" for="branch-{{$bkey}}">{{$branchListData}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall3 ? ' checked ' : '' }} name="checkall3" data-class="checkall3" id="checkall3" value="1"> <label for="checkall3">Calendar Years</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall3">
                                                @php
                                                $calanderYearList = \App\Model\calanderYear::calanderYearList();
                                                $calendars=[];
                                                foreach ($calanderYearList as $key => $yearOne) {
                                                    $yearNum = date('Y', strtotime($yearOne['year_start']));
                                                    $calendars[$yearNum] = $yearOne['year_name'];
                                                    
                                                }
                                                @endphp
                                                @foreach($calendars as $calkey=>$calendarsData)
                                                    <div class="form-check">
                                                        <input type="checkbox" {{ in_array($calkey, $YEARS) ? ' checked ' : '' }} name="years[]" class="form-check-input" id="cal-{{$calkey}}" value="{{$calkey}}">
                                                        <label class="form-check-label" for="cal-{{$calkey}}">{{$calendarsData}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall4 ? ' checked ' : '' }} name="checkall4" data-class="checkall4" id="checkall4" value="1"> <label for="checkall4">Payroll</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall4">
                                                @php
                                                $payroll=keyFields('payroll');
                                                @endphp
                                                @foreach($payroll as $pkey=>$payrollData)
                                                    <div class="form-check">
                                                        <input type="checkbox" {{ in_array($pkey, $filterset) ? ' checked ' : '' }} name="filterset[]" class="form-check-input" id="pay-{{$pkey}}" value="{{$pkey}}">
                                                        <label class="form-check-label" for="pay-{{$pkey}}">{{$payrollData}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall5 ? ' checked ' : '' }} name="checkall5" data-class="checkall5" id="checkall5" value="1"> <label for="checkall5">Classification Details</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall5">
                                                @php
                                                $classification=keyFields('classifications');
                                                @endphp
                                                @foreach($classification as $ckey=>$classData)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="filterset[]" {{ in_array($ckey, $filterset) ? ' checked ' : '' }} class="form-check-input" id="classification-{{$ckey}}" value="{{$ckey}}">
                                                        <label class="form-check-label" for="classification-{{$ckey}}">{{$classData}}</label>
                                                    </div>
                                                @endforeach
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall6 ? ' checked ' : '' }} name="checkall6" data-class="checkall6" id="checkall6" value="1"> <label for="checkall6">Additional Information</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall6">
                                                @php
                                                $additional=keyFields('additionals');
                                                @endphp
                                                @foreach($additional as $akey=>$addData)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="filterset[]" {{ in_array($akey, $filterset) ? ' checked ' : '' }} class="form-check-input" id="additional-{{$akey}}" value="{{$akey}}">
                                                        <label class="form-check-label" for="additional-{{$akey}}">{{$addData}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="panel panel-info" style="border:0.1px solid lightgrey;">
                                        <div class="panel-heading"><input type="checkbox" class="form-check-input check-all" {{ $request->checkall7 ? ' checked ' : '' }} name="checkall7" data-class="checkall7" id="checkall7" value="1"> <label for="checkall7">Leave Information</label></div>
                                        <div class="panel-wrapper collapse in" aria-expanded="true">
                                            <div class="panel-body checkall7">
                                                @php
                                                $information=keyFields('attendance');
                                                @endphp
                                                @foreach($information as $lkey=>$infoData)
                                                    <div class="form-check">
                                                        <input type="checkbox" name="filterset[]" {{ in_array($lkey, $filterset) ? ' checked ' : '' }} class="form-check-input" id="leave-{{$lkey}}" value="{{$lkey}}">
                                                        <label class="form-check-label" for="leave-{{$lkey}}">{{$infoData}}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i> Go</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                        </div>
                    </div>
                </div>
                
                    
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('info'))
                            <div class="alert alert-info alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>{{ session()->get('info') }}</strong>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div class="panel panel-info">
                            <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@lang('menu.dynamic_reports') Results</div>
                            <div class="panel-body">
                                {{-- <div id="searchBox">
                                    <div class="row">
                                        <div class="col-md-8">
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <span><i class="fa fa-calendar"></i></span>
                                                <span for="salary_month" title="{{ request()->salary_month ? ($select_month_name . ' = ' . $Total_Work_Days) : '' }}">Salary Month</span>
                                                {!! Form::select('salary_month', $listMonth, $select_month_form, [
                                                    'class' => 'form-control salary_month',
                                                    'id' => 'salary_month',
                                                ]) !!}
                                            </div>

                                        </div>
                                        <div class="col-md-1" style="display: none">
                                            <div class="form-group">
                                                <span class="text-white"><br></span>
                                                <input type="submit" id="filter" class="btn btn-info " title="{{ $select_month_name . ' = ' . $Total_Work_Days }}" value="Salary Statement">
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}

                                <div class="table-responsive {{ $displayClass }}" id="bulkexportarea">
                                    <table id="salary" class="table table-bordered salary-statment-table" style="font-size: 12px">
                                        <thead class="tr_header">
                                            <tr>
                                                <th>S. No</th>
                                                @foreach ($header as $key => $Data)
                                                    @if (in_array($key, $filterset))
                                                        <th>{{ $Data }}</th>
                                                    @endif
                                                @endforeach
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $SNO = 0;
                                                $gLog = request()->get('t', 'all');
                                            @endphp
                                            @foreach ($ExcelEmployeeList as $empIndex => $ExcelEmployee)
                                                @php
                                                    $TODAY              = date('Y-m-d');
                                                    $SALARY_MONTH       = dateConvertFormtoDB('01/'.$ExcelEmployee->salary_month);
                                                    $EsiClass = '';
                                                    $OvertimeClass = '';
                                                    $Esi21Class = '';
                                                    $ProbationClass = '';
                                                    $MidJoinReleveDays = 0;
                                                    
                                                    $employee      = $ExcelEmployee->statmentEmployee;
                                                    if(!$employee || !isset($ExcelEmployee->ctc)) {
                                                        info('No employee data or ctc is invalid for '.$ExcelEmployee->emp_code);
                                                        continue;
                                                    }
                                                    if(count($branches) && !in_array($employee->branch_id, $branches)) {
                                                        continue;
                                                    }
                                                    if(count($users) && !in_array($employee->employee_id, $users)) {
                                                        continue;
                                                    }

                                                    $PayrollStatement = \App\Model\PayrollStatement::where('payroll_upload_id', $ExcelEmployee->payroll_upload_id)->first();
                                                    if(!$PayrollStatement) {
                                                        $PayrollStatement = new \App\Model\PayrollStatement;
                                                        $PayrollStatement->payroll_upload_id = $ExcelEmployee->payroll_upload_id;
                                                    }
                                                    $SNO++;
                                                    $PayrollStatementData = $PayrollStatement->getAttributes();
                                                    $ExcelEmployeeData = $ExcelEmployee->getAttributes();
                                                    $employeeData = $employee->getAttributes();
                                                    $userData = $employee->userName->getAttributes();
                                                    $rowData = array_merge($employeeData, $userData, $ExcelEmployeeData, $PayrollStatementData);
                                                    $rowData['Employee'] = $employee;
                                                    $rowData['PayrollStatement'] = $PayrollStatement;
                                                    $rowData['ExcelEmployee'] = $ExcelEmployee;
                                                    $rowData['pay_slip'] = '<a href="'.Route('upload.payrollview', ['id' => $ExcelEmployee->payroll_upload_id, 'select_month_form'=>$select_month_form]).'" target="blank" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> Payslip</a>';

                                                    $CTC                = $ExcelEmployee->ctc;
                                                    $Gross              = $ExcelEmployee->gross_salary;
                                                    $EarnedCTC          = $CTC;
                                                    $EarnedGross        = $Gross;
                                                    $Basic              = $ExcelEmployee->Basic;

                                                    $Over_Time          = $ExcelEmployee->over_time;
                                                    
                                                    $HRA                        = $ExcelEmployee->HRA;
                                                    $LTA                        = $ExcelEmployee->LTA;
                                                    $Bonus                      = $ExcelEmployee->Bonus;

                                                    $ESI_TOTAL                  = $ExcelEmployee->ESI_TOTAL;
                                                    $ESI_Employee_Total         = $ExcelEmployee->ESI_Employee_Total;
                                                    $ESI_Employer_Total         = $ExcelEmployee->ESI_Employer_Total;
                                                    $PF_TOTAL                   = $ExcelEmployee->PF_TOTAL;
                                                    $PF_Employer                = $ExcelEmployee->PF_Employer;
                                                    $ESI_Employer               = $ExcelEmployee->ESI_Employer;
                                                    $PF_Employee                = $ExcelEmployee->PF_Employee;
                                                    $ESI_Employee               = $ExcelEmployee->ESI_Employee;
                                                    
                                                    
                                                    $days_leaves                = $ExcelEmployee->days_leaves;
                                                    $days_absents               = $ExcelEmployee->days_absents;
                                                    $days_presents              = $ExcelEmployee->days_presents;
                                                    $days_holidays              = $ExcelEmployee->days_holidays;
                                                    $Other_earnings             = $ExcelEmployee->other_earnings;
                                                    $TDS                        = $ExcelEmployee->tds;
                                                    $Salary_Advance             = $ExcelEmployee->salary_advance;
                                                    $Labour_Welfare             = $ExcelEmployee->labour_welfare;
                                                    $Professional_Tax           = $ExcelEmployee->professional_tax;
                                                    $Excess_Telephoone_Usage    = $ExcelEmployee->excess_telephone_usage;
                                                    $OT_PER_HOUR                = $ExcelEmployee->OT_PER_HOUR;
                                                    $OT_ESI_Employer            = $ExcelEmployee->OT_ESI_Employer;
                                                    $OT_ESI_Employee            = $ExcelEmployee->OT_ESI_Employee;
                                                    $Special_Allowance          = $ExcelEmployee->Special_Allowance;
                                                    $Nett_Gross                 = $ExcelEmployee->Nett_Gross;
                                                    $Total_Deduction            = $ExcelEmployee->Total_Deduction;
                                                    // $Net_Salary_Abs             = $Nett_Gross - $Total_Deduction;
                                                    // $Net_Salary                 = round($Net_Salary_Abs);
                                                    // $Net_SalaryRou              = round($Net_Salary_Abs);
                                                    $ESI_Employee_Total         = $ExcelEmployee->ESI_Employee_Total;
                                                    $ESI_Employer_Total         = $ExcelEmployee->ESI_Employer_Total;
                                                    $ESI_TOTAL                  = $ExcelEmployee->ESI_TOTAL;
                                                    $PF_TOTAL                   = $ExcelEmployee->PF_TOTAL;
                                                    $vars = ['employee_id','finger_print_id','emp_code','fullname','SALARY_MONTH','salary_freeze','date_of_birth','date_of_joining','LOP','Basic','HRA','LTA','Special_Allowance','EarnedGross','Other_earnings','OT_PER_HOUR','Over_Time','OT_ESI_Employer','OT_ESI_Employee','ESI_Employee_Total','ESI_Employer_Total','ESI_TOTAL','PF_TOTAL','Nett_Gross','EarnedCTC','PF_Employee','ESI_Employee','TDS','Salary_Advance','Excess_Telephoone_Usage','Labour_Welfare','Professional_Tax','ESI_Employer','PF_Employer','Bonus','Total_Deduction','Net_Salary', 'days_leaves', 'days_absents', 'days_presents', 'days_holidays', 'days_weekoffs', 'payroll_upload_id'];
                                                    
                                                    $employee_id = $employee->employee_id;
                                                    $emp_code = $employee->emp_code;
                                                    $finger_print_id = $employee->finger_id;
                                                    $fullname = $employee->fullname();
                                                    $salary_freeze = $ExcelEmployee->salary_freeze;
                                                    $t=1;
                                                    foreach ($vars as $key => $field) {
                                                        if(isset($$field) && $PayrollStatement->salary_freeze==0) {
                                                            $PayrollStatement->$field = $$field;
                                                            if($MidJoinReleveDays>0 && $field=='fullname') {
                                                                $PayrollStatement->$field = $MidJoinReleveDays;
                                                            }
                                                            $t++;
                                                        }
                                                    }
                                                    
                                                    
                                                    $t=1;
                                                    foreach ($vars as $key => $field) {
                                                        if(isset($$field)) {
                                                            $$field = $PayrollStatement->$field;
                                                            $t++;
                                                        }
                                                    }
                                                
                                                    $rowData['Present'] = ($Total_Work_Days - $ExcelEmployee->days_absents);
                                                    $rowData['Absent'] = $ExcelEmployee->days_absents;
                                                    $rowData['Total_Work_Days'] = $Total_Work_Days;
                                                @endphp
                                                <tr class="">
                                                    <td class="" title="">
                                                        {{ $SNO }}
                                                    </td>
                                                    @foreach ($header as $key => $Data)
                                                        @if (in_array($key, $filterset))
                                                            <td>{!! displayData($key, $rowData) !!}</td>
                                                        @endif
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>
@php
function displayData($key, $Data) {
	// global $header, $classification, $payroll, $additionals, $attendance, $leaves;
    // $timeFormat = sprintf('%02d:%02d', (int) $ot_hours, fmod($ot_hours, 1) * 60);

	global $dateFunctions, $relationFunctions, $otherFunctions, $traceData;
    $traceData = $Data;
    if(isset($Data[$key])) {
        // info('$key='.$key);
        if($key=='role_id') {
            return $Data['Employee']->rolename();
        } else if($key=='supervisor_id') {
            return $Data['Employee']->supervisorDetail();
        } else if($key=='overtime_status') {
            return $Data[$key] ? 'Applicable' : 'Not Applicable';
        } else if($key=='permanent_status') {
            return $Data[$key]==1?'Permanent':'Probation';
        } else if($key=='salary_month') {
            return request()->get('salary_month');
        }
        
        if(isset($dateFunctions[$key])) {
            return dateConvertDBtoForm($Data[$key]);
        } else if(isset($relationFunctions[$key])) {
            $object = $relationFunctions[$key]['object'] ?? '';
            $relation = $relationFunctions[$key]['relation'] ?? '';
            $field = $relationFunctions[$key]['field'] ?? '';
            $object = isset($Data[$object]) ? $Data[$object] : '';
            return $object->$relation->$field ?? '-';
        } else if(isset($otherFunctions[$key])) {
            $function = $otherFunctions[$key];
            return $function($Data[$key]);
        }
        return $Data[$key] ?? '-';
    }
    // else if(isset($Data['PayrollStatement']->$key)) {
    //     return $key;
    // }
}


@endphp
@endsection
@section('page_scripts')
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', 'table.dataTable tbody tr',function(e) {
        $(this).toggleClass('highlight');
    });
    $(document).on('click', '.check-all',function(e) {
        let classs = $(this).data('class');
        console.log('.'+classs+' input:checkbox');
        let cur = $(this).prop('checked');
        if(cur) {
            $('.'+classs+' input:checkbox').prop('checked', true);
        } else {
            $('.'+classs+' input:checkbox').prop('checked', false);
        }
        console.log('cur='+cur);
    });
    $(document).on('click', '#inner-section-btn',function(e) {
        $('.inner-section').toggle();
    });
    $(document).on('change', '#salary_month',function(e) {
        $('#dynamic-reports-form').submit();
    });
    $(document).on('click', '#freeze',function(e) {
        let month = $(this).data('month');
        if(confirm('Are you sure want to freeze salary statment('+month+') ?')) {
            return true;
        }
        return false;
    });
    
});

var buttonCommon = {
	exportOptions: {
		format: {
			body: function(data, column, row) {
				var div = document.createElement("div");
				div.innerHTML = data;
				var text = div.textContent || div.innerText || ""
				return text;
			}
		}
	}
};
$(document).ready(function () {
    $.fn.dataTable.ext.errMode = 'none';
    dtable = $('#salary').on('error.dt', function (e, settings, techNote, message ) {
        console.log( 'An error has been reported by DataTables: ', message );
    }).DataTable({
        searching: false,
        paging: false,
        info: false,
        ordering: false,
        dom: 'Bfrtip',
        rowGroup: {
            dataSrc: 'group'
        },
        buttons: [
            $.extend(true, {}, buttonCommon, {
                extend: 'excel',
                text: 'Download Excel',
                className: 'btn btn-primary btn-md',
            }),
        ],
    });
});

</script>
@endsection