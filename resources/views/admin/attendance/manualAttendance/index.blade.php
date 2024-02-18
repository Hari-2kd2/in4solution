@php
use App\Model\Employee;
use App\Model\ManualAttendanceCase;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.employee_attendance')
@endsection
<style>
    .departmentName {
        position: relative;
    }
    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
        background: none;
        color: red !important;
        cursor: default;
    }
    #department_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
        width: 100%;
        height: 100%;
    }
    .form-control.error {
        border: 1px solid #E91E63 !important;
    }
    .manual-record {
        background: #e3d6fd !important;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
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
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'manualAttendance.filter', 'id' => 'manual-attendance', 'method' => 'GET']) }}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <div class="form-group departmentName">
                                        <label class="control-label">@lang('employee.department')</label>
                                        <select class="form-control employee_id select2" 
                                            name="department_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if (isset($_REQUEST['department_id'])) @if ($_REQUEST['department_id'] == $value->department_id) {{ 'selected' }} @endif
                                                    @endif>{{ $value->department_name }} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control manual_date required" readonly
                                            placeholder="@lang('common.date')" name="date"
                                            value="@if (isset($_REQUEST['date'])) {{ $_REQUEST['date'] }}@else{{ dateConvertDBtoForm( date('Y-m-d',strtotime("-1 days")) ) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>
                        @if (isset($attendanceData))
                            {{ Form::open(['route' => 'manualAttendance.store', 'id' => 'manual-attendance-from', 'onsubmit' => 'return checkForms()']) }}

                            <input type="hidden" name="department_id" value="{{ $_REQUEST['department_id'] }}">
                            <input type="hidden" name="date" value="{{ $_REQUEST['date'] }}">

                            <div class="table-responsive">
                                <table class="table table-bordered" style="margin-bottom: 47px">
                                    <thead class="tr_header">
                                        <tr>
                                            <th>@lang('common.serial')</th>
                                            <th>@lang('employee.finger_print_no')</th>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('common.department')</th>
                                            <th>@lang('common.branch')</th>
                                            <th>@lang('attendance.in_time')</th>
                                            <th>@lang('attendance.out_time')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $inc = 1;
                                            $selected_date = dateConvertFormtoDB($_REQUEST['date']);
                                        @endphp
                                        @if (count($attendanceData) > 0)
                                            @foreach ($attendanceData as $value)
                                            @php
                                                $Employee = Employee::find($value->employee_id);
                                                $ManualAttendanceIn = ManualAttendanceCase::where('manual_date', $selected_date)->where('ID', $value->finger_id)->where('type', 'IN')->first();
                                                $ManualAttendanceOut = ManualAttendanceCase::where('manual_date', $selected_date)->where('ID', $value->finger_id)->where('type', 'OUT')->first();
                                                $manual_record_in = $ManualAttendanceIn ? 'manual-record' : '';
                                                $manual_record_out = $ManualAttendanceOut ? 'manual-record' : '';
                                                if($value->finger_id=='T0001') {
                                                    // dd($value);
                                                }
                                            @endphp
                                                <tr>
                                                    <td>{{ $inc++ }}</td>
                                                    <td>{{ $value->finger_id }}</td>
                                                    <td>{{ $Employee->fullname() }}</td>
                                                    <td>{{ $Employee->department_disp() }}</td>
                                                    <td>{{ $Employee->branch->branch_name ?? '-' }}</td>
                                                    <td class="times-td in in-{{ $inc }} {{ $manual_record_in }}" data-n="{{ $inc }}">
                                                        <input type="hidden" name="finger_print_id[]" value="{{ $value->finger_id }}">
                                                        <input type="hidden" name="employee_id[]" value="{{ $value->employee_id }}">
                                                        <input class="form-control times-input time-in" type="time" pattern="[0-9]{2}:[0-9]{2}" placeholder="@lang('attendance.in_time')" name="inTime[]" value="{{ $value->inTime ? date('H:i', strtotime($value->inTime)) : '' }}">
                                                    </td>
                                                    <td class="times-td out out-{{ $inc }} {{ $manual_record_out }}" data-n="{{ $inc }}">
                                                        <input class="form-control times-input time-out" format="ddTHH:mm" type="time" pattern="[0-9]{2}:[0-9]{2}" placeholder="@lang('attendance.out_time')" name="outTime[]" value="{{ $value->outTime ? date('H:i', strtotime($value->outTime)) : '' }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5">@lang('attendance.no_data_available')</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            @if (count($attendanceData) > 0)
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <button type="submit" class="btn btn-info btn_style"><i
                                                    class="fa fa-check"></i> @lang('common.save')</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {{ Form::close() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script>
    function checkForms() {
        var errorFlag=true, ecount=0;

        $('.times-td.in').each( function( i ) {

            let n = $(this).data('n');
            var time_in = $('.in-'+n+' .time-in').val()
            var time_out = $('.out-'+n+' .time-out').val();
            if(time_in && time_out) {
                // elog((i+1) + ') time_in='+time_in+', time_out='+time_out);
                if(checkIfTimes(time_in, time_out)) {
                    $('.out-'+n+' .form-control, .in-'+n+' .form-control').addClass('error');
                    errorFlag=false;
                    ecount++;
                } else {
                    $('.out-'+n+' .form-control, .in-'+n+' .form-control').removeClass('error');
                }
            } else {
                $('.out-'+n+' .form-control, .in-'+n+' .form-control').removeClass('error');
            }

        });
        if(!errorFlag) {
            bootbox.alert({message: '<b>'+ecount+' invaild input time found!<br>Please enter proper time for In Time, Out Time<br>In Time should be less then Out Time.</b>',function() {$('.times-td.in:first').focus()}});
        }
        return errorFlag; 
    }
$(document).ready(function () {

    $(document).on("focus", ".manual_date", function() {
        $(this).datepicker({
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            clearBtn: true,
            startDate: '{{ $newdate = date("d/m/Y", strtotime("-2 months")) }}',
            endDate: '{{ date("d/m/Y",strtotime("-1 days")) }}',
        }).on('changeDate', function(e) {
            $(this).datepicker('hide');
        });
    });

    $('.times-input').change(function (e) {
        e.preventDefault();
        let parentTd = $(this).parent();
        let n = $(parentTd).data('n');
        var time_in = '', time_out = '';
        if($(parentTd).hasClass('out')) {
            time_in = $('.in-'+n+' .time-in').val();
            time_out = $(this).val();
        } else if($(parentTd).hasClass('in')) {
            time_in = $(this).val();
            time_out = $('.out-'+n+' .time-out').val();
        }
        if(time_in && time_out) {
            // if error add error class
            if(checkIfTimes(time_in, time_out)) {
                $('.out-'+n+' .form-control, .in-'+n+' .form-control').addClass('error');
            } else {
                $('.out-'+n+' .form-control, .in-'+n+' .form-control').removeClass('error');
            }
        }
    });
});

const date = new Date().toISOString();
function checkIfTimes(StartDt, EndDt) {
    var today = date.substring(0, date.indexOf('T'));
    var strtDt = new Date(today+' '+StartDt);
    var endDt = new Date(today+' '+EndDt);
    if (strtDt > endDt) {
        // start time greater then end time
        // error time return true to we handle validation to restrict
        return true;
    }
    return false
}
</script>
@endsection