@extends('admin.master')
@section('content')
@section('title')
@if (isset($editModeData))
@lang('training.edit_employee_training')
@else
@lang('training.add_employee_training')
@endif
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
            <a href="{{ route('trainingInfo.index') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i class="fa fa-list-ul" aria-hidden="true"></i> @lang('training.view_employee_training') </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            @foreach ($errors->all() as $error)
                            <strong>{!! $error !!}</strong><br>
                            @endforeach
                        </div>
                        @endif
                        @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                        </div>
                        @endif
                        @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <strong>{{ session()->get('error') }}</strong>
                        </div>
                        @endif

                        @if (isset($editModeData))
                        {{ Form::model($editModeData, ['route' => ['trainingInfo.update', $editModeData->training_info_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'trainingInfoForm']) }}
                        @else
                        {{ Form::open(['route' => 'trainingInfo.store', 'enctype' => 'multipart/form-data', 'id' => 'trainingInfoForm']) }}
                        @endif
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.training_type')<span class="validateRq">*</span></label>
                                        {{ Form::select('training_type_id', $trainingTypeList, Input::old('training_type_id'), ['class' => 'form-control training_type_id required select2']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.subject')<span class="validateRq">*</span></label>
                                        {!! Form::text(
                                        'subject',
                                        Input::old('subject'),
                                        $attributes = [
                                        'class' => 'form-control required subject',
                                        'id' => 'subject',
                                        'placeholder' => __('training.subject'),
                                        ],
                                        ) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.video_link')</label>
                                        {!! Form::text(
                                        'video_link',
                                        Input::old('video_link'),
                                        $attributes = [
                                        'class' => 'form-control required video_link',
                                        'id' => 'video_link',
                                        'placeholder' => __('training.video_link'),
                                        ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.certificate')(JPG,JPEG,PNG,PDF)</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-files-o"></i></span>
                                            {!! Form::file(
                                            'certificate',
                                            $attributes = ['class' => 'form-control certificate', 'accept' => 'image/png, image/jpeg,image/jpg,.pdf'],
                                            ) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.training_duration') (hh:mm)</label>
                                        {!! Form::text(
                                        'training_duration',
                                        Input::old('training_duration'),
                                        $attributes = [
                                        'class' => 'form-control required training_duration timePicker',
                                        'id' => 'training_duration',
                                        'placeholder' => __('training.training_duration'),
                                        ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('training.description')</label>
                                        {!! Form::textarea(
                                        'description',
                                        Input::old('description'),
                                        $attributes = [
                                        'class' => 'form-control',
                                        'id' => 'description',
                                        'placeholder' => __('training.description'),
                                        'cols' => '30',
                                        'rows' => '1',
                                        ],
                                        ) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.name')<span class="validateRq">*</span></label>
                                        {{ Form::select('employee_id[]', $employeeList, isset($editModeData) ? json_decode($editModeData->employee_id) : Input::old('employee_id[]'), ['class' => 'form-control employee_id required select2', 'multiple' => 'multiple']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-6">
                                        @if (isset($editModeData))
                                        <button type="submit" class="btn btn-info btn_style"><i class="fa fa-pencil"></i>
                                            @lang('common.update')</button>
                                        @else
                                        <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i>
                                            @lang('common.save')</button>
                                        @endif
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