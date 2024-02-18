@extends('admin.master')
@section('content')

@section('title')
@if (isset($editModeData))
@lang('common.edit_policy')
@else
@lang('common.add_policy')
@endif
@endsection


<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

        @if (session('logged_session_data.role_id') === 1)
        <div class="col-lg-8 col-sm-8 col-md-8 col-xs-12">
            <a href="{{ route('companyPolicy.index') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-list-ul" aria-hidden="true"></i> @lang('common.view_policy')</a>
        </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (isset($editModeData))
                        {{ Form::model($editModeData, ['route' => ['companyPolicy.update', $editModeData->company_policy_id], 'method' => 'PUT', 'files' => 'true', 'id' => 'PolicyForm', 'class' => 'form-horizontal']) }}
                        @else
                        {{ Form::open(['route' => ['companyPolicy.store'], 'method' => 'POST', 'files' => 'true', 'id' => 'PolicyForm', 'class' => 'form-horizontal']) }}
                        @endif
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-offset-2 col-md-6">
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
                                        <i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-body">

                                <div class="form-group">

                                    <div class="col-md-6">
                                        <label for="exampleInput">@lang('common.policy_type')<span class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-building-o"></i></span>
                                            {{ Form::select('policy_type', policyList(), Input::old('policy_type'), ['id' => 'policy_type', 'class' => 'form-control policy_type required select2']) }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="exampleInput">@lang('common.branch')<span class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-building-o"></i></span>
                                            {{ Form::select('branch_id', branches(), Input::old('branch_id'), ['id' => 'branch_id', 'class' => 'form-control branch_id required select2']) }}
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group">

                                    <div class="col-md-6">
                                        <label for="exampleInput">@lang('common.title')<span class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-header"></i></span>
                                            {!! Form::text(
                                            'title',
                                            Input::old('title'),
                                            $attributes = ['class' => 'form-control required title', 'id' => 'title', 'placeholder' => 'Title'],
                                            ) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="exampleInput">@lang('common.file')
                                            (JPG,JPEG,PNG,PDF,XLSX,DOC,DOCX,PPT,PPTX)<span class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-files-o"></i></span>
                                            {!! Form::file(
                                            'file',
                                            $attributes = [
                                            'class' => 'form-control file',
                                            'accept' => 'image/png, image/jpeg,image/jpg,.pdf,.doc,.docx,.ppt,.pptx',
                                            ],
                                            ) !!}
                                        </div>
                                    </div>

                                </div>

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-md-12">
                                            @if (isset($editModeData))
                                            <button type="submit" class="btn btn-info btn_style"><i class="fa fa-pencil"></i> @lang('common.update')</button>
                                            @else
                                            <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i> @lang('common.save')</button>
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
    $('.policy_type').change(function() {
        var type = $(this).val();

        // if (type == 1) {
        //     $('.branch_id').attr('disabled', true)
        // } else {
        //     $('.branch_id').attr('disabled', false)
        // }
    })
</script>
@endsection