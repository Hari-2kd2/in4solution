@php
    use Illuminate\Support\Facades\DB;
    use App\Lib\Enumerations\LeaveStatus;
    use App\Model\LeaveApplication;

@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.requested_application')
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
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
                        @if (count($supervisorResults) > 0)
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-bordered table-hover ">
                                    <thead class="tr_header">
                                        <tr>
                                            <th>@lang('common.serial')</th>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('common.emp_code')</th>
                                            {{-- <th>@lang('common.supervisor')</th> --}}
                                            <th>@lang('leave.leave_type')</th>
                                            <th>@lang('leave.request_duration')</th>
                                            <th>@lang('leave.request_date')</th>
                                            <th>@lang('leave.number_of_day')</th>
                                            <th style="width: 300px;word-wrap: break-word;">@lang('leave.purpose')</th>
                                            <th>@lang('common.status')</th>
                                            <th>@lang('common.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {!! $sl = null !!}
                                        @foreach ($supervisorResults as $value)
                                            @php
                                                $value->employee = \App\Model\Employee::find($value->employee_id);
                                                $application = DB::table('leave_application')
                                                    ->where('leave_application_id', $value->leave_application_id)
                                                    ->first();
                                                $Employee = \App\Model\Employee::find($value->employee_id);
                                                $value->leaveType = \App\Model\LeaveType::find($value->leave_type_id);
                                                $LeaveApplication = LeaveApplication::findOrFail($value->leave_application_id);
                                            @endphp
                                            <tr>
                                                <td style="width: 50px;">{!! ++$sl !!}</td>
                                                <td>
                                                    @if (isset($value->employee->first_name))
                                                        {!! $value->employee->first_name !!}
                                                    @endif
                                                    @if (isset($value->employee->last_name))
                                                        {!! $value->employee->last_name !!}
                                                    @endif
                                                </td>
                                                <td>{{ isset($value->employee->emp_code) ? $value->employee->emp_code : '' }}
                                                </td>
                                                {{-- <td>{{ $Employee->supervisorDetail() }}</td> --}}
                                                <td>{{ $value->leaveType->leave_type_name ?? '' }}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                                <td>
                                                    {!! $value->number_of_day !!}
                                                    @if ($value->medical_file)
                                                        <br /><small><a style="padding:5px" target="_blank"
                                                                class="badge-info" href="{!! asset('uploads/employeeMedicalFile/' . $value->medical_file) !!}"><b><i
                                                                        class="fa fa-paperclip"></i> @lang('employee.medical_certificate')
                                                                </b></a></small>
                                                    @endif
                                                </td>
                                                <td>{!! $value->purpose !!}</td>
                                                <td style="width: 100px;">
                                                    <span
                                                        class="label label-{{ $LeaveApplication->leaveClass() }}">{{ $LeaveApplication->leaveStatus() }}</span>
                                                </td>

                                                <td>
                                                    @if ($application->status == 1)
                                                        <a href="{!! route('requestedApplication.viewDetails', $application->leave_application_id) !!}" title="View leave details!"
                                                            class="btn btn-info btn-md btnColor">
                                                            <i class="fa fa-arrow-circle-right"></i>
                                                        </a>
                                                    @else
                                                        <i class="btn btn-success btn-sm fa fa-check"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        @if (count($functionalSupervisorResults) > 0)
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-bordered table-hover ">
                                    <thead class="tr_header">
                                        <tr>
                                            <th>@lang('common.serial')</th>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('common.emp_code')</th>
                                            {{-- <th>@lang('common.supervisor')</th> --}}
                                            <th>@lang('leave.leave_type')</th>
                                            <th>@lang('leave.request_duration')</th>
                                            <th>@lang('leave.request_date')</th>
                                            <th>@lang('leave.number_of_day')</th>
                                            <th style="width: 300px;word-wrap: break-word;">@lang('leave.purpose')</th>
                                            <th >HOD Status</th>
                                            <th>@lang('common.status')</th>
                                            <th>@lang('common.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {!! $sl = null !!}
                                        @foreach ($functionalSupervisorResults as $value)
                                            @php
                                                $value->employee = \App\Model\Employee::find($value->employee_id);
                                                $application = DB::table('leave_application')
                                                    ->where('leave_application_id', $value->leave_application_id)
                                                    ->first();
                                                $Employee = \App\Model\Employee::find($value->employee_id);
                                                $value->leaveType = \App\Model\LeaveType::find($value->leave_type_id);
                                                $LeaveApplication = LeaveApplication::findOrFail($value->leave_application_id);
                                            @endphp
                                            <tr>
                                                <td style="width: 50px;">{!! ++$sl !!}</td>
                                                <td>
                                                    @if (isset($value->employee->first_name))
                                                        {!! $value->employee->first_name !!}
                                                    @endif
                                                    @if (isset($value->employee->last_name))
                                                        {!! $value->employee->last_name !!}
                                                    @endif
                                                </td>
                                                <td>{{ isset($value->employee->emp_code) ? $value->employee->emp_code : '' }}
                                                </td>
                                                {{-- <td>{{ $Employee->supervisorDetail() }}</td> --}}
                                                <td>{{ $value->leaveType->leave_type_name ?? '' }}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                                <td>
                                                    {!! $value->number_of_day !!}
                                                    @if ($value->medical_file)
                                                        <br /><small><a style="padding:5px" target="_blank"
                                                                class="badge-info" href="{!! asset('uploads/employeeMedicalFile/' . $value->medical_file) !!}"><b><i
                                                                        class="fa fa-paperclip"></i> @lang('employee.medical_certificate')
                                                                </b></a></small>
                                                    @endif
                                                </td>
                                                <td>{!! $value->purpose !!}</td>
                                                @if ($value->status == 1)
                                                    <td style="width: 100px;">
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    </td>
                                                @elseif($value->status == 2)
                                                    <td style="width: 100px;">
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    </td>
                                                @elseif($value->status == 4)
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">Passed</span>
                                                    </td>
                                                @else
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    </td>
                                                @endif
                                                @if ($value->functional_head_status == 1)
                                                    <td style="width: 100px;">
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    </td>
                                                @elseif($value->functional_head_status == 2)
                                                    <td style="width: 100px;">
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    </td>
                                                @else
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    </td>
                                                @endif

                                                <td>
                                                    @if ($application->functional_head_status == 1)
                                                        <a href="{!! route('requestedApplicationFunctionalHead.viewDetailsFunctionalHead', $application->leave_application_id) !!}" title="View leave details!"
                                                            class="btn btn-info btn-md btnColor">
                                                            <i class="fa fa-arrow-circle-right"></i>
                                                        </a>
                                                    @else
                                                        <i class="btn btn-success btn-sm fa fa-check"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        @if (count($AdminResult) > 0)
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-bordered table-hover ">
                                    <thead class="tr_header">
                                        <tr>
                                            <th>@lang('common.serial')</th>
                                            <th>@lang('common.employee_name')</th>
                                            <th>@lang('common.emp_code')</th>
                                            {{-- <th>@lang('common.supervisor')</th> --}}
                                            <th>@lang('leave.leave_type')</th>
                                            <th>@lang('leave.request_duration')</th>
                                            <th>@lang('leave.request_date')</th>
                                            <th>@lang('leave.number_of_day')</th>
                                            <th style="width: 300px;word-wrap: break-word;">@lang('leave.purpose')</th>
                                            <th style="">HOD Status</th>
                                            <th>Functional Head Status</th>
                                            <th>@lang('common.action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {!! $sl = null !!}
                                        @foreach ($AdminResult as $value)
                                            @php
                                                $value->employee = \App\Model\Employee::find($value->employee_id);
                                                $application = DB::table('leave_application')
                                                    ->where('leave_application_id', $value->leave_application_id)
                                                    ->first();
                                                $Employee = \App\Model\Employee::find($value->employee_id);
                                                $value->leaveType = \App\Model\LeaveType::find($value->leave_type_id);
                                                $LeaveApplication = LeaveApplication::findOrFail($value->leave_application_id);
                                            @endphp
                                            <tr>
                                                <td style="width: 50px;">{!! ++$sl !!}</td>
                                                <td>
                                                    @if (isset($value->employee->first_name))
                                                        {!! $value->employee->first_name !!}
                                                    @endif
                                                    @if (isset($value->employee->last_name))
                                                        {!! $value->employee->last_name !!}
                                                    @endif
                                                </td>
                                                <td>{{ isset($value->employee->emp_code) ? $value->employee->emp_code : '' }}
                                                </td>
                                                {{-- <td>{{ $Employee->supervisorDetail() }}</td> --}}
                                                <td>{{ $value->leaveType->leave_type_name ?? '' }}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</td>
                                                <td>{!! dateConvertDBtoForm($value->application_date) !!}</td>
                                                <td>
                                                    {!! $value->number_of_day !!}
                                                    @if ($value->medical_file)
                                                        <br /><small><a style="padding:5px" target="_blank"
                                                                class="badge-info" href="{!! asset('uploads/employeeMedicalFile/' . $value->medical_file) !!}"><b><i
                                                                        class="fa fa-paperclip"></i> @lang('employee.medical_certificate')
                                                                </b></a></small>
                                                    @endif
                                                </td>
                                                <td>{!! $value->purpose !!}</td>
                                                @if ($value->status == 1)
                                                    <td style="width: 100px;">
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    </td>
                                                @elseif($value->status == 2)
                                                    <td style="width: 100px;">
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    </td>
                                                @elseif($value->status == 4)
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">Passed</span>
                                                    </td>
                                                @else
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    </td>
                                                @endif
                                                @if ($value->functional_head_status == 1)
                                                    <td style="width: 100px;">
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    </td>
                                                @elseif($value->functional_head_status == 2)
                                                    <td style="width: 100px;">
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    </td>
                                                @else
                                                    <td style="width: 100px;">
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    </td>
                                                @endif

                                                <td>
                                                    #
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
