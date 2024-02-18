@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.requested_permission')
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover ">
                                <thead class="tr_header">
                                    <tr>
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('leave.employee_name')</th>
                                        <th>@lang('leave.permission_detail')</th>
                                        <th>@lang('leave.purpose')</th>
                                        <th>@lang('leave.remarks')</th>
                                        <th>@lang('common.status')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        @php
                                            $value->employee = \App\Model\Employee::find($value->employee_id);
                                        @endphp
                                        <tr>
                                            <td style="width: 50px;">{!! ++$sl !!}</td>
                                            <td><small>{{ $value->employee->detailname() }}</small></td>
                                            <td>
                                                <small><span
                                                        style="padding: 0 2px;">Date:</span>{!! dateConvertDBtoForm($value->leave_permission_date) !!}</small>
                                                <br>
                                                <small><span
                                                        style="padding: 0 2px;">Duration:</span>{!! $value->permission_duration !!}</small>
                                            </td>
                                            <td>
                                                <small>{!! $value->leave_permission_purpose !!}</small>
                                            </td>
                                            <td>
                                                <small>HOD:
                                                    @if ($value->approve_by || $value->reject_by)
                                                        <small>{{ wordwrap(ucwords(strtolower($value->head_remarks ?? '-')), 100, "<br>\n") }}</small>
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
                                            <td>
                                                @if ($value->status == 1 && $value->employee->supervisor_id == session('logged_session_data.employee_id'))
                                                    <a href="{!! route('requestedPermissionApplication.viewDetails', $value->leave_permission_id) !!}" title="View Permission details!"
                                                        class="btn btn-info btn-xs btnColor">
                                                        <i class="fa fa-arrow-circle-right"></i>
                                                    </a>
                                                @elseif (
                                                    $value->functional_head_status == 1 &&
                                                        $value->employee->functional_head_id == session('logged_session_data.employee_id'))
                                                    <a href="{!! route(
                                                        'requestedPermissionApplicationFunctionalHead.viewDetailsPermissionFunctionalHead',
                                                        $value->leave_permission_id,
                                                    ) !!}" title="View Permission details!"
                                                        class="btn btn-info btn-xs btnColor">
                                                        <i class="fa fa-arrow-circle-right"></i>
                                                    </a>
                                                @else
                                                    <a class="btn btn-info btn-xs "><i
                                                            class="fa fa-check"></i></a>
                                                @endif
                                            </td>
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
