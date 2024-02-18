@extends('admin.master')
@section('content')
@section('title')
    @if (isset($editModeData))
        @lang('salary.edit_settings')
    @else
        @lang('salary.add_settings')
    @endif
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>Professional Tax Settings</li>

            </ol>
        </div>

    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>Professional Tax Settings</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (isset($editModeData))
                            {{ Form::model($editModeData, ['route' => ['ProfessionalTax.settingsupdate', 'id' => $editModeData->pt_id], 'method' => 'PUT', 'files' => 'true', 'class' => 'form-horizontal']) }}
                        @else
                            {{ Form::open(['route' => 'ProfessionalTax.settingsstore', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']) }}
                        @endif
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-offset-2 col-md-6">
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
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="control-label col-md-6">Months<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            @php
                                                $expl = [];
                                                $mnth = [1 => 'January', 2 => 'Feburary', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'Auguest', 9 => 'Septemper', 10 => 'October', 11 => 'November', 12 => 'December'];
                                                if (isset($_GET['id'])) {
                                                    $p_tax = \App\Model\ProfessionalTax::find($_GET['id']);
                                                    $expl = explode(',', $p_tax->months);
                                                }
                                            @endphp
                                            {!! Form::select('months[]', $mnth, Input::old('months') ? Input::old('months') : $expl, [
                                                'class' => 'form-control months select2 required',
                                                'multiple' => true,
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6">Amount<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            @php
                                                $amount = '';
                                                if (isset($editModeData->amount)) {
                                                    $amount = $editModeData->amount;
                                                }
                                            @endphp
                                            {!! Form::text(
                                                'amount',
                                                Input::old('amount') ? Input::old('amount') : $amount,
                                                $attributes = ['class' => 'form-control required amount', 'id' => 'amount'],
                                            ) !!}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-offset-6 col-md-8">
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
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
