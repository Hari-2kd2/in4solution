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
 {
   display:none;
   width:100%;
   table-layout:fixed;
  }

    /*
  tbody {
   display:block;
   height:500px;
   overflow:auto;
  }
  thead, tbody tr {
   display:table;
   width:100%;
   table-layout:fixed;
  }
  thead {
   width: calc( 100% - 1em )
  }*/
</style>
<script>
    jQuery(function() {
        $("#attendanceRecord").validate();
    });
</script>
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
                <?php
                // dd($SalaryRepository->salaryBase());
                ?>
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                       <div id="searchBox">
                            <div class="col-md-1"></div>
                            {{ Form::open([
                                'route' => 'salary.statement',
                                'id' => 'salaryStatement',
                                'class' => 'form-horizontal',
                                'method'=>'GET'
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                       <label class="control-label" for="email">@lang('salary.employee_name')</label>
                                        <select class="form-control employee_id select2" 
                                            name="employee_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($_REQUEST['employee_id'])) @if ($_REQUEST['employee_id'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif>{{ $value->first_name." ".$value->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                               

                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="department_id">@lang('common.department'):</label>
                                        <select name="department_id" class="form-control department_id  select2">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">Month & Year<span
                                                class="validateRq">*</span>:</label>
                                        <input type="text" class="form-control monthField" style="height: 35px;"
                                            required readonly placeholder="@lang('common.date')" id="date"
                                            name="date"
                                            value="@if (isset($date)) {{ $date }}@else {{ date('Y-m') }} @endif">
                                    </div>
                                </div>
                                <div class="col-sm-0"></div>
                                <div class="col-sm-1">
                                    <label class="control-label col-sm-1 text-white"
                                        for="email">@lang('common.date')</label>
                                    <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                        class="btn btn-info " value="@lang('common.filter')">
                                </div>
                            </div>
                            {{ Form::close() }}

                        </div>

                        <br>
                        {{-- @if(isset($_GET['employee_id']))
                        <h4 class="text-right">
                             <a class="btn btn-success" style="color: #fff"
                                        href="{{route('salary.reportdownload',['employee'=>$_GET['employee_id'],'department'=>$_GET['department_id'],'date'=>$_GET['date']])}}"><i class="fa fa-download fa-lg" aria-hidden="true"></i> Download</a>
                        </h4>
                        @endif --}}
                        <div class="table-responsive">
                            <table id="salary" class="table table-bordered" style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th>Employee Id</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Days Worked</th>
                                        <th>Days Leave</th>
                                        <th>Days Absent</th>
                                        <th>LOP</th>
                                        <th>Basic</th>
                                        <th>HRA</th>
                                        <th>LTA</th>
                                        <th>Special Allowance</th>
                                        <th>Gross Earnings</th>
                                        <th>Other earnings</th>
                                        <th>Over Time</th>
                                        <th>OT ESI Employer</th>
                                        <th>OT ESI Employee</th>
                                        <th>Nett Gross</th>
                                        <th>CTC</th>
                                        <th>PF Employee</th>
                                        <th>ESI Employee</th>
                                        <th>TDS</th>
                                        <th>Salary Advance</th>
                                        <th>Excess Telephoone Usage</th>
                                        <th>Labour Welfare</th>
                                        <th>Professional Tax</th>
                                        <th>ESI Employer</th>
                                        <th>PF Employer</th>
                                        <th>Bonus</th>
                                        <th>Total Deduction</th>
                                        <th>Net Salary</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                   @foreach ($employeeList as $employee)
                                        @php
                                            $Common             = new \App\Components\Common;
                                            $CTC                = $employee->salary_ctc;
                                            $Gross              = $employee->salary_gross;
                                            $Basic              = $SalaryRepository->empData($employee->finger_id, 'basic');
                                            $isOT               = $SalaryRepository->empData($employee->finger_id, 'overtime_status');
                                            $isEsi              = $SalaryRepository->data($employee->finger_id, 'with_esi');
                                            $above_60           = $SalaryRepository->data($employee->finger_id, 'above_60');
                                            $HRA_PERCENT        = $Common::PERCENTAGE_HRA;
                                            $BONUS_PERCENT      = $Common::PERCENTAGE_BONUS;
                                            $HRA                = round($Gross / 100 * $HRA_PERCENT);
                                            $LTA                = round($Basic * 2 / 12);
                                            $Bonus              = round($Basic / 100 * $BONUS_PERCENT);
                                            $PF_Employer        = 0;
                                            $ESI_Employer       = 0;
                                            
                                            $PF_Employee        = 0;
                                            $ESI_Employee       = 0;

                                            $LOP                = 0;
                                            $Other_earnings     = 0;
                                            $TDS                = 0;
                                            $Salary_Advance     = 0;
                                            $Labour_Welfare     = 0;
                                            $Professional_Tax   = 208;
                                            $Excess_Telephoone_Usage = 0;

                                            
                                            $PF_Employer    = ($Gross - $HRA) / 100 * $SalaryRepository->PERCENTAGE_EPF_EMPLOYER;
                                            $PF_Employee    = ($Gross - $HRA) / 100 * $SalaryRepository->PERCENTAGE_EPF_EMPLOYEE;
                                            
                                            // ESI calculate if employee gross is < 21000 thousands
                                            if($Gross < 21000) {
                                                $ESI_Employer = round(($Gross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER), 2);
                                                $ESI_Employee = round(($Gross / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE), 2);
                                            }

                                            $OT_PER_HOUR = 0;
                                            $Over_Time = 0;
                                            $OT_ESI_Employer = 0;
                                            $OT_ESI_Employee = 0;
                                            // if OT enabled employee check
                                            if($isOT) {
                                                $OT_HOURS = $SalaryRepository->data($employee->finger_id, 'ot_hours');
                                                // if OT Hours > 0
                                                if($OT_HOURS>0) {
                                                    $OT_PER_HOUR = $Basic / 8 / 26;
                                                    $Over_Time = round($OT_HOURS * $OT_PER_HOUR);
                                                    $OT_ESI_Employer = round($Over_Time / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER, 2);
                                                    $OT_ESI_Employee = round($Over_Time / 100 * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE);
                                                }
                                            }
                                            
                                            // Check is employee age is above 60
                                            if($above_60) {
                                                $PF_Employer = $ESI_Employer = $Bonus = $ESI_Employee = $PF_Employee = 0;
                                            }
                                            // CTC - Basic - HRA - LTA - Bonus - EPF - ESI
                                            $Special_Allowance = $CTC - $Basic - $HRA - $LTA - $Bonus - $PF_Employer - $ESI_Employer;
                                            
                                            // Gross + OT Amount
                                            $Nett_Gross = $Gross + $Over_Time + $Other_earnings;
                                            
                                            $salary_advance  = $SalaryRepository->data($employee->finger_id, 'salary_advance');
                                            // employee already get salary advance 
                                            if($salary_advance) {
                                                $Salary_Advance = $salary_advance;
                                            }

                                            // Basic / 26 = one day
                                            // $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax manually upload by branch admin (monthly, initmate any where)
                                            $Total_Deduction = ($PF_Employee + $ESI_Employee + $OT_ESI_Employee + $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax + $LOP);
                                            $Net_Salary = $Nett_Gross - $Total_Deduction;
                                        @endphp
                                       <tr>
                                            <td>{{ $employee->finger_id }}</td>
                                            <td>{{ $employee->fullname() }}</td>
                                            <td>{{ $employee->department->department_name ?? '' }}</td>
                                            <td>{{ $SalaryRepository->data($employee->finger_id, 'worked_days') }}</td>
                                            <td>{{ $SalaryRepository->data($employee->finger_id, 'leave_days') }}</td>
                                            <td>{{ $SalaryRepository->data($employee->finger_id, 'absent_days') }}</td>
                                            <td>{{ $LOP }}</td>
                                            <td>{{ $Basic }}</td>
                                            <td>{{ $HRA }}</td>
                                            <td>{{ $LTA }}</td>
                                            <td>{{ $Special_Allowance }}</td>
                                            <td>{{ $Gross }}</td>
                                            <td>{{ $Other_earnings }}</td>
                                            <td>{{ $Over_Time }}</td>
                                            <td>{{ $OT_ESI_Employer }}</td>
                                            <td>{{ $OT_ESI_Employee }}</td>
                                            <td>{{ $Nett_Gross }}</td>
                                            <td>{{ $CTC }}</td>
                                            <td>{{ $PF_Employee }}</td>
                                            <td>{{ $ESI_Employee }}</td>
                                            <td>{{ $TDS }}</td>
                                            <td>{{ $Salary_Advance }}</td>
                                            <td>{{ $Excess_Telephoone_Usage }}</td>
                                            <td>{{ $Labour_Welfare }}</td>
                                            <td>{{ $Professional_Tax }}</td>
                                            <td>{{ $ESI_Employer }}</td>
                                            <td>{{ $PF_Employer }}</td>
                                            <td>{{ $Bonus }}</td>
                                            <td>{{ $Total_Deduction }}</td>
                                            <td>{{ $Net_Salary }}</td>
                                            <td><a href="{{ Route('salary.viewPayslip', ['id' => $employee->finger_id]) }}" target="blank" class="hide btn btn-primary"><i class="fa fa-eye"></i> View</a></td>
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
@endsection
@section('page_scripts')
<script type="text/javascript">
    
</script>
@endsection('page_scripts')