@php
    use App\Model\LeaveApplication;
    use App\Lib\Enumerations\LeaveStatus;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.my_application_list')
@endsection
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('applyForLeave.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('leave.apply_for_leave')</a>
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
                        <div>
                            <table class="table table-hover table-bordered manage-u-table">
                                <thead>
                                    <tr class="tr_header">
                                        <th>#</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('leave.leave_detail')</th>
                                        <th>@lang('leave.request_duration')</th>
                                        <th>@lang('leave.remarks')</th>
                                        <th>@lang('common.status')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        @php
                                            $LeaveApplication = LeaveApplication::findOrFail($value->leave_application_id);
                                            // $LeaveApplication->webCancelBtn()
                                        @endphp
                                        <tr>
                                            <td style="width: 50px;">{!! ++$sl !!}</td>

                                            <td style="width: 150px;">
                                                @if (isset($value->employee))
                                                    <small>{!! $value->employee->detailname() !!}</small>
                                                @endif
                                            </td>

                                            <td style="width: 110px;">
                                                @if (isset($value->leaveType->leave_type_name))
                                                    <small><span style="padding: 0 2px;">Leave
                                                            Type:</span>{!! $value->leaveType->leave_type_name !!}</small> <br>
                                                @endif
                                                <small><span style="padding: 0 2px;">No of Days:</span>
                                                    {!! $value->number_of_day !!}</small><br>
                                                <small><span
                                                        style="padding: 0 2px;">Purpose:</span>{!! $value->purpose ?? '-' !!}</small>
                                                <br>
                                            </td>

                                            <td style="width: 180px;">
                                                <small>{!! dateConvertDBtoForm($value->application_from_date) !!} <b>to</b> {!! dateConvertDBtoForm($value->application_to_date) !!}</small>
                                                <br /><small class="text-muted">Application Date :
                                                    {!! dateConvertDBtoForm($value->application_date) !!}</small>
                                            </td>
                                            <td>
                                                <small>HOD:
                                                    @if ($value->approve_by || $value->reject_by)
                                                        <small>{{ wordwrap(ucwords(strtolower($value->remarks ?? '-')), 100, "<br>\n") }}</small>
                                                    @endif
                                                </small>
                                                <br>
                                                <small>FUNCTIONAL HEAD:
                                                    @if ($value->functional_head_approved_by || $value->functional_head_reject_by)
                                                        <small>{{ wordwrap(ucwords(strtolower($value->functional_head_remarks ?? '-')), 100, "<br>\n") }}</small>
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <small>HOD:
                                                    @switch($value->status)
                                                        @case(1)
                                                            <span class="label label-warning">@lang('common.pending')</span>
                                                        @break

                                                        @case(2)
                                                            <span class="label label-success">@lang('common.approved')</span>
                                                        @break

                                                        @case(3)
                                                            <span class="label label-danger">@lang('common.rejected')</span>
                                                        @break

                                                        @case(4)
                                                            <span class="label label-info">@lang('common.passed')</span>
                                                        @break

                                                        @case(5)
                                                            <span class="label label-danger">@lang('common.canceled')</span>
                                                        @break

                                                        @default
                                                    @endswitch
                                                </small>
                                                <br>
                                                <small>FUNCTIONAL HEAD:
                                                    @switch($value->functional_head_status)
                                                        @case(1)
                                                            <span class="label label-warning">@lang('common.pending')</span>
                                                        @break

                                                        @case(2)
                                                            <span class="label label-success">@lang('common.approved')</span>
                                                        @break

                                                        @case(3)
                                                            <span class="label label-danger">@lang('common.rejected')</span>
                                                        @break

                                                        @case(4)
                                                            <span class="label label-info">@lang('common.passed')</span>
                                                        @break

                                                        @case(5)
                                                            <span class="label label-danger">@lang('common.canceled')</span>
                                                        @break

                                                        @default
                                                    @endswitch
                                                </small>
                                            </td>

                                            <td class="text-center" style="width: 100px;">
                                                @if ($value->functional_head_status == 1 && $value->status == 1)
                                                    {!! $LeaveApplication->webCancelBtn() !!}
                                                @else
                                                    <button class="btn btn-xs btn-info"><i
                                                            class="fa fa-check"></i></button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="text-center">
                                {{ $results->links() }}
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
<script>
    $(document).ready(function() {
        $('.cancel-leave').click(function(e) {
            var id = $(this).data('id');
            var action = $(this).data('url');
            var prompt = $(this).data('prompt');
            swal({
                    title: "Are you sure?",
                    text: 'Are you sure want cancel the leave(s) ' + prompt + '?',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete it!",
                    closeOnConfirm: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            type: 'GET',
                            url: action,
                            dataType: 'json',
                            success: function(data, textStatus, request) {
                                slog('data=' + data);
                                if (data == id) {
                                    swal({
                                        title: 'Leave Cancellation Process',
                                        text: "Leave cancellation process is successful.",
                                        type: "success"
                                    });
                                } else {
                                    swal({
                                        title: 'Oops!',
                                        text: "Leave cancellation process something went wrong!",
                                        type: "error"
                                    });
                                }
                                setTimeout(() => {
                                    location.reload();
                                }, 5000);
                            }
                        }).always(function(dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
                            //
                        });
                        window.location.reload();
                    }
                });
        }); // end cancel-leave
    }); // end $(document).ready
</script>
@endsection
