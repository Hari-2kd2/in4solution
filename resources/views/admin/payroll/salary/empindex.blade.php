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
                                'route' => 'salary.empindex',
                                'id' => 'dailyAttendanceReport',
                                'class' => 'form-horizontal',
                                'method'=>'GET'
                            ]) }}
                            <div class="form-group">
                            
                                <div class="col-md-2">
                                    <div class="form-group">
                                       <label class="control-label" for="email">@lang('salary.employee_name')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" required disabled
                                            name="employee_id">
                                            
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $employee->employee_id }}"  {{ 'selected' }} >{{ $value->first_name." ".$value->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                               

                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="department_id">@lang('common.department'):</label>
                                        <select name="department_id" class="form-control department_id  select2" disabled>
                                            <option value="">--- @lang('common.please_select') ---</option>
                                             
                                                <option value="{{ $value->department_id }}"
                                                    @if ($departmentList->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $departmentList->department_name }}</option>
                                            
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
                        @if(isset($_GET['employee_id']))
                        <h4 class="text-right">
                             <a class="btn btn-success" style="color: #fff"
                                        href="{{route('salary.reportdownload',['employee'=>$_GET['employee_id'],'department'=>$_GET['department_id'],'date'=>$_GET['date']])}}"><i class="fa fa-download fa-lg" aria-hidden="true"></i> Download</a>
                        </h4>
                        @endif
                        <div class="table-responsive">
                            <table id="salary" class="table table-bordered" style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th>Employee Id</th>
                                        <th>Employee Name</th>
                                        <th>Department</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>No of Days Worked</th>
                                        <th>Earnings Amount</th>
                                        <th>Deduction</th>
                                        <th>Net Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   
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
    $(function() { 
        $('#salary').DataTable({
            processing: true,
            serverSide: true,
            sDom: 'lrtip' , 
            ajax:{
                url:"{{route('salary.empdetails')}}",
                data: function ( d ) {                     
                    d.branch='<?php echo isset($_GET['branch_id']) ? $_GET['branch_id'] : ''; ?>';
                    d.department='<?php echo isset($_GET['department_id']) ? $_GET['department_id'] : '' ; ?>';
                    d.date='<?php echo isset($_GET['date']) ? $_GET['date'] : '' ; ?>';
                },
            },  
            columns: [
                { data:'finger_print_id', name: 'finger_print_id'},
                { data:'employee', name:'employee'},
                { data:'department', name: 'department'},
                { data:'month', name: 'month'},
                { data:'year', name: 'year'},
                { data:'worked_days', name: 'worked_days'},
                { data:'wages_earnings', name: 'wages_earnings'},
                { data:'deduction', name: 'deduction'},
                { data:'net_amount', name: 'net_amount'},
                { data:'action', name: 'action', orderable: false, searchable: false},
             ],
            //responsive: !0,
        });
    });
</script>
@endsection('page_scripts')