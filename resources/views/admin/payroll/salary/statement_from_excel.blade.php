@php
    use Carbon\Carbon;
    use App\Model\ProfessionalTax;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_statement')
@endsection
<style>
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
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                    @lang('dashboard.dashboard')</a></li>
                    <li>@yield('title')</li>
                </ol>
            </div>
        </div>
        <hr>
    <div class="row">
        
        <div class="col-sm-12">
            <div class="panel panel-info">
                @php
                $listMonth = \App\Model\PayrollUpload::salaryMonthList();
                $branchList = branchList();
                $branchList[''] = 'All Branch';
                $listNull[null] = 'Select Salary Month';
                $listMonth = \App\Model\PayrollUpload::salaryMonthList();
                $listMonth = array_merge($listNull, $listMonth);
                $select_month_form = $salary_month = request()->get('salary_month', '');
                $freeze = request()->get('freeze', '');
                $select_month = request()->get('salary_month', date('m/Y'));
                $select_month = dateConvertFormtoDB(('01/'.$select_month));
                $date = new DateTime( monthConvertFormtoDB($select_month) );
                $select_month_name = $date->format('F');
                $select_month = $date->format('Y-m');
                $Total_Work_Days = count(findMonthToAllDate($select_month));
                $dates = findMonthToAllDate($select_month);
            @endphp

            <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @include('flash_message')
                        @if ($role_id)
                            <div id="searchBox">
                                {{ Form::open([
                                    'route' => 'salary.statement',
                                    'id' => 'salaryStatement',
                                    'class' => '',
                                    'method'=>'GET'
                                    ]) }}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="branch_id">@lang('common.branch')</label>
                                            {{ Form::select('branch_id', $branchList, $Employee->branch_id, ['id' => 'branch_id', 'class' => 'form-control branch_id']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label class="control-label text-white">&nbsp;</label><br>
                                            <input type="submit" id="filter" style="margin-left: 5px;" class="btn btn-info " value="@lang('common.filter')">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        @if ($select_month_form)
                                        <div class="form-group">
                                            <label class="control-label text-white">&nbsp;</label><br>
                                            <button name="freeze" data-month="{{ $salary_month }}" class="btn btn-info hide" value="freeze" id="freeze">Freeze</button>
                                            <button name="freeze-clone" type="button" data-month="{{ $salary_month }}" class="btn btn-info" value="freeze" id="freeze-clone">Freeze</button>
                                        </div>
                                        @endif
                                    </div>
                                    
                                </div>
                                
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
                                {{ Form::close() }}
                            </div>

                        @endif
                        {{-- <div class="row">
                            <div class="col-xs-12">
                                <h4 class="text-right">
                                    <input type="button" id="bulkexportbtn" class=" btn btn-primary"  value="EXCEL DOWNLOAD"/>
                                </h4>
                            </div>
                        </div> --}}
                        <div class="table-responsive" id="bulkexportarea">
                            <table id="salary" class="table table-bordered" style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th>SL. NO</th>
                                        <th>Employee Code</th>
                                        <th>Employee Name</th>
                                        <th>DOB</th>
                                        <th>DOJ</th>
                                        <th>Basic</th>
                                        <th>HRA</th>
                                        <th>LTA</th>
                                        <th>Special Allowance</th>
                                        <th>Earned Gross</th>
                                        <th>Other Earnings</th>
                                        <th>Over Time</th>
                                        <th>OT ESI Employer</th>
                                        <th>OT ESI Employee</th>
                                        <th>ESI Employer</th>
                                        <th>ESI Employee</th>
                                        <th>ESI Employee Total</th>
                                        <th>ESI Employer Total</th>
                                        <th>ESI TOTAL</th>
                                        <th>PF Employer</th>
                                        <th>PF Employee</th>
                                        <th>PF TOTAL</th>
                                        <th>Nett Gross</th>
                                        <th>CTC</th>
                                        <th>TDS</th>
                                        <th>Salary Advance</th>
                                        <th>Excess Telephoone Usage</th>
                                        <th>Labour Welfare</th>
                                        <th>Professional Tax</th>
                                        <th>Bonus</th>
                                        <th>Total Deduction</th>
                                        <th>Net Salary</th>
                                        <th>Total Days</th>
                                        <th>Absent</th>
                                        <th>Leave</th>
                                        <th></th>
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
                                            $SALARY_MONTH       = dateConvertFormtoDB('01/'.$salary_month);
                                            $EsiClass = '';
                                            $OvertimeClass = '';
                                            $Esi21Class = '';
                                            $ProbationClass = '';
                                            $MidJoinReleveDays = 0;
                                            $Other_Variable = [];
                                            $employee      = $ExcelEmployee->statmentEmployee;
                                            if(!$employee || !isset($ExcelEmployee->ctc)) {
                                                continue;
                                            }
                                            $jLog = 'Code: '.$employee->emp_code.' ≈ ';
                                            $PayrollStatement = \App\Model\PayrollStatement::where('payroll_upload_id', $ExcelEmployee->payroll_upload_id)->first();
                                            if(!$PayrollStatement) {
                                                $PayrollStatement = new \App\Model\PayrollStatement;
                                                $PayrollStatement->payroll_upload_id = $ExcelEmployee->payroll_upload_id;
                                            }
                                            $SNO++;
                                            $Common             = new \App\Components\Common;
                                            // in later here we give employee table $employee->salary_ctc, $employee->salary_gross
                                            $CTC                = $ExcelEmployee->ctc;
                                            $Gross              = $ExcelEmployee->gross_salary;
                                            $EarnedCTC          = $CTC;
                                            $EarnedGross        = $Gross;
                                            $Basic              = $EarnedCTC / 100 * $Common::PERCENTAGE_BASIC;
                                            $jLog .= '$EarnedCTC='.$EarnedCTC . ', $EarnedGross=' . $EarnedGross;
                                            $jLog .= ', $Basic='.$Basic;
                                            $Basic              = round($Basic);
                                            $jLog .= ', Basic='.$Basic;
                                            $BasicAbs           = round($EarnedCTC / 100 * $Common::PERCENTAGE_BASIC);
                                            $Over_Time          = $ExcelEmployee->over_time;
                                            $days_leaves        = $ExcelEmployee->days_leaves;
                                            $days_absents       = $ExcelEmployee->days_absents;
                                            $days_presents      = $ExcelEmployee->days_presents;
                                            $days_holidays      = $ExcelEmployee->days_holidays;
                                            $days_weekoffs      = $ExcelEmployee->days_weekoffs;
                                            $Age                = yearDiffs($employee->date_of_birth, date('Y-m-d'));
                                            $DAYS_FROM_DOJ      = false;
                                            $MONYHS_FROM_DOJ    = false;
                                            $semi_salary        = false;
                                            $leaving_salary     = false;
                                            $MONYHS_WORKED      = monthDiffs($employee->date_of_joining, date('Y-m-d'));
                                            $DAYS_FROM_DOJ_TITLE = $DAYS_FROM_DOL_TITLE = '';
                                            if($employee->permanent_status==0) {
                                                $SAL_DATE=strtotime($SALARY_MONTH);
                                                $SAL_MON=(int) date("m",$SAL_DATE);
                                                $SAL_YEA=(int) date("Y",$SAL_DATE);

                                                $DOJ_DATE=strtotime($employee->date_of_joining);
                                                $DOJ_DAY=(int) date("d",$DOJ_DATE);
                                                $DOJ_MON=(int) date("m",$DOJ_DATE);
                                                $DOJ_YEA=(int) date("Y",$DOJ_DATE);

                                                if($DOJ_DAY > 1 && $SAL_MON==$DOJ_MON && $DOJ_YEA==$SAL_YEA) {
                                                    $DAYS_FROM_DOJ      = daysDiffs($employee->date_of_joining, $SALARY_MONTH);
                                                    $MONYHS_FROM_DOJ    = monthDiffs($employee->date_of_joining, $SALARY_MONTH);
                                                   
                                                    $EarnedCTC          = $EarnedCTC * ($Total_Work_Days  - $DAYS_FROM_DOJ) / $Total_Work_Days;
                                                    $jLog .= ', $EarnedCTC(MID)='.$EarnedCTC;
                                                    $EarnedCTC          = round($EarnedCTC);
                                                    $jLog .= ', EarnedCTC='.$EarnedCTC;
                                                    $EarnedGross        = round($EarnedGross * ($Total_Work_Days - $DAYS_FROM_DOJ) / $Total_Work_Days);
                                                    $Basic              = round($EarnedCTC / 100 * $Common::PERCENTAGE_BASIC);
                                                    $semi_salary        = ' semi-salary ';
                                                    $DAYS_FROM_DOJ_TITLE = $DAYS_FROM_DOJ.' days';
                                                    $MidJoinReleveDays = $Total_Work_Days - $DAYS_FROM_DOJ;
                                                    $jLog .= ", SAL_MON=$SAL_MON, DOJ_MON=$DOJ_MON, DOJ_YEA=$DOJ_YEA, SAL_YEA=$SAL_YEA";
                                                }
                                            }
                                            
                                            if($employee->date_of_leaving) {
                                                $jLog .= ", DOL=$employee->date_of_leaving";
                                                $SAL_DATE=strtotime($SALARY_MONTH);
                                                $SAL_MON=(int) date("m",$SAL_DATE);
                                                $SAL_YEA=(int) date("Y",$SAL_DATE);
                                                
                                                $DOL_DATE=strtotime($employee->date_of_leaving);
                                                $DOL_DAY=(int) date("d",$DOL_DATE);
                                                $DOL_MON=(int) date("m",$DOL_DATE);
                                                $DOL_YEA=(int) date("Y",$DOL_DATE);
                                                if($DOL_DAY > 1 && $SAL_MON==$DOL_MON && $DOL_YEA==$SAL_YEA) {
                                                    $DAYS_FROM_DOL      = daysDiffs($employee->date_of_leaving, $SALARY_MONTH) + 1; // last day included
                                                    $MONYHS_FROM_DOL    = monthDiffs($employee->date_of_leaving, $SALARY_MONTH);
                                                   
                                                    $EarnedCTC          = ($EarnedCTC * ($Total_Work_Days  - $DAYS_FROM_DOL) / $Total_Work_Days);
                                                    $jLog .= ', $EarnedCTC(DOL)='.$EarnedCTC;
                                                    $EarnedCTC          = round($EarnedCTC);
                                                    $jLog .= ', EarnedCTC(DOL)='.$EarnedCTC;
                                                    $EarnedGross        = round($EarnedGross * ($Total_Work_Days - $DAYS_FROM_DOL) / $Total_Work_Days);
                                                    $Basic              = round($EarnedCTC / 100 * $Common::PERCENTAGE_BASIC);
                                                    $leaving_salary        = ' leaving-salary ';
                                                    $DAYS_FROM_DOL_TITLE = $DAYS_FROM_DOL.' days';
                                                    $MidJoinReleveDays = $DAYS_FROM_DOL;
                                                    $jLog .= ", SAL_MON=$SAL_MON, DOL_MON=$DOL_MON, DOL_YEA=$DOL_YEA, SAL_YEA=$SAL_YEA, date_of_leaving=".$employee->date_of_leaving;
                                                }
                                            }

                                            $addYear            = addYear($employee->date_of_birth, $SalaryRepository->NO_ESI_AGE_ABOVE);
                                            $AgeEsiDate         = nextMonthFirstDate($addYear);
                                            $AgeFlag            = false;
                                            // up come the first date include
                                            $HRA_PERCENT        = $Common::PERCENTAGE_HRA;
                                            $BONUS_PERCENT      = $Common::PERCENTAGE_BONUS;
                                            $LOP                = 0;
                                            if($ExcelEmployee->days_absents>0) {
                                                $EarnedCTC          = round($EarnedCTC * ($Total_Work_Days  - $ExcelEmployee->days_absents) / $Total_Work_Days);
                                                $jLog .= ', $EarnedGross='.$EarnedGross;
                                                $EarnedGross        = $EarnedGross * ($Total_Work_Days  - $ExcelEmployee->days_absents) / $Total_Work_Days;
                                                $EarnedGross        = round($EarnedGross);
                                                $jLog .= ', EarnedGross='.$EarnedGross;
                                                $Basic              = round($EarnedCTC / 100 * $Common::PERCENTAGE_BASIC);
                                            }

                                            $HRA                        = $EarnedGross / 100 * $HRA_PERCENT;
                                            $jLog .= ', $HRA='.$HRA;
                                            $HRA                        = round($HRA);
                                            $jLog .= ', HRA='.$HRA;
                                            $LTA                        = $Basic * 2 / 12;
                                            $jLog .= ', $LTA='.$LTA;
                                            $LTA                        = round($LTA);
                                            $jLog .= ', LTA='.$LTA;
                                            $Bonus                      = $Basic / 100 * $BONUS_PERCENT;
                                            $jLog .= ', $Bonus='.$Bonus;
                                            $Bonus                      = round($Bonus);
                                            $jLog .= ', Bonus='.$Bonus;

                                            $ESI_TOTAL                  = 0;
                                            $ESI_Employee_Total         = 0;
                                            $ESI_Employer_Total         = 0;
                                            $PF_TOTAL                   = 0;
                                            $PF_Employer                = 0;
                                            $ESI_Employer               = 0;
                                            
                                            $PF_Employee                = 0;
                                            $ESI_Employee               = 0;
                                            
                                            $date = new DateTime( monthConvertFormtoDB($select_month) );
                                            $monthNo = $date->format('n');
                                            $ProfessionalTax = ProfessionalTax::whereRaw('FIND_IN_SET("'.$monthNo.'", months)')->first();
                                            $ExcelEmployee->professional_tax = $ProfessionalTax->amount ?? $ExcelEmployee->professional_tax;

                                            $Other_earnings             = $ExcelEmployee->other_earnings;
                                            $TDS                        = $ExcelEmployee->tds;
                                            $Salary_Advance             = $ExcelEmployee->salary_advance;
                                            $Labour_Welfare             = $ExcelEmployee->labour_welfare;
                                            $Professional_Tax           = $ExcelEmployee->professional_tax;
                                            $Excess_Telephoone_Usage    = $ExcelEmployee->excess_telephone_usage;
                                            
                                            if($employee->pf_status==1) {
                                                $PF_Employer                = ($EarnedGross - $HRA) / 100 * $SalaryRepository->PERCENTAGE_EPF_EMPLOYER;
                                                $jLog .= ', $PF_Employer='.$PF_Employer;
                                                $PF_Employer                = round($PF_Employer);
                                                $jLog .= ', PF_Employer='.$PF_Employer;
                                                $PF_Employee                = ($EarnedGross - $HRA) / 100 * $SalaryRepository->PERCENTAGE_EPF_EMPLOYEE;
                                                $jLog .= ', $PF_Employee='.$PF_Employee;
                                                $PF_Employee                = round($PF_Employee);
                                                $jLog .= ', PF_Employee='.$PF_Employee;
                                            } else {
                                                $PF_Employee = $PF_Employer = 0;
                                            }
                                            
                                            // ESI calculate if employee gross is < 21000 thousands
                                            if($Gross < 21000) { // input gross base ESI calulation in previously $EarnedGross < 21000
                                                $jLog .= ', * $EarnedGross='.$EarnedGross;
                                                $ESI_Employer = ($EarnedGross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER);
                                                $jLog .= ', $ESI_Employer='.$ESI_Employer;
                                                $ESI_Employer = round($ESI_Employer, 2);
                                                $jLog .= ', ESI_Employer='.$ESI_Employer;
                                                $ESI_Employee = ($EarnedGross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE);
                                                $jLog .= ', $ESI_Employee='.$ESI_Employee;
                                                $ESI_Employee = ceil($ESI_Employee);
                                                $jLog .= ', ESI_Employee='.$ESI_Employee;
                                            } elseif ($employee->salary_revision && $employee->salary_esi_stop) {
                                                $esi_month = $employee->salary_revision;
                                                $esi_stop = $employee->salary_esi_stop;
                                                $date = new DateTime($employee->salary_revision);
                                                $salary_revision_month = $date->format('Y-m-01');
                                                if($SALARY_MONTH>=$salary_revision_month && $SALARY_MONTH<=$employee->salary_esi_stop) {
                                                    $ESI_Employer = round(($EarnedGross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER), 2);
                                                    $ESI_Employee = round(($EarnedGross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE), 2);
                                                    $DAYS_FROM_DOJ_TITLE .= PHP_EOL. 'salary_revision_month='. dateConvertDBtoForm($employee->salary_revision).', ESI_Employer='.$ESI_Employer.', ESI_Employee='.$ESI_Employee;
                                                    $EsiClass = 'esi-revision';
                                                }
                                            }
                                            $OT_PER_HOUR = '0';
                                            $OT_HOURS = '0';
                                            $OT_ESI_Employer = 0;
                                            $OT_ESI_Employee = 0;
                                            // if Over Time Hours > 0
                                            if($Over_Time>0) {
                                                $OT_HOURS = $Over_Time;
                                                $OT_PER_HOUR = $Basic / 208;
                                                $Over_Time = round($Over_Time * $OT_PER_HOUR);
                                                if($EarnedGross < 21000) {
                                                    $OT_ESI_Employer = $Over_Time / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER;
                                                    $jLog .= ', $OT_ESI_Employer='.$OT_ESI_Employer;
                                                    $OT_ESI_Employer = round($OT_ESI_Employer, 2);
                                                    $jLog .= ', OT_ESI_Employer='.$OT_ESI_Employer;
                                                    
                                                    $OT_ESI_Employee = $Over_Time / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE;
                                                    $jLog .= ', $OT_ESI_Employee='.$OT_ESI_Employee;
                                                    $OT_ESI_Employee = round($OT_ESI_Employee);
                                                    $jLog .= ', OT_ESI_Employee='.$OT_ESI_Employee;
                                                }
                                                $OvertimeClass = 'green-color';
                                            }

                                           
                                            // Check is employee age is above 60
                                            if($employee->date_of_birth!='' && $TODAY >= $AgeEsiDate && $Age >= $SalaryRepository->NO_ESI_AGE_ABOVE) {
                                                $AgeFlag = ' above-60 ';
                                                $ESI_Employer = $Bonus = $ESI_Employee = $OT_ESI_Employer = $OT_ESI_Employee=0;
                                                // $PF_Employer = $PF_Employee = 0;
                                            }
                                            // CTC - Basic - HRA - LTA - Bonus - EPF - ESI
                                            $Special_Allowance = $EarnedCTC - ($Basic + $HRA + $LTA + $Bonus + $PF_Employer + $ESI_Employer + $OT_ESI_Employer);
                                            $jLog .= ', $Special_Allowance='.$Special_Allowance;
                                            $Special_Allowance = round($Special_Allowance);
                                            $jLog .= ', Special_Allowance='.$Special_Allowance;
                                            // Gross + OT Amount
                                            $Nett_Gross = $EarnedGross + $Over_Time + $Other_earnings;
                                            $jLog .= ', Nett_Gross='.$Nett_Gross;
                                            
                                            // $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax manually upload by superadmin
                                            $Total_Deduction = ($PF_Employee + $ESI_Employee + $OT_ESI_Employee + $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax);
                                            $jLog .= ', Total_Deduction='.$Total_Deduction;
                                            $Net_Salary_Abs = $Nett_Gross - $Total_Deduction;

                                            $jLog .= ', $Net_Salary='.$Net_Salary_Abs;
                                            $Net_Salary = round($Net_Salary_Abs);
                                            $jLog .= ', Net_Salary='.$Net_Salary;
                                            $Net_SalaryRou = round($Net_Salary_Abs);

                                            if($CTC==0 && $Gross==0) {
                                                $PF_Employer=$ESI_Employer=$PF_Employee=$ESI_Employee=$Special_Allowance=$EarnedCTC=$Basic=$HRA=$LTA=$Bonus=$PF_Employer=$ESI_Employer=$Nett_Gross=$EarnedGross=$Over_Time=$Other_earnings=$Total_Deduction=$PF_Employee=$ESI_Employee=$OT_ESI_Employee=$TDS=$Salary_Advance=$Excess_Telephoone_Usage=$Labour_Welfare=$Professional_Tax=$LOP=$Net_Salary = 0;
                                            }

                                            // ESI TOTALS
                                            $ESI_Employee_Total = $ESI_Employee + $OT_ESI_Employee;
                                            $ESI_Employer_Total = $ESI_Employer + $OT_ESI_Employer;
                                            $ESI_TOTAL = $ESI_Employee_Total + $ESI_Employer_Total;
                                            // PF TOTAL
                                            $PF_TOTAL = $PF_Employee + $PF_Employer;
                                            $jLog .= ', ESI_Employee_Total='.$ESI_Employee_Total .', ESI_Employer_Total='.$ESI_Employer_Total.', ESI_TOTAL='.$ESI_TOTAL .', PF_TOTAL='.$PF_TOTAL;
                                            $vars = ['employee_id','finger_print_id','emp_code','fullname','SALARY_MONTH','salary_freeze','date_of_birth','date_of_joining','LOP','Basic','HRA','LTA','Special_Allowance','EarnedGross','Other_earnings','OT_HOURS','OT_PER_HOUR','Over_Time','OT_ESI_Employer','OT_ESI_Employee','ESI_Employee_Total','ESI_Employer_Total','ESI_TOTAL','PF_TOTAL','Nett_Gross','EarnedCTC','PF_Employee','ESI_Employee','TDS','Salary_Advance','Excess_Telephoone_Usage','Labour_Welfare','Professional_Tax','ESI_Employer','PF_Employer','Bonus','Total_Deduction','Net_Salary','payroll_upload_id', 'Other_Variable', 'days_leaves', 'days_absents', 'days_presents', 'days_holidays', 'days_weekoffs'];
                                            $Other_Variable_Vars = ['Total_Work_Days', 'MidJoinReleveDays', 'AgeFlag', 'OvertimeClass', 'EsiClass', 'leaving_salary', 'semi_salary', 'DAYS_FROM_DOJ_TITLE', 'DAYS_FROM_DOL_TITLE'];
                                            
                                            $employee_id = $employee->employee_id;
                                            $emp_code = $employee->emp_code;
                                            $finger_print_id = $employee->finger_id;
                                            $fullname = $employee->fullname();
                                            $salary_freeze = $ExcelEmployee->salary_freeze;
                                            $t=1;
                                            foreach ($Other_Variable_Vars as $pkey => $pfield) {
                                                if(isset($$pfield)) {
                                                    $Other_Variable[$pfield] = $$pfield;
                                                }
                                            }
                                            $Other_Variable = json_encode($Other_Variable);
                                            foreach ($vars as $key => $field) {
                                                if(isset($$field) && $PayrollStatement->salary_freeze==0) {
                                                    $PayrollStatement->$field = $$field;
                                                    $t++;
                                                }
                                            }
                                            if($PayrollStatement->salary_freeze==0) {
                                                $branch_id = request()->get('branch_id');
                                                if(!$branch_id) {
                                                    $ExcelEmployee->Other_Variable = $PayrollStatement->Other_Variable;
                                                    $ExcelEmployee->update();
                                                    if($PayrollStatement->payroll_id) {
                                                        $PayrollStatement->update();
                                                    } else {
                                                        $PayrollStatement->save();
                                                    }
                                                }
                                            } else {
                                                foreach ($vars as $key => $field) {
                                                    if(isset($$field)) {
                                                        $$field = $PayrollStatement->$field;
                                                        $t++;
                                                    }
                                                }
                                            }
                                            // dd($PayrollStatement->getAttributes());
                                            $jLog .= ', EarnedGross='.$EarnedGross;
                                        @endphp
                                       <tr class="{{ $semi_salary . $AgeFlag}}">
                                            <td class="{{ $ProbationClass . $leaving_salary }}" title="{{ $DAYS_FROM_DOL_TITLE }}">
                                                {{ $SNO }}
                                             </td>
                                            <td class="{{ $EsiClass }}" title="{{ $DAYS_FROM_DOJ_TITLE }}">{{ $employee->emp_code }}</td>
                                            <td class="{{ $OvertimeClass }}">{{ $fullname }}</td>
                                            <td>{{ dateConvertDBtoForm($employee->date_of_birth) }}</td>
                                            <td>{{ dateConvertDBtoForm($employee->date_of_joining) }}</td>
                                            <td>{{ $Basic }}</td>
                                            <td>{{ $HRA }}</td>
                                            <td>{{ $LTA }}</td>
                                            <td>{{ $Special_Allowance }}</td>
                                            <td>{{ $EarnedGross }}</td>
                                            <td>{{ $Other_earnings }}</td>
                                            <td title="{{ $OT_HOURS>0 ? 'Per hour  '.$OT_PER_HOUR.' * '.$OT_HOURS.' hrs' : ''  }}"><span class="{{ $Over_Time > 0 ? 'blue-color' : ''}}">{{ $Over_Time }}</span></td>
                                            <td>{{ $OT_ESI_Employer }}</td>
                                            <td>{{ $OT_ESI_Employee }}</td>
                                            <td>{{ $ESI_Employer }}</td>
                                            <td><span class="{{ $EsiClass }}" title="{{ $EsiClass ? 'Salary Revision - ESI' : '' }}">{{ $ESI_Employee }}</span></td>
                                            <td>{{ $ESI_Employee_Total }}</td>
                                            <td>{{ $ESI_Employer_Total }}</td>
                                            <td>{{ $ESI_TOTAL }}</td>
                                            <td>{{ $PF_Employer }}</td>
                                            <td>{{ $PF_Employee }}</td>
                                            <td>{{ $PF_TOTAL }}</td>
                                            <td>{{ $Nett_Gross }}</td>
                                            <td>{{ $EarnedCTC }}</td>
                                            <td>{{ $TDS }}</td>
                                            <td>{{ $Salary_Advance }}</td>
                                            <td>{{ $Excess_Telephoone_Usage }}</td>
                                            <td>{{ $Labour_Welfare }}</td>
                                            <td>{{ $Professional_Tax }}</td>
                                            <td>{{ $Bonus }}</td>
                                            <td>{{ $Total_Deduction }}</td>
                                            <td>{{ $Net_Salary }}</td>
                                            <td title="{{ ($employee->date_of_leaving ? 'DOL' : 'DOJ') . ': ' . dateConvertDBtoForm($employee->date_of_leaving ? $employee->date_of_leaving : $employee->date_of_joining) }}">{{ $MidJoinReleveDays>0 ? $MidJoinReleveDays : $Total_Work_Days }}</td>
                                            <td>{{ $ExcelEmployee->days_absents > 0 ? $ExcelEmployee->days_absents : (int) ($ExcelEmployee->days_absents) }}</td>
                                            <td>{{ $ExcelEmployee->days_leaves > 0 ? $ExcelEmployee->days_leaves : (int) $ExcelEmployee->days_leaves }}</td>
                                            <td>
                                                <a href="{{ Route('upload.payrollview', ['id' => $ExcelEmployee->payroll_upload_id, 'select_month_form'=>$select_month_form]) }}" target="blank" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i> Payslip</a>
                                            </td>
                                       </tr>
                                       @php
                                           if($gLog!='all' && $gLog!=$employee->emp_code) {
                                            $jLog = '';
                                           } else {
                                            $jLog = addslashes($jLog);
                                           }
                                       @endphp
                                       <script>
                                        $(document).ready(function () {
                                            <?php
                                            if($jLog) {
                                            ?>
                                            ilog("{{ addslashes($jLog) }}");
                                            <?php
                                            }
                                            ?>
                                        });
                                       </script>
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
@php
    function pround($value) {
        return round($value * 2, 1) / 2;
        // $step = 1;
        // $multiplicand = floor( $value / $step );
        // $rest = $value % $step ;
        // if( $rest > $step/2 ) $multiplicand++; // round up if needed
        // $roundedvalue = $step*$multiplicand;
        // return $roundedvalue;
    }
@endphp
@endsection
@section('page_scripts')
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', 'table.dataTable tbody tr',function(e) {
        $(this).toggleClass('highlight');
    });
    $(document).on('change', '#salary_month',function(e) {
        $('#salaryStatement').submit();
    });
    $(document).on('click', '#freeze-clone',function(e) {
        let month = $(this).data('month');
        bootbox.confirm('<h4>Are you sure want to freeze all branch salary statment('+month+') ?</h4><h4 class="red-color">Warning! Once freezed can not be be roll back un-freez mode!<h4>',
        function(result) {
            if(result) {
                console.log('This was logged in the callback: ' + result);
                $('#freeze').click();
                return result;
            }
        });
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