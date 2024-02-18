@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.permission_list')
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
            <a href="{{ route('applyForPermission.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('leave.apply_for_permission')</a>
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
                        <div class="">
                            <table class="table table-hover table-bordered manage-u-table">
                                <thead class="tr_header">
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('leave.request_permission_duration')</th>
                                        <th>@lang('leave.remarks')</th>
                                        <th>@lang('common.status')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        <tr>
                                            <td>{!! ++$sl !!}</td>
                                            <td>
                                                @if (isset($value->employee))
                                                    {!! $value->employee->fullname() !!}
                                                @endif
                                            </td>

                                            <td>
                                                @if (isset($value->leave_permission_date))
                                                    <small><span
                                                            style="padding:  0 2px;">@lang('leave.date'):</span>{!! $value->leave_permission_date !!}</small>
                                                    <br>
                                                @endif
                                                @if (isset($value->from_time) && isset($value->to_time))
                                                    <small><span
                                                            style="padding:  0 2px;">@lang('leave.permission_from_time'):</span>{!! date('H:i', strtotime($value->from_time)) !!},</small>
                                                    <small><span
                                                            style="padding:  0 2px;">@lang('leave.permission_to_time'):</span>{!! date('d-m-Y', strtotime($value->to_time)) !!}</small><br>
                                                @endif

                                                @if (isset($value->permission_duration))
                                                    <small><span
                                                            style="padding:  0 2px;">@lang('leave.request_duration'):</span>{!! $value->permission_duration !!}</small>
                                                @endif
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
                                                        <small>{{ wordwrap(ucwords(strtolower($value->remarks ?? '-')), 100, "<br>\n") }}</small>
                                                    @endif
                                                </small>
                                            </td>

                                            <td style="width: 100px;">
                                                <small>HOD:
                                                    @if ($value->status == 1)
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    @elseif ($value->status == 2)
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    @elseif ($value->status == 3)
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    @else
                                                        <span class="label label-info">@lang('common.passed')</span>
                                                    @endif
                                                </small>
                                                <br>
                                                <small>FUNCTIONAL HEAD:
                                                    @if ($value->functional_head_status == 1)
                                                        <span class="label label-warning">@lang('common.pending')</span>
                                                    @elseif ($value->functional_head_status == 2)
                                                        <span class="label label-success">@lang('common.approved')</span>
                                                    @elseif ($value->functional_head_status == 4)
                                                        <span class="label label-success">Passed</span>
                                                    @else
                                                        <span class="label label-danger">@lang('common.rejected')</span>
                                                    @endif
                                                </small>
                                            </td>

                                            <td class="text-center" style="width: 100px;">
                                                <button class="btn btn-xs btn-info"><i class="fa fa-check"></i></button>
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
