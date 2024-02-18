{{-- @php
use App\Model\EmployeeCategory;
@endphp --}}
@extends('admin.master')
@section('content')
@section('title')
    @lang('payroll_setup.manage_professionaltax')
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
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <a href="{{ route('ProfessionalTax.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> ADD PROFESSIONAL TAX</a>
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
                            <table id="myTable" class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>Months</th>
                                        <th>Amount</th>
                                        <th>Updated At</th>
                                        <th style="text-align: center;">@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        @php
                                            // $emp_cat=EmployeeCategory::where('empcategory_id',$value->category_id)->first();
                                            $months = explode(',', $value->months);
                                            $set = [];
                                            foreach ($months as $Data) {
                                                $date = '01-' . $Data . '-' . DATE('Y');
                                                $set[] = DATE('M', strtotime($date));
                                            }
                                        @endphp
                                        <tr class="{!! $value->ptax_id !!}">
                                            <td style="width: 100px;">{!! ++$sl !!}</td>
                                            <td style="width: 100px;">{!! implode(', ', $set) !!}</td>
                                            <td style="width: 100px;">{!! $value->amount !!}</td>
                                            <td style="width: 100px;">{!! DATE('d-m-Y h:i A', strtotime($value->updated_at)) !!}</td>
                                            <td style="width: 100px;">
                                                <a href="{!! route('ProfessionalTax.edit', ['id' => $value->pt_id]) !!}"
                                                    class="btn btn-success btn-xs btnColor">
                                                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                </a>
                                            </td>
                                        </tr>
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
