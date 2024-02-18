@extends('admin.master')
@section('content')
@section('title')
    @lang('training.employee_training_report')
@endsection
<style>
    .employeeName {
        position: relative;
    }

    #employee_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
        width: 100%;
        height: 100%;
    }
</style>
<script>
    jQuery(function() {
        $("#report").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'employeeTrainingReport.employeeTrainingReport', 'id' => 'report']) }}
                                <div class="col-md-3"></div>
                                <div class="col-md-4">
                                    <div class="form-group employeeName">
                                        <label class="control-label" for="email">@lang('common.employee')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" required
                                            name="employee_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($employee_id)) @if ($employee_id == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif>{{ $value->first_name }}
                                                    {{ $value->last_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        @if (count($results) > 0 && $results != '')
                            <h4 class="text-right" style="display: none">
                                <a class="btn btn-success" style="color: #fff"
                                    href="{{ URL('downloadTrainingReport/?employee_id=' . $employee_id) }}"><i
                                        class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download') PDF</a>
                            </h4>
                        @endif
                        @if ($results != '')
                            <div class="table-responsive">
                                <table id="myTable" class="table table-bordered">
                                    <thead class="tr_header">
                                        <tr>
                                            <th style="width:50px;">@lang('common.serial')</th>
                                            <th>@lang('employee.name')</th>
                                            <th>@lang('employee.employee_id')</th>
                                            <th>@lang('training.training_type')</th>
                                            {{-- <th>@lang('training.training_duration')</th> --}}
                                            <th>@lang('training.link')</th>
                                            <th>@lang('common.read_at')</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{ $sl = null }}
                                        @foreach ($results as $value)
                                            @if ($value->employee && $value->trainingInfo)
                                                <tr>
                                                    <td>{{ ++$sl }}</td>
                                                    <td>{{ $value->employee->first_name }}
                                                    </td>
                                                    <td>{{ $value->employee->detailname() }}
                                                    </td>
                                                    <td>{{ $value->trainingInfo->trainingType->training_type_name }}
                                                    </td>
                                                    {{-- @if ($value->trainingInfo->training_duration != '')
                                                            <td>{{ date('H:i', strtotime($value->trainingInfo->training_duration)) }}
                                                            </td>
                                                        @else
                                                            <td>--</td>
                                                        @endif --}}
                                                    <td><a style='color: #000'
                                                            href="{{ $value->trainingInfo->video_link }}"
                                                            target="_blank" rel="noopener noreferrer">
                                                            {{ $value->trainingInfo->video_link }}</a>
                                                    </td>
                                                    <td>
                                                        @php
                                                            echo "<b style='color: green'><i class='cr-icon glyphicon glyphicon-ok'></i>" . '<span style="padding:0 12px;">' . date('d M Y h:i A', strtotime($value->trainingInfo->created_at)) . '</span></b>';
                                                        @endphp
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
