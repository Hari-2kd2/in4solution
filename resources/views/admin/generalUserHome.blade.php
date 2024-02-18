@php
    use App\Model\EmployeeInOutData;
    use App\Lib\Enumerations\AttendanceStatus;
@endphp
@extends('admin.master')
@section('content')
@section('title', 'Dashboard')

@php
    if (count($attendanceData) >= 6) {
        $att6 = 'tbody {
            display: block;
            height: 320px;
            overflow: auto;
        }

        thead,
        tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        thead {
            width: calc(100% - 1em)
        }';
    }
    if (count($leaveApplication) >= 1) {
        $att3 = '.leaveApplication {
            overflow-x: hidden;
            height: 210px;
        }';
    }
    if (count($notice) >= 1) {
        $notG1 = '.noticeBord {
            overflow-x: hidden;
            height: 210px;
        }';
    }
    if (count($warning) >= 1) {
        $warG1 = '.warning {
            overflow-x: hidden;
            height: 210px;
        }';
    }
@endphp

<style>
    .box {
        position: relative;
        background: #ffffff;
        width: 100%;
    }

    {{ $att6 ?? '' }} {{ $att3 ?? '' }} {{ $notG1 ?? '' }} {{ $warG1 ?? '' }} .box-body {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
        padding: 10px;
    }

    .profile-user-img {
        margin: 0 auto;
        width: 100px;
        padding: 3px;
        border: 3px solid #d2d6de;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> Dashboard</a></li>
            </ol>

        </div>
    </div>
    <div class="row">

        @if ($ip_attendance_status == 1)
            <!-- employe attendance  -->
            @php
                $logged_user = employeeInfo();
            @endphp
            <div class="col-md-6" style="display: none">
                <div class="white-box">
                    <h3 class="box-title">Hey {!! $logged_user[0]->user_name !!} please Check in/out your attendance</h3>
                    <hr>
                    <div class="noticeBord">
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
                                <strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <form action="{{ route('ip.attendance') }}" method="POST">
                            {{ csrf_field() }}
                            <p>Your IP is {{ \Request::ip() }}</p>
                            <input type="hidden" name="employee_id" value="{{ $logged_user[0]->user_name }}">

                            <input type="hidden" name="ip_check_status" value="{{ $ip_check_status }}">
                            <input type="hidden" name="finger_id" value="{{ $logged_user[0]->finger_id }}">
                            @if ($count_user_login_today > 0)
                                <button class="btn btn-danger">
                                    <i class="fa fa-clock-o"> </i>
                                    Check Out
                                </button>
                            @else
                                <button class="btn btn-primary">
                                    <i class="fa fa-clock-o"> </i>
                                    Check In
                                </button>
                            @endif

                        </form>
                    </div>
                </div>
            </div>

            <!-- end attendance  -->
        @endif
        @if (count($leaveApplication) > 0)
            <div class="col-md-12 col-lg-6 col-sm-12">
                <div class="white-box">
                    <h3 class="box-title">@lang('dashboard.recent_leave_application')</h3>
                    <hr>
                    <div class="leaveApplication">
                        @foreach ($leaveApplication as $leaveApplication)
                            <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                                <div class="comment-body">
                                    @if ($leaveApplication->employee->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
                                    @endif
                                    <div class="mail-contnet">
                                        @php
                                            $d = strtotime($leaveApplication->created_at);
                                        @endphp
                                        <h5>{{ $leaveApplication->employee->first_name }}
                                            {{ $leaveApplication->employee->last_name }}</h5><span
                                            class="time">{{ date(' d M Y h:i: a', $d) }}</span> <span
                                            class="label label-rouded label-info">PENDING</span>
                                        <br /><span class="mail-desc" style="max-height: none">
                                            @lang('leave.leave_type') :
                                            {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                            @lang('leave.request_duration') :
                                            {{ dateConvertDBtoForm($leaveApplication->application_from_date) }} To
                                            {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                            @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }} <br>
                                            @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                                        </span>

                                        <button type="button" name="status"
                                            class="ti-check text-success m-r-5 supervisorRemarksForLeave"
                                            data-status="2"
                                            data-leave_application_id={{ $leaveApplication->leave_application_id }}>Approve</button>
                                        <button type="button" name="status"
                                            class="ti-check text-success m-r-5 supervisorRemarksForLeave"
                                            data-status="3"
                                            data-leave_application_id={{ $leaveApplication->leave_application_id }}>
                                            Reject</button>
                                        <button type="button" name="status"
                                            class="ti-check text-success m-r-5 supervisorRemarksForLeave"
                                            data-status="4"
                                            data-leave_application_id={{ $leaveApplication->leave_application_id }}>
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
                    <h3 class="box-title">@lang('dashboard.recent_leave_application')</h3>
                    <hr>
                    <div class="leaveApplication">
                        @foreach ($functionalHeadApplication as $leaveApplication)
                            <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                                <div class="comment-body">
                                    @if ($leaveApplication->employee->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
                                    @endif
                                    <div class="mail-contnet">
                                        @php
                                            $d = strtotime($leaveApplication->created_at);
                                        @endphp
                                        <h5>{{ $leaveApplication->employee->first_name }}
                                            {{ $leaveApplication->employee->last_name }}</h5><span
                                            class="time">{{ date(' d M Y h:i: a', $d) }}</span> <span
                                            class="label label-rouded label-info">PENDING</span>
                                        <br /><span class="mail-desc" style="max-height: none">
                                            @lang('leave.leave_type') :
                                            {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                            @lang('leave.request_duration') :
                                            {{ dateConvertDBtoForm($leaveApplication->application_from_date) }} To
                                            {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                            @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }} <br>
                                            @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                                        </span>

                                        <button type="button" name="status"
                                            class="btn btn-info btn_style functionalHeadRemarksForLeave" data-status="2"
                                            data-leave_application_id={{ $leaveApplication->leave_application_id }}>Approve</button>
                                        <button type="button" name="status"
                                            class="btn btn-danger btn_style functionalHeadRemarksForLeave"
                                            data-status="3"
                                            data-leave_application_id={{ $leaveApplication->leave_application_id }}>
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
                    <h3 class="box-title">@lang('dashboard.recent_permission_application')</h3>
                    <hr>
                    <div class="permissionApplication" style="max-height: 300px; overflow-y: auto;">
                        @foreach ($supervisorResultsPer as $permissionApplication)
                            <div class="comment-center p-t-10 {{ $permissionApplication->leave_permission_id }}">
                                <div class="comment-body">
                                    @if ($permissionApplication->employee->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $permissionApplication->employee->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
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
                                        <button type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForPermission"
                                            data-status="2"
                                            data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>Approve</button>
                                        <button type="button" name="status"
                                            class="btn btn-danger btn_style supervisorRemarksForPermission"
                                            data-status="3"
                                            data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
                                            Reject</button>
                                        <button type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForPermission"
                                            data-status="4"
                                            data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
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
                    <h3 class="box-title">@lang('dashboard.recent_permission_application')</h3>
                    <hr>
                    <div class="permissionApplication" style="max-height: 300px; overflow-y: auto;">
                        @foreach ($functionalSupervisorResultsPer as $permissionApplication)
                            <div class="comment-center p-t-10 {{ $permissionApplication->leave_permission_id }}">
                                <div class="comment-body">
                                    @if ($permissionApplication->employee->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $permissionApplication->employee->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
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

                                        <button type="button" name="status"
                                            class="btn btn-info btn_style functionalHeadRemarksForPermission"
                                            data-status="2"
                                            data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>Approve</button>
                                        <button type="button" name="status"
                                            class="btn btn-danger btn_style functionalHeadRemarksForPermission"
                                            data-status="3"
                                            data-leave_permission_id={{ $permissionApplication->leave_permission_id }}>
                                            Reject</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-6">
            <div class="panel">
                <div class="p-30">
                    <div class="row">
                        @if ($employeeInfo->photo != '')
                            <div class="col-xs-4 col-sm-4"><img src="{!! asset('uploads/employeePhoto/' . $employeeInfo->photo) !!}" alt="varun"
                                    class="img-circle img-responsive"></div>
                        @else
                            <div class="col-xs-4 col-sm-4"><img src="{!! asset('admin_assets/img/profilePic.png') !!}" alt="varun"
                                    class="img-circle img-responsive"></div>
                        @endif
                        <div class="col-xs-12 col-sm-8">
                            <h2 class="m-b-0">{{ $employeeInfo->first_name }} {{ $employeeInfo->last_name }}</h2>
                            <h4>{{ $employeeInfo->designation->designation_name ?? '-' }}</h4><a
                                href="{{ url('profile') }}" class="btn btn-rounded btn-success"><i
                                    class="ti-user m-r-5"></i> PROFILE </a>
                        </div>
                    </div>
                    <div class="row text-center m-t-30">
                        <div class="col-xs-6 b-r">
                            <h2>{{ $employeeTotalLeave->totalNumberOfDays }}</h2>
                            <h4>LEAVE CONSUME</h4>
                        </div>

                        <div class="col-xs-6">
                            <h2>{{ $employeeTotalAward->totalAward }}</h2>
                            <h4>AWARD</h4>
                        </div>
                    </div>
                </div>
                <hr class="m-t-10" />
            </div>
        </div>

        <div class="col-md-6">
            <div class="white-box">
                <h5 class="box-title">Functional Head Contacts</h5>
                <p>{{ isset($fn_head) ? $fn_head->first_name : '-' }}</p>
                <p>{{ isset($fn_head) ? $fn_head->phone : '-' }}</p>

                <hr>
                <h5 class="box-title">Department Head Contacts </h5>
                <p>{{ isset($dept_head) ? $dept_head->first_name : '-' }}</p>
                <p>{{ isset($dept_head) ? $dept_head->phone : '-' }}</p>
            </div>
        </div>

    </div>
</div>
@endsection


@section('page_scripts')
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
    $(document).on('click', '.functionalHeadRemarksForPermission', function() {

        var actionTo = "{{ URL::to('approveOrRejectFunctionalHeadPermissionApplication') }}";
        var leave_permission_id = $(this).attr('data-leave_permission_id');
        var status = $(this).attr('data-status');
        // alert(status);
        if (status == 2) {
            var statusText = "Are you want to approve Permission application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject Permission application?";
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
                            leave_permission_id: leave_permission_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            // alert(data);
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
                                            window.location.href =
                                                "{{ route('requestedPermissionApplication.index') }}";
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
                                            window.location.href =
                                                "{{ route('requestedPermissionApplication.index') }}";
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
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "Permission application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_permission_id).fadeOut();
                                            window.location.href =
                                                "{{ route('requestedPermissionApplication.index') }}";
                                        }
                                    });

                            } else {
                                if (data == 'pass'.toLowerCase()) {
                                    swal({
                                            title: "Passed!",
                                            text: "Permission application Passed.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_permission_id).fadeOut();
                                                window.location.href =
                                                    "{{ route('requestedPermissionApplication.index') }}";
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
                                                window.location.href =
                                                    "{{ route('requestedPermissionApplication.index') }}";
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
@endsection
