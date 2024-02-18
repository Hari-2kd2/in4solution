@extends('admin.master')
@section('content')
@section('title', 'Requested RH Application Details')
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.view_rh_applicaiton')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="box-title">@lang('leave.rh_application_form_detail')</h3>
                                <hr>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><b>
                                                @if (isset($RhApplication->employee->first_name))
                                                    {{ $RhApplication->employee->first_name }}
                                                @endif
                                                @if (isset($RhApplication->employee->last_name))
                                                    {{ $RhApplication->employee->last_name }}
                                                @endif
                                            </b></p>
                                            <p>@lang('common.designation'): <b>
                                                    @if (isset($RhApplication->employee->designation->designation_name))
                                                        {{ $RhApplication->employee->designation->designation_name }}
                                                    @endif
                                                </b>
                                            </p>
                                            <p>@lang('common.department'): <b>
                                                    @if (isset($RhApplication->employee->department->department_name))
                                                        {{ $RhApplication->employee->department->department_name }}
                                                    @endif
                                                </b>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>@lang('employee.phone'): <b>
                                                    @if (isset($RhApplication->employee->phone))
                                                        {{ $RhApplication->employee->phone }}
                                                    @endif
                                                </b>
                                            </p>
                                            <p>@lang('employee.supervisor'): <b>
                                                {{ $RhApplication->employee->supervisorDetail() }}
                                                </b>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-md-6 col-sm-6">Applied On :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($RhApplication->application_date)
                                            {{ dateConvertDBtoForm($RhApplication->application_date) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-md-6 col-sm-6 ">@lang('leave.holiday_name') :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if (isset($RhApplication->RestrictedHoliday->holiday_name))
                                            {{ $RhApplication->RestrictedHoliday->holiday_name }}
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-md-6 col-sm-6 ">@lang('leave.holiday_date') :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($RhApplication->holiday_date)
                                            {{ dateConvertDBtoForm($RhApplication->holiday_date) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-md-6 col-sm-6">@lang('leave.purpose') :</label>
                                    <p class="col-md-6 col-sm-6">
                                        @if ($RhApplication->purpose)
                                            {{ $RhApplication->purpose }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h3 class="box-title">@lang('leave.restricted_holiday_detail')</h3>
                                <hr>
                                {{ Form::open(['route' => ['requestedApplication.RhUpdate', $RhApplication->rh_application_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'RhApproveOrRejectForm']) }}
                                @php
                                $getDetail = $RhApplication->getDetail();
                                @endphp
                                <div class="form-group">

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th colspan="2" style="border:1px solid #ccc;margin:0;padding:4px;width:50%;text-align:center">{{ $RhApplication->calanderYear->year_name }}</th>
                                                </tr>
                                                @foreach ($getDetail as $lab => $val)
                                                    <tr>
                                                        <td style="border:1px solid #ccc;margin:0;padding:4px;width:50%;text-align:right">@lang('leave.'.$lab)</td>
                                                        <td style="border:1px solid #ccc;margin:0;padding:4px;width:50%;padding-left:10px">{{ $val }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4">Remarks :</label>
                                    <p class="col-sm-8">
                                        <textarea class="form-control" cols="10" rows="6" name="remarks" required placeholder="Enter remarks....."
                                            value="@if (isset($RhApplication->remarks)) {{ $RhApplication->remarks }} @endif"></textarea>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-4"></label>
                                    <p class="col-sm-8">
                                        <button type="submit" name="status" class="btn btn-info btn_style"
                                            value="2">Approve</button>
                                        <button type="submit" name="status" class="btn btn-danger btn_style"
                                            value="3"> Reject</button>
                                    </p>
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
