@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.leave_balance_report')
@endsection
@php
$sl = 0;
$NA = '-';
@endphp
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
</style>
<script>
    jQuery(function() {
        $("#leaveBalanceReport").validate();
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

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'leaveReport.leaveBalanceReport', 'id' => 'leaveBalanceReport']) }}
                                
                                @if (session('logged_session_data.role_id')<=3)
                                    @if (session('logged_session_data.role_id')==1)
                                        <div class="col-md-3" hidden>
                                            <div class="form-group branch_name">
                                                <label class="control-label">@lang('common.branch')<span
                                                        class="validateRq"> </span>:</label>
                                                <select class="form-control branch_id select2" name="branch_id">
                                                    <option value="">---- @lang('common.please_select') ----</option>
                                                    @foreach ($branchList as $value)
                                                        <option value="{{ $value->branch_id }}"
                                                            @if (@$value->branch_id == request()->post('branch_id')) {{ 'selected' }} @endif>
                                                            {{ $value->branch_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-3">
                                        <div class="form-group department_name">
                                            <label class="control-label" for="email">@lang('common.department')<span
                                                    class="validateRq"> </span>:</label>
                                            <select class="form-control department_id select2" name="department_id">
                                                <option value="">---- @lang('common.please_select') ----</option>
                                                @foreach ($departmentList as $value)
                                                    <option value="{{ $value->department_id }}"
                                                        @if (@$value->department_id == request()->post('department_id')) {{ 'selected' }} @endif>
                                                        {{ $value->department_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                {{-- <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBtoForm(date('Y-01-01')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="to_date"
                                            value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div> --}}
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
                        {{-- @if (count($results) > 0)
                            <h4 class="text-right">
                                <a class="btn btn-success" style="color: #fff"
                                    href="{{ URL('downloadLeaveReport/?department_id=' . $department_id . '&from_date=' . $from_date . '&to_date=' . $to_date) }}"><i
                                        class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download') PDF</a>
                            </h4>
                        @endif --}}
                        <div class="table-responsive" style="font-size: 12px;">
                            <table id="myDataTable" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('leave.employee_id')</th>
                                        <th>@lang('leave.employee')</th>
                                        <th>@lang('leave.department')</th>
                                        <th>@lang('leave.casual_leave')</th>
                                        <th>@lang('leave.privilege_leave')</th>
                                        <th>@lang('leave.sick_leave')</th>
                                        <th>@lang('leave.rh_leave')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($EmployeeLeavesList as $EmployeeLeaves)
                                    @php
                                        $employee = $EmployeeLeaves->employee ? $EmployeeLeaves->employee : new \App\Model\Employee;
                                    @endphp
                                        <tr>
                                            <td>{{ ++$sl }}</td>
                                            <td>{{ $employee->finger_id ?? $NA }}</td>
                                            <td>{{ $employee->fullname() ?? $NA }}</td>
                                            <td>{{ $employee->department_disp() ?? $NA }}</td>
                                            <td>{{ truncateNum($EmployeeLeaves->casual_leave) }}</td>
                                            <td>{{ truncateNum($EmployeeLeaves->privilege_leave) }}</td>
                                            <td>{{ truncateNum($EmployeeLeaves->sick_leave) }}</td>
                                            <td>{{ $employee->rh_balance() ?? $NA }}</td>
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
