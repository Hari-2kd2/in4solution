@extends('admin.master')
@section('content')
@section('title', 'Requested Application Details')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
                            <div class="col-md-6">
                                <h3 class="box-title">Permission Approval</h3>
                                <hr>
                                {{ Form::open(['route' => ['requestedPermissionApplication.update', $leaveApplicationData->leave_permission_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'permissionApproveOrRejectForm']) }}

                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4 ">Requested Date :</label>
                                    <p class="col-sm-8"><input type="text" readonly class="form-control"
                                            value="@if (isset($leaveApplicationData->leave_permission_date)) {{ dateConvertDBtoForm($leaveApplicationData->leave_permission_date) }} @endif">
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4 ">Permission Duration :</label>
                                    <p class="col-sm-8"> <input type="text" class="form-control"
                                            value="@if (isset($leaveApplicationData->permission_duration)) {{ $leaveApplicationData->permission_duration }} @endif"
                                            readonly></p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4">Remarks :</label>
                                    <p class="col-sm-8">
                                        <textarea class="form-control head_remarks" cols="10" rows="6" name="head_remarks" required
                                            placeholder="Enter remarks....."
                                            value="@if (isset($leaveApplicationData->remarks)) {{ $leaveApplicationData->remarks }} @endif"></textarea>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4"></label>
                                    <p class="col-sm-8">
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForPermission"
                                            data-status="2"
                                            data-leave_permission_id={{ $leaveApplicationData->leave_permission_id }}>Approve</button>
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-danger btn_style supervisorRemarksForPermission"
                                            data-status="3"
                                            data-leave_permission_id={{ $leaveApplicationData->leave_permission_id }}>
                                            Reject</button>
                                        <button style="width: 80px;" type="button" name="status"
                                            class="btn btn-info btn_style supervisorRemarksForPermission"
                                            data-status="4"
                                            data-leave_permission_id={{ $leaveApplicationData->leave_permission_id }}>
                                            Pass</button>
                                    </p>
                                </div>
                                {{ Form::close() }}
                            </div>
                            <div class="col-md-6" style="margin: 6px 0;">
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p>@lang('employee.emp_code'): <b>{{ $leaveApplicationData->employee->emp_code ?? '' }}</b></p>
                                <p>@lang('employee.name'): <b>{{ $leaveApplicationData->employee->fullname() ?? '' }}</b>
                                </p>
                                <p>@lang('common.designation'):
                                    <b>{{ $leaveApplicationData->employee->designation_disp() ?? '' }}</b>
                                </p>
                                <p>@lang('common.department'):
                                    <b>{{ $leaveApplicationData->employee->department_disp() ?? '' }}</b>
                                </p>
                                <p>@lang('leave.purpose'):
                                    <b>{{ $leaveApplicationData->leave_permission_purpose ?? '' }}</b>
                                </p>
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
    $(document).on('click', '.supervisorRemarksForPermission', function() {

        var actionTo = "{{ URL::to('approveOrRejectPermissionApplication') }}";
        var leave_permission_id = $(this).attr('data-leave_permission_id');
        var status = $(this).attr('data-status');
        var head_remarks = $('.head_remarks').val();
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
                            head_remarks: head_remarks,
                            _token: token
                        },
                        success: function(data) {
                            data = data.trim().toLowerCase();
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
                                if (data == 'pass') {
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
