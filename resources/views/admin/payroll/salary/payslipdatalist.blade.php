@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_details')
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
    .dataTables_info
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
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                       <div id="searchBox">
                            <div class="col-md-1"></div>
                            {{ Form::open([
                                'route' => 'salary.payslipdata',
                                'id' => 'dailyAttendanceReport',
                                'class' => 'form-horizontal',
                                'method'=>'GET'
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                       <label class="control-label" for="email">@lang('salary.employee_name') </label>
                                        <select class="form-control employee_id select2  "  
                                            name="employee_id" id="employee_id">
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
                        @if(isset($payroll))
                        <h4 class="text-right">
                        <input type="button" id="bulkexportbtn" class=" btn btn-primary"  value="EXCEL DOWNLOAD"/>
                        </h4>
                        @endif
                        <div id="bulkexportarea">
                        <div class="table-responsive">
                            
                            <table id="salary" class="table table-bordered" style="font-size: 12px;" border="1">
                                <thead class="tr_header">
                                <tr>
                                 <th colspan = "49"><h3>Employee Payslip Export - @if (isset($date)) {{ date('M-Y',strtotime($date)) }}@else {{ date('M-Y') }} @endif</h3></th>
                                </tr>
                                    <tr>

                                        <th>S.No</th>
                                        <th>Finger Print ID</th>  
                                        <th>Employee Name</th>  
                                        <th>Department</th>
                                        <th>yearly CTC</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Days In Month</th>
                                        <th>Sundays</th> 
										<th>Holidays </th>
										<th>CL </th>
										<th>SL</th>
										<th>EL</th>
										<th>LOP</th>
										<th>Absent</th>
										<th>LOP amount</th>
									    <th>Working Days</th>
									    <th>Worked Days</th>
									    <th>Month Salary</th>
									    <th>Day Salary</th>
                                        <th>Hour Salary</th>
                                        <th>Worked Salary</th>
                                        <th>Basic</th>  
                                        <th>DA </th>
                                        <th>HRA</th>
                                        <th>Conveyance</th>
                                        <th>Medical</th>
                                        <th>Children</th> 
										<th>Lta</th>
										<th>Special</th>
										<th>Other</th>
										<th>Income Tax</th>
										<th>Professional Tax</th>
										<th>Wages Earnings</th>
										<th>Deduction</th>
									    <th>Net Amount</th>
									    <th>Full Basic</th>
									    <th>Full DA</th>
									    <th>Full HRA</th>
                                        <th>Full Conveyance</th>
                                        <th>FullMedical</th>
                                        <th>Full Lta</th>  
                                        <th>Full Special</th>
                                        <th>Full Other</th>
                                        <th>OT Amount</th>
                                        <th>OT Hour</th>
                                        <th>PF Amount</th> 
										<th>Advance Deduction</th>
										<th>Full Wages Earnings</th> 
                                    </tr>
                                </thead>
                                <tbody style="border:1px solid;">
                                @php
									 $si=1;
									if($payroll){                                       
                                        foreach($payroll AS $dataSet){
                                            $employee = APP\Model\Employee::where('employee_id', $dataSet['employee'])->first();
                                            $department = APP\Model\Department::where('department_id', $dataSet['department'])->first();
                                 @endphp
								<tr>
                                    <td>{{$si++ }}</td>
									<td>{{$dataSet['finger_print_id']}}</td>	 
									<td>{{$employee->first_name.' '.$employee->last_name}}</td>									 
									<td>{{$department->department_name}}</td>
                                    <td>{{$dataSet['yearly_ctc']}}</td>	 
									<td>{{$dataSet['month']}}</td>									 
									<td>{{$dataSet['year']}}</td>
                                    <td>{{$dataSet['days_in_month']}}</td>	 
									<td>{{$dataSet['sundays']}}</td>									 
									<td>{{$dataSet['holidays']}}</td>
                                    <td>{{$dataSet['cl']}}</td>	 
									<td>{{$dataSet['sl']}}</td>									 
									<td>{{$dataSet['el']}}</td>
                                    <td>{{$dataSet['lop']}}</td>	
                                    <td>{{$dataSet['absent']}}</td> 
									<td>{{$dataSet['lop_amount']}}</td>									 
									<td>{{$dataSet['working_days']}}</td>
                                    <td>{{$dataSet['worked_days']}}</td>	 
									<td>{{$dataSet['month_salary']}}</td>									 
									<td>{{$dataSet['day_salary']}}</td>
                                    <td>{{$dataSet['hour_salary']}}</td>
                                    <td>{{$dataSet['worked_salary']}}</td>									 
									<td>{{$dataSet['basic']}}</td>                                   
                                    <td>{{$dataSet['da']}}</td>	 
									<td>{{$dataSet['hra']}}</td>									 
									<td>{{$dataSet['conveyance']}}</td>
                                    <td>{{$dataSet['medical']}}</td>	 
									<td>{{$dataSet['children']}}</td>									 
									<td>{{$dataSet['lta']}}</td>
                                    <td>{{$dataSet['special']}}</td>	 
									<td>{{$dataSet['other']}}</td>									 
									<td>{{$dataSet['income_tax']}}</td>
                                    <td>{{$dataSet['professional_tax']}}</td>	 
									<td>{{$dataSet['wages_earnings']}}</td>									 
									<td>{{$dataSet['deduction']}}</td>
                                    <td>{{$dataSet['net_amount']}}</td>	 
									<td>{{$dataSet['full_basic']}}</td>									 
									<td>{{$dataSet['full_da']}}</td>
                                    <td>{{$dataSet['full_hra']}}</td>	 
									<td>{{$dataSet['full_conveyance']}}</td>									 
									<td>{{$dataSet['full_medical']}}</td>
                                    <td>{{$dataSet['full_lta']}}</td>	 
									<td>{{$dataSet['full_special']}}</td>									 
									<td>{{$dataSet['full_other']}}</td>
                                    <td>{{$dataSet['ot_amount']}}</td>									 
									<td>{{$dataSet['ot_hour']}}</td>
                                    <td>{{$dataSet['pf_amount']}}</td>	 
									<td>{{$dataSet['advance_deduction']}}</td>									 
									<td>{{$dataSet['full_wages_earnings']}}</td> 									 
									</tr>
									@php
									 }
									}
									@endphp 
                                </tbody>
                            </table>
                        </div>
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
    $(function() {
        $('#salary').DataTable({ 
            "bPaginate": false,
            "bLengthChange": false,
            "bFilter": false,
            "bInfo": false,
            "bAutoWidth": false 
        });         
     
    });
    $(document).ready(function() {
    $(document).on('click','#bulkexportbtn',function(e) {
        var result = 'data:application/vnd.ms-excel,' + encodeURIComponent($('#bulkexportarea').html());
        var link = document.createElement("a");
        document.body.appendChild(link);
        link.download = "payslip_export.xls"; //You need to change file_name here.
        link.href = result;

        link.click();
    });
});
</script>
<script type="text/javascript">

</script>
@endsection('page_scripts')