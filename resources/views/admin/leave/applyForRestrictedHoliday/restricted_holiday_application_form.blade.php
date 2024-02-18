@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.rh_application_form')
@endsection
<style>
    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
        background: none;
        color: red !important;
        cursor: default;
    }

    td {
        color: black !important;
    }
</style>

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
            <a href="{{ route('applyForRestrictedHoliday.index') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.view_rh_applicaiton')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.rh_application_form')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">×</span></button>
                                @foreach ($errors->all() as $error)
                                    <strong>{!! $error !!}</strong><br>
                                @endforeach
                            </div>
                        @endif
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

                        @php
                            $getTaken = $RhApplication->getTaken();
                        @endphp

                        @if ($getTaken>=\App\Components\Common::MAX_ALLOWED_RESTRICTED_HOLIDAY)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <p style="color: rgb(128, 0, 0); font-weight: bold">@lang('leave.max_reached_restricted_holiday'): {{ $getTaken }}</p>
                                </div>
                            </div>
                        </div>
                        @else
                        {{ Form::open(['route' => 'applyForRestrictedHoliday.store', 'enctype' => 'multipart/form-data', 'id' => 'leaveApplicationForm']) }}
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <p class="text-blue" style="color: rgb(128, 0, 0); font-weight: bold">@lang('leave.max_allowed_restricted_holiday_full'): {{ \App\Components\Common::MAX_ALLOWED_RESTRICTED_HOLIDAY }}</p>
                                            <p class="text-blue" style="color: green; font-weight: bold">@lang('leave.used_restricted_holiday'): {{ $getTaken }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exampleInput">@lang('leave.list_of_restricted_holiday')<span class="validateRq">*</span></label>
                                            <select name="holiday_id" id="holiday_id" class="form-control">
                                                <option value="">- @lang('leave.select_restricted_holiday') -</option>
                                                @foreach ($RestrictedHolidayList as $RestrictedHoliday)
                                                    @php
                                                        $today = date('Y-m-d');
                                                        $selected = $RestrictedHoliday->holiday_id==old('holiday_id') ? 'selected' : '';
                                                    @endphp
                                                    @if($today<$RestrictedHoliday->holiday_date)
                                                        <option style="background:green;color:white " value="{{ $RestrictedHoliday->holiday_id }}">{{ dateConvertDBtoForm($RestrictedHoliday->holiday_date) . ' -> ' . $RestrictedHoliday->holiday_name }}</option>
                                                    @else
                                                        <option disabled style="background:brown; color:white" {{ $selected }} value="">{{ dateConvertDBtoForm($RestrictedHoliday->holiday_date) . ' -> ' . $RestrictedHoliday->holiday_name }} - @lang('common.past_holiday')</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exampleInput">@lang('leave.purpose')<span
                                                    class="validateRq">*</span></label>
                                            {!! Form::textarea(
                                                'purpose',
                                                Input::old('purpose'),
                                                $attributes = [
                                                    'class' => 'form-control purpose',
                                                    'id' => 'purpose',
                                                    'placeholder' => __('leave.purpose'),
                                                    'cols' => '30',
                                                    'rows' => '3',
                                                ],
                                            ) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="submit" id="formSubmit" class="btn btn-info "><i
                                                class="fa fa-paper-plane"></i> @lang('leave.send_application')</button>
                                    </div>
                                </div>
                            </div>
                        {{ Form::close() }}
                        @endif
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
