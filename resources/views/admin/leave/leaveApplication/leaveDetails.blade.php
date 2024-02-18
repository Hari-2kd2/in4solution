@extends('admin.master')
@section('content')
@section('title', 'Requested Application Details')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> Dashboard</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>Application Details</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="box-title">Employee Leave Application Details</h3>
                                <hr>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div hidden>
                                                @if (isset($leaveApplicationData->employee->photo) && $leaveApplicationData->employee->photo != '')
                                                    <img style="width: 60px;margin: 0 auto"
                                                        class="profile-user-img img-responsive img-circle"
                                                        src="{!! asset('uploads/employeePhoto/' . $leaveApplicationData->employee->photo) !!}" alt="User profile picture">
                                                @else
                                                    <img style="width: 60px;margin: 0 auto"
                                                        class="profile-user-img img-responsive img-circle"
                                                        src="{!! asset('admin_assets/img/default.png') !!}" alt="User profile picture">
                                                @endif
                                            </div>
                                            <p>@lang('employee.name'):
                                                <b>{{ $leaveApplicationData->employee->fullname() }}</b>
                                            </p>
                                            <p>@lang('employee.emp_code'): <b>{{ $leaveApplicationData->employee->emp_code }}</b>
                                            </p>
                                            <p>@lang('employee.supervisor_short'):
                                                <b>{{ $leaveApplicationData->employee->supervisorDetail() }}</b>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>@lang('common.designation'):
                                                <b>{{ $leaveApplicationData->employee->designation_disp() }}</b>
                                            </p>
                                            <p>@lang('common.department'):
                                                <b>{{ $leaveApplicationData->employee->department_disp() }}</b>
                                            </p>
                                            <p>@lang('employee.phone'):
                                                <b>{{ $leaveApplicationData->employee->phone ?? '-' }}</b>
                                            </p>

                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive" hidden>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th style="margin: 0;padding:4px">Leave Type</th>
                                                <th style="margin: 0;padding:4px">Total Days</th>
                                                <th style="margin: 0;padding:4px">Leave Taken</th>
                                                <th style="margin: 0;padding:4px">Avaliable Days</th>
                                            </tr>
                                        </thead>

                                        @foreach ($leaveBalanceArr as $leave)
                                            <tbody>
                                                <tr>
                                                    <td style="margin: 0;padding:4px">{{ $leave['leaveType'] ?? 0 }}
                                                        {{ ' days' }}
                                                    </td>
                                                    <td style="margin: 0;padding:4px">
                                                        {{ $leave['totalDays'] != '' ? $leave['totalDays'] . ' days' : '' }}
                                                    </td>
                                                    <td style="margin: 0;padding:4px">
                                                        {{ $leave['leaveTaken'] ? $leave['leaveTaken'] . ' days' : ' ' }}
                                                    </td>
                                                    <td style="margin: 0;padding:4px">
                                                        {{ $leave['leaveBalance'] != '' ? $leave['leaveBalance'] . ' days' : '' }}
                                                    </td>

                                                </tr>
                                            </tbody>
                                        @endforeach
                                    </table>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label class="col-md-6 col-sm-6 ">Leave Type :</label>
                                    <p class="col-md-6 col-sm-6">
                                        {{ $leaveApplicationData->leaveType->leave_type_name }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-6 col-sm-6">Applied On :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($leaveApplicationData->application_date)
                                            {{ dateConvertDBtoForm($leaveApplicationData->application_date) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-6 col-sm-6 ">Period :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($leaveApplicationData->application_date)
                                            {{ dateConvertDBtoForm($leaveApplicationData->application_from_date) }}
                                        @endif
                                        {{ ' - ' }}
                                        @if ($leaveApplicationData->application_date)
                                            {{ dateConvertDBtoForm($leaveApplicationData->application_to_date) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-6 col-sm-6 ">Number of days :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($leaveApplicationData->application_date)
                                            {{ $leaveApplicationData->number_of_day }}
                                        @endif
                                        @if ($leaveApplicationData->medical_file)
                                            <br /><small><a style="padding:5px" target="_blank" class="badge-info"
                                                    href="{!! asset('uploads/employeeMedicalFile/' . $leaveApplicationData->medical_file) !!}"><b><i class="fa fa-paperclip"></i>
                                                        @lang('employee.medical_certificate') </b></a></small>
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-6 col-sm-6">Purpose :</label>
                                    <p class="col-md-6 col-sm-6">
                                        {{ $leaveApplicationData->purpose ?? '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3 class="box-title">Leave Approval</h3>
                                <hr>
                                {{ Form::open(['route' => ['requestedApplication.update', $leaveApplicationData->leave_application_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'leaveApproveOrRejectForm']) }}

                                <div class="form-group">
                                    <label class="col-sm-4 ">From Date :</label>
                                    <p class="col-sm-8"><input type="text" readonly class="form-control"
                                            value="@if (isset($leaveApplicationData->application_date)) {{ dateConvertDBtoForm($leaveApplicationData->application_from_date) }} @endif">
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 ">To Date :</label>
                                    <p class="col-sm-8"><input type="text" readonly class="form-control"
                                            value="@if (isset($leaveApplicationData->application_to_date)) {{ dateConvertDBtoForm($leaveApplicationData->application_to_date) }} @endif">
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 ">Number of days :</label>
                                    <p class="col-sm-8"> <input type="text" class="form-control"
                                            value="@if (isset($leaveApplicationData->application_date)) {{ $leaveApplicationData->number_of_day }} @endif"
                                            readonly></p>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4">Remarks :</label>
                                    <p class="col-sm-8">
                                        <textarea class="form-control remarks" cols="10" rows="6" name="remarks" required
                                            placeholder="Enter remarks....."
                                            value="@if (isset($leaveApplicationData->remarks)) {{ $leaveApplicationData->remarks }} @endif"></textarea>
                                    </p>
                                </div>
                                @php
                                    // dd($leaveApplicationData);
                                @endphp
                                <div class="form-group">
                                    <label class="col-sm-4"></label>
                                    <div class="col-sm-8">
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForLeave" data-status="2"
                                            data-leave_application_id={{ $leaveApplicationData->leave_application_id }}>Approve</button>
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-danger btn_style supervisorRemarksForLeave" data-status="3"
                                            data-leave_application_id={{ $leaveApplicationData->leave_application_id }}>
                                            Reject</button>
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForLeave" data-status="4"
                                            data-leave_application_id={{ $leaveApplicationData->leave_application_id }}>
                                            Pass</button>
                                    </div>
                                </div>
                                {{ Form::close() }}

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
<link href="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/css/site.css') !!}" rel="stylesheet" type="text/css" />
<script src="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/scripts/jquery.bootstrap.newsbox.min.js') !!}"></script>

<script type="text/javascript">
    $(document).on('click', '.supervisorRemarksForLeave', function() {
        var actionTo = "{{ URL::to('approveOrRejectLeaveApplication') }}";
        var leave_application_id = $(this).attr('data-leave_application_id');
        var remarks = $('.remarks').val();
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
                            remarks: remarks,
                            _token: token
                        },
                        success: function(data) {
                            data = data.trim().toLowerCase();
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "Leave application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                            window.location.href =
                                                "{{ route('requestedApplication.index') }}";
                                        }
                                    });

                            } else {
                                if (data == 'pass') {
                                    swal({
                                            title: "Passed!",
                                            text: "Leave application Passed.",
                                            type: "success"
                                        },
                                        function(isConfirm) {
                                            if (isConfirm) {
                                                $('.' + leave_application_id).fadeOut();
                                                window.location.href =
                                                    "{{ route('requestedApplication.index') }}";
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
                                                window.location.href =
                                                    "{{ route('requestedApplication.index') }}";
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
