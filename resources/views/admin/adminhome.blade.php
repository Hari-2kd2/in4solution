@php
use App\Model\EmployeeInOutData;
use App\Model\Employee;
@endphp

@extends('admin.master')
@section('content')
@section('title')
@lang('dashboard.dashboard')
@endsection
<style>
    .dash_image {
        width: 60px;
    }

    .my-custom-scrollbar {
        position: relative;
        height: 280px;
        overflow: auto;
    }

    .table-wrapper-scroll-y {
        display: block;
    }

    tbody {
        display: block;
        max-height: 300px;
        overflow: auto;
    }

    thead,
    tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    thead {
        width: calc(100%);
    }

    .table.dataTable.no-footer {
        border-bottom: none;
        margin-bottom: 6px;
    }


    .leaveApplication {
        overflow-x: hidden;
        height: 210px;
    }

    .noticeBord {
        overflow-x: hidden;
        height: 210px;
    }

    .preloader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        /* background: url('../images/timer.gif') 50% 50% no-repeat rgb(249, 249, 249); */
        opacity: .8;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .scroll-hide::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .scroll-hide {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    .analytics-info .list-inline {
        margin-bottom: 0 !important
    }

    .analytics-info .list-inline li {
        vertical-align: middle !important
    }

    .analytics-info .list-inline li span {
        font-size: 20px !important
    }

    .analytics-info .list-inline li i {
        font-size: 18px !important
    }

    .analytics-info .dash_image {
        height: 30px;
        width: 35px;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a>
                </li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title"> @lang('dashboard.total_employee') </h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/employee.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span class="counter text-primary">{{ $totalActiveEmployee }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.late_count')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span class="counter text-purple">{{ $lateCount }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_present')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/present.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-info"></i> <span class="counter text-info">{{ $totalAttendance }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_absent')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/absent.png') }}">
                    </li>
                    <li class="text-right"><a href="#"><i id="absentDetail" class="ti-arrow-down text-danger"></i></a>
                        <span class="counter text-danger">{{ $totalAbsent }}</span>
                    </li>
                </ul>

            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title"> @lang('dashboard.on_leave') </h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/employee.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span class="counter text-primary">{{ $leaveCount }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.new_joining')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span class="counter text-purple">{{ $newJoining }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.resigned')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/present.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-info"></i> <span class="counter text-info">{{ $totalResigned }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_device')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" src="{{ asset('admin_assets/img/absent.png') }}">
                    </li>
                    <li class="text-right"><a href="#"><i id="absentDetail" class="ti-arrow-down text-purple"></i></a>
                        <span class="counter text-purple">{{ $totalDevice }}</span>
                    </li>
                </ul>

            </div>
        </div>

    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success alert-block alert-dismissable">
        <button type="button" class="close" data-dismiss="alert">x</button>
        <strong>{{ $message }}</strong>
    </div>
    @endif
    @if ($message = Session::get('error'))
    <div class="alert alert-danger alert-block alert-dismissable">
        <button type="button" class="close" data-dismiss="alert">x</button>
        <strong>{{ $message }}</strong>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12 col-lg-12 col-sm-12">
            <div class="panel">
                <div class="panel-heading">
                    <h5 class="box-title" style="text-transform: uppercase;font-weight:500"><i class="mdi mdi-clipboard-text fa-fw"></i>
                        @lang('dashboard.today_attendance')</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="" class="table table-bordered table-hover manage-u-table">
                            <thead class="tr_header">
                                <tr>
                                    <th style="font-size:12px;">@lang('common.serial')</th>
                                    <th style="font-size:12px;">@lang('common.name')</th>
                                    <th style="font-size:12px;">@lang('common.id')</th>
                                    <th style="font-size:12px;">@lang('attendance.in_time')</th>
                                    <th style="font-size:12px;">@lang('attendance.out_time')</th>
                                    <th style="font-size:12px;">@lang('attendance.late_by')</th>
                                    <th style="font-size:12px;">@lang('attendance.working_time')</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!! $sl = null !!}
                                @forelse ($dailyAttendanceReport as $dept => $result)
                                <tr>
                                    <td colspan="7" class="text-left">
                                        <b>{{ $dept }}</b>
                                    </td>
                                </tr>
                                @foreach ($result as $key => $value)
                                @php
                                $zero = '-:-';
                                @endphp
                                <tr>
                                    <td style="font-size:12px;">{{ ++$sl }}</td>
                                    <td style="font-size:12px;" title="{{ $value->branch_name }}">
                                        {{ $value->fullName }}
                                    </td>
                                    <td style="font-size:12px;">{{ $value->finger_print_id }}
                                    </td>
                                    <td style="font-size:12px;">
                                        @php
                                        if ($value->in_time != '') {
                                        echo $value->in_time;
                                        } else {
                                        echo $zero;
                                        }
                                        @endphp
                                    </td>
                                    <td style="font-size:12px;">
                                        @php
                                        if ($value->out_time != '') {
                                        echo $value->out_time;
                                        } else {
                                        echo $zero;
                                        }
                                        @endphp
                                    </td>
                                    <td style="font-size:12px;">
                                        @php
                                        if ($value->late_by != null) {
                                        echo date('H:i', strtotime($value->late_by));
                                        } else {
                                        echo $zero;
                                        }
                                        @endphp
                                    </td>
                                    <td style="font-size:12px;">
                                        @php
                                        if ($value->working_time != null) {
                                        echo date('H:i', strtotime($value->working_time));
                                        } else {
                                        echo $zero;
                                        }
                                        @endphp
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p>No Data Found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-12 col-sm-12">
            <div class="panel">
                <div class="panel-heading">
                    <h5 class="box-title" style="text-transform: uppercase;font-weight:500"><i class="mdi mdi-clipboard-text fa-fw"></i>
                        @lang('dashboard.policies')</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="tr_header">
                                    <th>@lang('common.serial')</th>
                                    <th>@lang('common.title')</th>
                                    <th>@lang('common.policy_type')</th>
                                    <th>@lang('common.branch')</th>
                                    <th>@lang('common.file')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sl = null @endphp
                                @forelse ($policies as $value)
                                <tr class="{!! $value->company_policy_id !!}">
                                    <td>{!! ++$sl !!}</td>
                                    <td>{!! $value->title !!}</td>
                                    <td>{!! policyList($value->policy_type) !!}</td>
                                    <td>{!! isset($value->branch) ? $value->branch->branch_name : 'All Branch' !!}</td>
                                    <td>
                                        @if ($value->file)
                                        <a href="{{ asset('/uploads/employeePolicy/') }}/{{ $value->file }}" download><span class="text-info">Download
                                                File</span></a>
                                        @else
                                        {{ '-' }}
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p>No Data Found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        @if (count($profileRequest) > 0)
        <div class="col-md-6">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.profile_request') <span>({{ count($profileRequest) }})</span></h3>
                <hr>
                <div class="noticeBord">
                    @foreach ($profileRequest as $row)
                    <div class="comment-center p-t-10">
                        <div class="comment-body">
                            <div class="user-img"><i style="font-size: 31px" class="fa fa-flag-checkered text-info"></i>
                            </div>
                            <div class="mail-contnet">
                                <h5 class="text-danger">Profile Request</h5><span class="time">Request Date:
                                    {{ date(' d M Y h:i A', strtotime($row->created_at)) }}</span>
                                <br /><span class="mail-desc">
                                    @lang('employee.name'): {{ $row->first_name . ' ' . $row->last_name }} <br>
                                    @lang('employee.employee_id'): {{ $row->finger_id }}
                                </span>
                                <a href="{{ url('profile/' . $row->employee_id . '/view') }}" class="btn m-r-5 btn-rounded btn-outline btn-info">@lang('common.read_more')</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif


        @if (count($probationEndingEmployees) > 0)
        <div class="col-md-6">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.probation_period_notification') <span>({{ count($probationEndingEmployees) }})</span>
                </h3>
                <hr>
                <div class="noticeBord">
                    @foreach ($probationEndingEmployees as $row)
                    <div class="comment-center p-t-10">
                        <div class="comment-body">
                            @if ($row->photo != '')
                            <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $row->photo) !!}" alt="user" class="img-circle"></div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">
                                <h5 class="text-info">Date of Joining:</h5><span class="time">
                                    {{ date(' d M Y h:i A', strtotime($row->date_of_joining)) }} <br>
                                    {{ \Carbon\Carbon::parse($row->date_of_joining)->diffForHumans() }}</span>
                                <br />
                                <div style="margin:12px 0;color:gray">
                                    @lang('employee.name'): {{ $row->displayName() }} <br>
                                    @lang('employee.department'): {{ $row->department_disp() }} <br>
                                    @lang('employee.designation'): {{ $row->designation_disp() }} <br>
                                </div>
                                <a href="{{ url('permanent') }}" class="btn m-r-5 btn-rounded btn-outline btn-info">@lang('common.read_more')</a>
                                {{-- ?name=' . $row->first_name . '&department_id=' . $row->department_id . '&designation_id=' . $row->designation_id . '&role_id=' . $row->userName->role_id --}}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($leaveApplication) > 0)
        <div class="col-md-12 col-lg-6 col-sm-12">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.recent_leave_application') <span>({{ count($leaveApplication) }})</span></h3>
                <hr>
                <div class="leaveApplication">
                    @foreach ($leaveApplication as $leaveApplication)
                    <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                        <div class="comment-body">
                            @if ($leaveApplication->employee->photo != '')
                            <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">
                                @php
                                $d = strtotime($leaveApplication->created_at);
                                @endphp
                                <h5>{{ $leaveApplication->employee->first_name }}
                                    {{ $leaveApplication->employee->last_name }}
                                </h5><span class="time">{{ date(' d M Y h:i: a', $d) }}</span> <span class="label label-rouded label-info">PENDING</span>
                                <br /><span class="mail-desc" style="max-height: none">
                                    @lang('leave.leave_type') :
                                    {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                    @lang('leave.request_duration') :
                                    {{ dateConvertDBtoForm($leaveApplication->application_from_date) }} To
                                    {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                    @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }} <br>
                                    @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                                </span>

                                {{-- <a href="javacript:void(0)" data-status=2
                                            data-leave_application_id="{{ $leaveApplication->leave_application_id }}"
                                class="btn remarksForManagerLeave btn btn-rounded btn-success-custom btn-outline m-r-5"><i class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                                <a href="javacript:void(0)" data-status=3 data-leave_application_id="{{ $leaveApplication->leave_application_id }}" class="btn-rounded remarksForManagerLeave btn btn-danger btn-outline"><i class="ti-close text-danger m-r-5"></i> @lang('common.reject')</a> --}}

                                <button type="button" name="status" class="btn btn-info btn_style supervisorRemarksForLeave" data-status="2" data-leave_application_id={{ $leaveApplication->leave_application_id }}>Approve</button>
                                <button type="button" name="status" class="btn btn-danger btn_style supervisorRemarksForLeave" data-status="3" data-leave_application_id={{ $leaveApplication->leave_application_id }}>
                                    Reject</button>
                                <button type="button" name="status" class="btn btn-info btn_style supervisorRemarksForLeave" data-status="4" data-leave_application_id={{ $leaveApplication->leave_application_id }}>
                                    Pass</button>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if (count($functionalHeadApplication) > 0)
        <div class="col-md-12 col-lg-6 col-sm-12">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.recent_leave_application') <span>({{ count($functionalHeadApplication) }})</span>
                </h3>
                <hr>
                <div class="leaveApplication">
                    @foreach ($functionalHeadApplication as $leaveApplication)
                    <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                        <div class="comment-body">
                            @if ($leaveApplication->employee->photo != '')
                            <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">
                                @php
                                $d = strtotime($leaveApplication->created_at);
                                @endphp
                                <h5>{{ $leaveApplication->employee->first_name }}
                                    {{ $leaveApplication->employee->last_name }}
                                </h5><span class="time">{{ date(' d M Y h:i: a', $d) }}</span> <span class="label label-rouded label-info">PENDING</span>
                                <br /><span class="mail-desc" style="max-height: none">
                                    @lang('leave.leave_type') :
                                    {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                    @lang('leave.request_duration') :
                                    {{ dateConvertDBtoForm($leaveApplication->application_from_date) }} To
                                    {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                    @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }} <br>
                                    @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                                </span>

                                <button type="button" name="status" class="btn btn-info btn_style functionalHeadRemarksForLeave" data-status="2" data-leave_application_id={{ $leaveApplication->leave_application_id }}>Approve</button>
                                <button type="button" name="status" class="btn btn-danger btn_style functionalHeadRemarksForLeave" data-status="3" data-leave_application_id={{ $leaveApplication->leave_application_id }}>
                                    Reject</button>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($supervisorResultsPer) > 0)
        <div class="col-md-6">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.recent_permission_application') <span>({{ count($supervisorResultsPer) }})</span></h3>
                <hr>
                <div class="permissionApplication" style="max-height: 300px; overflow-y: auto;">
                    @foreach ($supervisorResultsPer as $permissionApplication)
                    <div class="comment-center p-t-10 {{ $permissionApplication->leave_permission_id }}">
                        <div class="comment-body">
                            @if ($permissionApplication->employee->photo != '')
                            <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $permissionApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">
                                @php
                                $d = strtotime($permissionApplication->created_at);
                                @endphp
                                <h5>{{ $permissionApplication->employee->first_name }}
                                    {{ $permissionApplication->employee->last_name }}
                                </h5><span class="time">{{ date('d M Y h:i: a', $d) }}</span>
                                <span class="label label-rouded label-info">PENDING</span>
                                <br /><span class="mail-desc" style="max-height: none">

                                    @lang('leave.request_duration') :
                                    {{ $permissionApplication->from_time }}
                                    To
                                    {{ $permissionApplication->to_time }}<br>
                                    @lang('leave.total_duration') :
                                    {{ $permissionApplication->permission_duration }}
                                    <br>
                                    @lang('leave.purpose') :
                                    {{ $permissionApplication->leave_permission_purpose }}
                                </span>
                                <button type="button" name="status" class="btn btn-info btn_style supervisorRemarksForPermission" data-status="2" data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>Approve</button>
                                <button type="button" name="status" class="btn btn-danger btn_style supervisorRemarksForPermission" data-status="3" data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
                                    Reject</button>
                                <button type="button" name="status" class="btn btn-info btn_style supervisorRemarksForPermission" data-status="4" data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
                                    Pass</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @if (count($functionalSupervisorResultsPer) > 0)
        <div class="col-md-6">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.recent_permission_application')
                    <span>({{ count($functionalSupervisorResultsPer) }})</span>
                </h3>
                <hr>
                <div class="permissionApplication" style="max-height: 300px; overflow-y: auto;">
                    @foreach ($functionalSupervisorResultsPer as $permissionApplication)
                    <div class="comment-center p-t-10 {{ $permissionApplication->leave_permission_id }}">
                        <div class="comment-body">
                            @if ($permissionApplication->employee->photo != '')
                            <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $permissionApplication->employee->photo) !!}" alt="user" class="img-circle"></div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">
                                @php
                                $d = strtotime($permissionApplication->created_at);
                                @endphp
                                <h5>{{ $permissionApplication->employee->first_name }}
                                    {{ $permissionApplication->employee->last_name }}
                                </h5><span class="time">{{ date('d M Y h:i: a', $d) }}</span>
                                <span class="label label-rouded label-info">PENDING</span>
                                <br /><span class="mail-desc" style="max-height: none">

                                    @lang('leave.request_duration') :
                                    {{ $permissionApplication->from_time }}
                                    To
                                    {{ $permissionApplication->to_time }}<br>
                                    @lang('leave.total_duration') :
                                    {{ $permissionApplication->permission_duration }}
                                    <br>
                                    @lang('leave.purpose') :
                                    {{ $permissionApplication->leave_permission_purpose }}
                                </span>
                                <button type="button" name="status" class="btn btn-info btn_style functionalHeadRemarksForPermission" data-status="2" data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>Approve</button>
                                <button type="button" name="status" class="btn btn-danger btn_style functionalHeadRemarksForPermission" data-status="3" data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
                                    Reject</button>
                                {{-- <button type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForLeave" data-status="2"
                                            data-leave_application_id={{ $permissionApplication->leave_permission_id }}>Approve</button>
                                <button type="button" name="status" class="btn btn-danger btn_style supervisorRemarksForLeave" data-status="3" data-leave_application_id={{ $permissionApplication->leave_permission_id }}>
                                    Reject</button>
                                <button type="button" name="status" class="btn btn-info btn_style supervisorRemarksForLeave" data-status="4" data-leave_application_id={{ $permissionApplication->leave_permission_id }}>
                                    Pass</button> --}}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if (count($upcoming_birtday) > 0)
        <div class="col-md-6">
            <div class="white-box">
                <h3 class="box-title">@lang('dashboard.upcoming_birthday') <span>({{ count($upcoming_birtday) }})</span></h3>
                <hr>
                <div class="leaveApplication">
                    @foreach ($upcoming_birtday as $employee_birthdate)
                    <div class="comment-center p-t-10">
                        <div class="comment-body">
                            @if ($employee_birthdate->photo != '')
                            <div class="user-img"> <img height="40" width="40" src="{!! asset('uploads/employeePhoto/' . $employee_birthdate->photo) !!}" alt="user" class="img-circle">
                            </div>
                            @else
                            <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user" class="img-circle"></div>
                            @endif
                            <div class="mail-contnet">

                                @php
                                $date_of_birth = $employee_birthdate->date_of_birth;
                                $separate_date = explode('-', $date_of_birth);

                                $date_current_year = date('Y') . '-' . $separate_date[1] . '-' . $separate_date[2];

                                $create_date = date_create($date_current_year);
                                @endphp

                                <h5>{{ $employee_birthdate->first_name }}
                                    {{ $employee_birthdate->last_name }}
                                    ({{ $employee_birthdate->emp_code . ($employee_birthdate->branch ? ', ' . $employee_birthdate->branch->branch_name : '') }})
                                </h5><span>@lang('dashboard.dob'):
                                    {{ date_format(date_create($employee_birthdate->date_of_birth), 'D dS F Y') }}</span>
                                <br />

                                <span class="mail-desc">
                                    @if ($date_current_year == date('Y-m-d'))
                                    <b>Today is
                                        @if ($employee_birthdate->gender == 'Male')
                                        His
                                        @else
                                        Her
                                        @endif
                                        Birtday Wish
                                        @if ($employee_birthdate->gender == 'Male')
                                        Him
                                        @else
                                        Her
                                        @endif
                                    </b>
                                    @else
                                    Wish
                                    @if ($employee_birthdate->gender == 'Male')
                                    Him
                                    @else
                                    Her
                                    @endif
                                    on {{ date_format($create_date, 'D dS F Y') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('page_scripts')
<script type="text/javascript">
    document.onreadystatechange = function() {
        switch (document.readyState) {
            case "loading":
                window.documentLoading = true;
                break;
            case "complete":
                window.documentLoading = false;
                break;
            default:
                window.documentLoading = false;
        }
    }

    function loading($bool) {
        // $("#preloaders").fadeOut(1000);
        if ($bool == true) {
            $.toast({
                heading: 'success',
                text: 'Processing Please Wait !',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 1
            });
            window.setTimeout(function() {
                location.reload()
            }, 3000);
        }
        $("#preloaders").fadeOut(1000);
    }
</script>

<link href="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/css/site.css') !!}" rel="stylesheet" type="text/css" />
<script src="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/scripts/jquery.bootstrap.newsbox.min.js') !!}"></script>
<script type="text/javascript">
    (function() {

        $(".demo1").bootstrapNews({
            newsPerPage: 2,
            autoplay: true,
            pauseOnHover: true,
            direction: 'up',
            newsTickerInterval: 4000,
            onToDo: function() {
                //console.log(this);
            }
        });

    })();
</script>
<script>
    $(function() {
        $('.toggle-class').change(function() {
            var status = $(this).prop('checked') == true ? 1 : 0;
            var id = $(this).data('id');
            var action = "{{ URL::to('admin/pushSwitch') }}";
            $.ajax({
                type: "GET",
                dataType: "json",
                url: action,
                data: {
                    'status': status,
                    'id': id,
                    // '_token': $('input[name=_token]').val()
                },
                success: function(data) {
                    console.log(data.success)
                }
            });
        })
    })

    $(document).on('click', '.supervisorRemarksForLeave', function() {

        var actionTo = "{{ URL::to('approveOrRejectLeaveApplication') }}";
        var leave_application_id = $(this).attr('data-leave_application_id');
        var status = $(this).attr('data-status');
        if (status == 2) {
            var statusText = "Are you want to approve Leave application?";
            var btnColor = "#2cabe3";
        } else {
            if (status == 3) {
                var statusText = "Are you want to reject Leave application?";
                var btnColor = "red";
            } else {
                var statusText = "Are you want to Pass Leave application?";
                var btnColor = "#2cabe3";
            }

        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_application_id: leave_application_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            data = data.trim().toLowerCase();
                            if (data == 'approve'.toLowerCase()) {
                                swal({
                                        title: "Approved!",
                                        text: "Leave application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                            location.reload();
                                        }
                                    });

                            } else {
                                if (data == 'pass'.toLowerCase()) {
                                    swal({
                                            title: "Passed!",
                                            text: "Leave application Passed.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_application_id).fadeOut();
                                                location.reload();
                                            }
                                        });
                                } else {
                                    swal({
                                            title: "Rejected!",
                                            text: "Leave application rejected.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_application_id).fadeOut();
                                                location.reload();
                                            }
                                        });
                                }

                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });


    $(document).on('click', '.functionalHeadRemarksForLeave', function() {

        var actionTo = "{{ URL::to('approveOrRejectFunctionalHeadLeaveApplication') }}";
        var leave_application_id = $(this).attr('data-leave_application_id');
        var status = $(this).attr('data-status');
        if (status == 2) {
            var statusText = "Are you want to approve Leave application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject Leave application?";
            var btnColor = "red";
        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_application_id: leave_application_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            data = data.trim().toLowerCase();
                            if (data == 'approve'.toLowerCase()) {
                                swal({
                                        title: "Approved!",
                                        text: "Leave application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                            location.reload();
                                        }
                                    });

                            } else {
                                swal({
                                        title: "Rejected!",
                                        text: "Leave application rejected.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                            location.reload();
                                        }
                                    });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });

    $(document).on('click', '.supervisorRemarksForPermission', function() {

        var actionTo = "{{ URL::to('approveOrRejectPermissionApplication') }}";
        var leave_permission_id = $(this).attr('data-leave_permission_id');
        var status = $(this).attr('data-status');
        // alert(status);
        if (status == 2) {
            var statusText = "Are you want to approve Leave application?";
            var btnColor = "#2cabe3";
        } else {
            if (status == 3) {
                var statusText = "Are you want to reject Leave application?";
                var btnColor = "red";
            } else {
                var statusText = "Are you want to Pass Leave application?";
                var btnColor = "#2cabe3";
            }

        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_permission_id: leave_permission_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            data = data.trim().toLowerCase();
                            if (data == 'approve'.toLowerCase()) {
                                swal({
                                        title: "Approved!",
                                        text: "Permission application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_permission_id).fadeOut();
                                            location.reload();
                                        }
                                    });

                            } else {
                                if (data == 'pass') {
                                    swal({
                                            title: "Passed!",
                                            text: "Permission application Passed.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_permission_id).fadeOut();
                                                location.reload();
                                            }
                                        });
                                } else {
                                    swal({
                                            title: "Rejected!",
                                            text: "Permission application rejected.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_permission_id).fadeOut();
                                                location.reload();
                                            }
                                        });
                                }

                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });
</script>

@if (auth()->user())
<script>
    function sendMarkRequest(id = null) {
        return $.ajax("{{ route('admin.markNotification') }}", {
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id
            }
        });
    }
    $(function() {
        $('.mark-as-read').click(function() {
            let request = sendMarkRequest($(this).data('id'));
            request.done(() => {
                $(this).parents('div.alert').remove();
            });
        });
        $('#mark-all').click(function() {
            let request = sendMarkRequest();
            request.done(() => {
                $('div.alert').remove();
            })
        });
    });
</script>
@endif
@endsection