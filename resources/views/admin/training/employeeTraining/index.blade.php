@extends('admin.master')
@section('content')
@section('title')
@lang('training.employee_training_list')
@endsection
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('trainingInfo.create') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-plus-circle" aria-hidden="true"></i> @lang('training.add_employee_training')</a>
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
                            <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                        </div>
                        @endif
                        @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('training.training_detail')</th>
                                        <th>@lang('training.video')</th>
                                        <th>@lang('training.file')</th>
                                        <th>@lang('training.trainees')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}

                                    @foreach ($results as $value)
                                    @php
                                    $trainingEmployee = $employee
                                    ->filter(function ($q) use ($value) {
                                    return in_array($q->employee_id, json_decode($value->employee_id));
                                    })
                                    ->values()
                                    ->pluck('last_name', 'first_name');
                                    $i = $str = null;
                                    foreach ($trainingEmployee as $firstName => $lastName) {
                                    $i++;
                                    $str .= trim($firstName . ' ' . $lastName);
                                    $str .= $trainingEmployee->count() == $i ? '. ' : ', ';
                                    }
                                    @endphp
                                    @if (in_array(session('logged_session_data.employee_id'), json_decode($value->employee_id)) ||
                                    json_decode($value->employee_id) == null ||
                                    session('logged_session_data.role_id') == 1 ||
                                    session('logged_session_data.role_id') == 2)
                                    <tr class="{!! $value->training_info_id !!}">
                                        <td style="width: 100px;">{!! ++$sl !!}</td>
                                        <td>
                                            @if (isset($value->trainingType->training_type_name))
                                            <p><b style="padding: 0 6px 0 0;">@lang('training.training_type'):</b>{!! $value->trainingType->training_type_name !!}</p>
                                            <p><b style="padding: 0 6px 0 0;">@lang('training.subject'):</b>{!! $value->subject !!}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->video_link)
                                            <a href="{{ $value->video_link }}"><span class="text-info">Watch Video</span></a>
                                            @else
                                            {{ '-' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->certificate)
                                            <a href="{{ asset('/uploads/employeeTrainingCertificate/') }}/{{ $value->certificate }}" download><span class="text-info">Download
                                                    File</span></a>
                                            @else
                                            {{ '-' }}
                                            @endif

                                        </td>
                                        <td>
                                            {!! wordwrap(ucwords(strtolower($str)), 100, "<br>\n") !!}
                                        </td>
                                        <td style="width: 100px;">
                                            <a title="View Details" href="{{ route('trainingInfo.show', $value->training_info_id) }}" class="btn btn-primary btn-xs btnColor">
                                                <i class="glyphicon glyphicon-th-large" aria-hidden="true"></i>
                                            </a>
                                            @if (session('logged_session_data.role_id') == 1)
                                            <a href="{!! route('trainingInfo.edit', $value->training_info_id) !!}" class="btn btn-success btn-xs btnColor">
                                                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                            </a>
                                            <a href="{!! route('trainingInfo.delete', $value->training_info_id) !!}" data-token="{!! csrf_token() !!}" data-id="{!! $value->training_info_id !!}" class="delete btn btn-danger btn-xs deleteBtn btnColor"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
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