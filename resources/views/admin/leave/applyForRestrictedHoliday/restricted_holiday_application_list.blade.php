@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.rh_application_form')
@endsection
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.rh_application_form')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered" id="myDataTable">
                                <thead >
                                     <tr>
                                        <th>#</th>
                                        <th>@lang('common.employee_name')</th>
                                        <th>@lang('common.emp_code')</th>
                                        <th>@lang('employee.supervisor')</th>
                                        <th>@lang('leave.application_date')</th>
                                        <th>@lang('leave.holiday_date')</th>
                                        <th>@lang('leave.holiday_name')</th>
                                        <th>@lang('leave.purpose')</th>
                                        <th>@lang('leave.response_date')</th>
                                        <th>@lang('common.status')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl=null !!}
                                    @foreach($RhApplicationList AS $RhApplication)
                                    @php
                                        $RhApplication = DB::table('restricted_holiday_application')->where('rh_application_id', $RhApplication->rh_application_id)->first();
                                        $RestrictedHoliday = DB::table('holiday_restricted')->where('holiday_id', $RhApplication->holiday_id)->first();
                                        $RhApplication->employee = \App\Model\Employee::find($RhApplication->employee_id);
                                    @endphp
                                        <tr>
                                            <td style="width: 100px;">{!! ++$sl !!}</td>
                                            <td>
                                                {!! $RhApplication->employee->first_name !!}
                                                {!! $RhApplication->employee->last_name !!}
                                            </td>
                                            <td>{!! $RhApplication->employee->emp_code !!}</td>
                                            <td>{!! $RhApplication->employee->supervisorDetail() !!}</td>
                                            <td>{!! dateConvertDBtoForm($RhApplication->application_date) !!}</td>
                                            <td>{!! dateConvertDBtoForm($RhApplication->holiday_date) !!}</td>
                                            <td>{!! $RestrictedHoliday->holiday_name ?? '' !!}</td>
                                            <td>{!! $RhApplication->purpose !!}</td>
                                            <td>{!! $RhApplication->status!=1 ? dateConvertDBtoForm($RhApplication->updated_at) : '' !!}</td>
                                            
                                            @if($RhApplication->status == 1)
                                                <td  style="width: 100px;"><span class="label label-warning">@lang('common.pending')</span></td>
                                            @elseif($RhApplication->status == 2)
                                                <td  style="width: 100px;"><span class="label label-success">@lang('common.approved')</span></td>
                                            @elseif($RhApplication->status == 3)
                                                <td  style="width: 100px;"><span class="label label-danger">@lang('common.rejected')</span></td>
                                            @elseif($RhApplication->status == 4)
                                                <td  style="width: 100px;"><span class="label label-warning">@lang('leave.auto_canceled')</span></td>
                                            @else
                                                <td  style="width: 100px;"></td>
                                            @endif
                                            <td>
                                                @if ($RhApplication->status == 1)
                                                    <a href="{!! route('requestedApplication.RhviewDetails', $RhApplication->rh_application_id) !!}" title="View leave details!"
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
                            <div class="text-center">
                                {{-- {{$RhApplicationList->links()}} --}}
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
        jQuery(function() {
            
        });
    </script>
@endsection
