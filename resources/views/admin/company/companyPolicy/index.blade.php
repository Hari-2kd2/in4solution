@extends('admin.master')
@section('content')
@section('title')
    @lang('common.company_policy')
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

        @if (session('logged_session_data.role_id') === 1)
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <a href="{{ route('companyPolicy.create') }}"
                    class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                        class="fa fa-plus-circle" aria-hidden="true"></i> @lang('common.add_policy')</a>
            </div>
        @endif
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

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('common.title')</th>
                                        <th>@lang('common.policy_type')</th>
                                        <th>@lang('common.branch')</th>
                                        <th>@lang('common.file')</th>
                                        <th>@lang('common.updated_at')</th>
                                        @if (session('logged_session_data.role_id') === 1)
                                            <th>@lang('common.action')</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        <tr class="{!! $value->company_policy_id !!}">
                                            <td style="width: 200px;">{!! ++$sl !!}</td>
                                            <td>{!! $value->title !!}</td>
                                            <td>{!! policyList($value->policy_type) !!}</td>
                                            <td>{!! isset($value->branch) ? $value->branch->branch_name : 'All Branch' !!}</td>
                                            <td>
                                                @if ($value->file)
                                                    <a href="{{ asset('/uploads/employeePolicy/') }}/{{ $value->file }}"
                                                        download><span class="text-info">Download
                                                            File</span></a>
                                                @else
                                                    {{ '-' }}
                                                @endif
                                            </td>
                                            <td>{{ date('d/m/Y h:i A', strtotime($value->updated_at)) }}</td>

                                            @if (session('logged_session_data.role_id') === 1)
                                                <td style="width: 100px;">
                                                    <a href="{!! route('companyPolicy.edit', $value->company_policy_id) !!}"
                                                        class="btn btn-success btn-xs btnColor">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="{!! route('companyPolicy.delete', $value->company_policy_id) !!}"
                                                        data-token="{!! csrf_token() !!}"
                                                        data-id="{!! $value->company_policy_id !!}"
                                                        class="delete btn btn-danger btn-xs deleteBtn btnColor"><i
                                                            class="fa fa-trash-o" aria-hidden="true"></i></a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{-- @php
                            if (isset($result) && $result->file != '') {
                                $info = new SplFileInfo($result->file);
                                $extension = $info->getExtension();

                                if ($extension === 'png' || $extension === 'jpg' || $extension === 'jpeg' || $extension === 'PNG' || $extension === 'JPG' || $extension === 'JPEG') {
                                    echo '<img src="' . asset('uploads/employeePolicy/' . $result->file) . '" width="100%" >';
                                } else {
                                    echo '<embed src="' . asset('uploads/employeePolicy/' . $result->file) . '" width="100%" height="1200" />';
                                }
                            } else {
                                echo '<div class="row text-center"><p class="col-md-12">No Data Found</p></div>';
                            }
                        @endphp --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
