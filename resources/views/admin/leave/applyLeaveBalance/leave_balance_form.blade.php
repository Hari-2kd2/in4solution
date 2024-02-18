@extends('admin.master')

@section('title')
    @if (isset($editModeData))
        @lang('leave.edit_leave_balance')
    @else
        @lang('leave.add_leave_balance')
    @endif
@endsection

@section('content')
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
                    <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
                    <li>@yield('title')</li>
                </ol>
            </div>
            <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
                <a href="{{ route('leaveBalance.index') }}"
                    class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                        class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.view_leave_balance')</a>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.leave_balance_form')</div>
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
                                    <i
                                        class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                                </div>
                            @endif

                            @if (isset($editModeData))
                                {{ Form::model($editModeData, ['route' => ['leaveBalance.update', $editModeData->leave_balance_id], 'method' => 'PUT', 'files' => true, 'id' => 'leaveBalanceForm', 'class' => 'form-horizontal']) }}
                            @else
                                {{ Form::open(['route' => 'leaveBalance.store', 'id' => 'leaveBalanceForm', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) }}
                            @endif
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-offset-2 col-md-6">
                                        <!-- Move the error messages and success messages inside this div -->
                                        @if ($errors->any())
                                            <div class="alert alert-danger alert-dismissible" role="alert">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-label="Close"><span aria-hidden="true">×</span></button>
                                                @foreach ($errors->all() as $error)
                                                    <strong>{!! $error !!}</strong><br>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if (session()->has('success'))
                                            <div class="alert alert-success alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-hidden="true">×</button>
                                                <i
                                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                                            </div>
                                        @endif
                                        @if (session()->has('error'))
                                            <div class="alert alert-danger alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert"
                                                    aria-hidden="true">×</button>
                                                <i
                                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    @if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="control-label col-md-4">@lang('common.employee_name')<span
                                                        class="validateRq">*</span></label>
                                                <div class="col-md-8">
                                                    {{ Form::select('employee_id', $employeeList, old('employee_id'), ['class' => 'form-control employee_id select2 required', 'name' => 'employee_id']) }}

                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {!! Form::hidden('employee_id', isset($getEmployeeInfo) ? $getEmployeeInfo->employee_id : '', [
                                            'class' => 'employee_id',
                                            'name' => 'employee_id',
                                        ]) !!}
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label class="control-label col-md-4">@lang('common.employee_name')<span
                                                        class="validateRq">*</span></label>
                                                <div class="col-md-8">
                                                    {!! Form::text(
                                                        isset($editModeData)
                                                            ? $editModeData->employee->first_name . ' ' . $editModeData->employee->last_name
                                                            : (isset($getEmployeeInfo)
                                                                ? $getEmployeeInfo->first_name . ' ' . $getEmployeeInfo->last_name
                                                                : ''),
                                                        ['class' => 'form-control', 'readonly' => 'readonly', 'name' => 'employee_id'],
                                                    ) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="control-label col-md-4">@lang('leave.leave_type_name')<span
                                                    class="validateRq">*</span></label>
                                            <div class="col-md-8">
                                                {{ Form::select('leave_type_id', $leaveTypeList, old('leave_type_id'), [
                                                    'class' => 'form-control leave_type_id select2 required',
                                                    'name' => 'leave_type_id',
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label class="control-label col-md-4">@lang('leave.leave_balance')<span
                                                    class="validateRq">*</span></label>
                                            <div class="col-md-8">
                                                {!! Form::text('leave_balance', isset($editModeData) ? $editModeData->leave_balance : old('leave_balance'), [
                                                    'class' => 'form-control leave_balance',
                                                    'name' => 'leave_balance',
                                                    'placeholder' => __('leave.leave_balance'),
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-offset-4 col-md-8">
                                            @if (isset($editModeData))
                                                <button type="submit" class="btn btn-info btn_style"><i
                                                        class="fa fa-pencil"></i> @lang('common.update')</button>
                                            @else
                                                <button type="submit" class="btn btn-info btn_style"><i
                                                        class="fa fa-check"></i> @lang('common.save')</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('page_scripts')
    <script>
        jQuery(function($) {
            $('form').bind('submit', function() {
                $(this).find(':input').prop('disabled', false);
            });
        });
    </script>
@endsection
