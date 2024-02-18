@php
use App\Components\Common; 
@endphp
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
    .dataTables_info,.dataTables_length,.dataTables_paginate,.dataTables_filter {
        display:none;
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
<div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2"
                stroke-miterlimit="10" />
        </svg>
    </div>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.Payslip')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>
   
    <hr>
    <div class="row">
        
        <div class="col-sm-12">
            <!-- Preloader -->
    <!-- ============================================================== -->
    
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                    @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <br>
                        @if (count($payroll) > 0 && $payroll != '')
                             <h4 class="text-right">
                                 
                                         <input type="button" id="bulkexportbtn" class=" btn btn-primary"  value="EXCEL DOWNLOAD"/>        
                             </h4>
                             <h4 class="text-right">
                             <input id="bulk-generate" class="btn btn-info text-sm" value="Generate Salary">
                                    </div>
                             </h4>
                            <input type="hidden" class="monthField" name="month" value="{{isset($month) ? $month : ''}}">
                            <input type="hidden" class="from_date" name="from_date" value="{{isset($from_date) ? $from_date : ''}} ">
                            <input type="hidden" class="to_date" name="to_date" value="{{isset($to_date) ? $to_date : ''}} ">
                             
                         @endif
                         <div id="bulkexportarea">
                        <div class="table-responsive" style="overflow-x:scroll; ">                        
                            <table id="bulkpreview" class="table table-bordered table-responsive" style="font-size: 12px;width:100%;" border="1">
                                <thead class="">
                                <tr>
                                 <th colspan = "48"><h3><b>Employee Payroll Generation - @if (isset($month)) {{ date('M-Y',strtotime($month)) }}@else {{ date('M-Y') }} @endif </b></h3></th>
                                </tr>
                                    <tr>
                                        <th>SNo</th>
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
                                <tbody> 
                                @php
									 $si=1;
									if($payroll){                                       
                                        foreach($payroll AS $dataSet){
                                            $department = APP\Model\Department::where('department_id', $dataSet['department'])->first();
                                 @endphp
									<tr>
									<td>{{$si++ }}</td>	 
									<td>{{$dataSet['employee_name']}}</td>									 
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
									<td>{{round($dataSet['lop_amount'],2)}}</td>									 
									<td>{{$dataSet['working_days']}}</td>
                                    <td>{{$dataSet['worked_days']}}</td>	 
									<td>{{round($dataSet['month_salary'],2)}}</td>									 
									<td>{{round($dataSet['day_salary'],2)}}</td>
                                    <td>{{round($dataSet['hour_salary'],2)}}</td>
                                    <td>{{round($dataSet['worked_salary'],2)}}</td>									 
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
									<td>{{round($dataSet['wages_earnings'],2)}}</td>									 
									<td>{{round($dataSet['deduction'],2)}}</td>
                                    <td>{{round($dataSet['net_amount'],2)}}</td>	 
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
        
    $('#bulkpreview').DataTable({ 
    "bPaginate": false,
    "bLengthChange": false,
    "bFilter": false,
    "bInfo": false,
    "bAutoWidth": false }); 
        
        
        $('#bulk-generate').click(function(e) {
            e.preventDefault();

            var month = $('.monthField').val();
            var from_date = $('.from_date').val();
            var to_date = $('.to_date').val();
            $('.preloader').show();
            $.ajax({
                type: "get",
                url: "{{ route('Salary.bulk-generate') }}",
                data: {
                    month: month,
                    from_date: from_date,
                    to_date: to_date
                },
                
                success: function(response) {
                     
                     
                    if (response.status != true) {
                        $('.preloader').hide();
                        $.toast({
                            heading: 'error',
                            text: response.message,
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'error',
                            hideAfter: 1000,
                            stack: 1
                        });
                    }else{
                        window.location.href = "{{route('salary.index')}}"; 
                     
                        $.toast({
                            heading: 'success',
                            text: response.message,
                            position: 'top-right',
                             loaderBg: '#ff6849',
                            icon: 'success',
                            hideAfter: 1000,
                            stack: 1
                        });
                        setTimeout(function(){
                           $('#message').html('');                     
                        }, 500);
                    window.location.href = "{{route('salary.index')}}"; 
                    $('.preloader').hide();
                    }
                }
            });
        });
    // $("#bulkexportbtn").click(function (e) {
    // window.open('data:application/vnd.ms-excel,' +  encodeURIComponent($('#bulkexportarea').html()));
    // e.preventDefault();
    // });
    
});  
$(document).ready(function() {
    $(document).on('click','#bulkexportbtn',function(e) {
        var result = 'data:application/vnd.ms-excel,' + encodeURIComponent($('#bulkexportarea').html());
        var link = document.createElement("a");
        document.body.appendChild(link);
        link.download = "payroll_export.xls"; //You need to change file_name here.
        link.href = result;

        link.click();
    });
});
</script>
@endsection('page_scripts')