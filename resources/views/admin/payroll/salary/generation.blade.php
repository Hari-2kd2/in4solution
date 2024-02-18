@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.generation')
@endsection
<style>
    .departmentName {
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

    .custom-file-upload {
        color: grey !important;
        display: inline-block;
        padding: 4px 4px 4px 4px;
        cursor: pointer;
        font-weight: normal;
        /* border: 2px solid #3f729b; */
        border-radius: 6px;
        width: 500px;
        height: 32px;

    }

    input::file-selector-button {
        display: inline-block;
        font-weight: bolder;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        background: #41b3f9;
        /* background: #3f729b; */
        /* background: #7ace4c; */
        border-width: 1px;
        border: none;
        font-size: 12px;
        overflow: hidden;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        background-size: 12px 12px;
        padding: 4px 4px 4px 4px;
    }
</style>
{{-- @php
if (isset($results)) {
    dd($result);
}
@endphp --}}
<script>
    jQuery(function() {
        $("#monthlyDeduction").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
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
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
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

                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'salary.sheet', 'id' => 'monthlyDeduction', 'method' => 'GET']) }}
                                <!-- <div class="col-md-2"></div> -->
                                <div class="col-md-3">
                                    <div class="form-group departmentName">
                                        <label class="control-label" for="email">@lang('salary.employee_name')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" required
                                            name="employee_id">
                                            <option value="">---- @lang('common.all_employees') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($_REQUEST['employee_id'])) @if ($_REQUEST['employee_id'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value->first_name . ' ' . $value->last_name . '(' . $value->finger_id . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                  
                                <div class="col-md-2">
                                    <label for="exampleInput">@lang('common.month')<span class="validateRq">*</span></label>
                                    <div class="form-group">
                                        {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                                        {!! Form::text(
                                            'month',
                                            isset($month) ? $month : '',
                                            $attributes = [
                                                'class' => 'form-control required monthField',
                                                'id' => 'month',
                                                'placeholder' => __('common.month'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="exampleInput">@lang('common.from_date')<span class="validateRq">*</span></label>
                                    <div class="form-group">
                                        {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                                        {!! Form::text(
                                            'from_date',
                                            Input::old('from_date'),
                                            $attributes = [
                                                'class' => 'form-control from_date required',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('common.from_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="exampleInput">@lang('common.to_date')<span class="validateRq">*</span></label>
                                    <div class="form-group">
                                        {{-- <span class="input-group-addon"><i class="fa fa-calendar"></i></span> --}}
                                        {!! Form::text(
                                            'to_date',
                                            Input::old('to_date'),
                                            $attributes = [
                                                'class' => 'form-control to_date required',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('common.to_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px; width: fit-content;" class="btn btn-info text-sm"
                                            value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }} 
                              
                                {{ Form::open(['route' => 'salary.bulk-generate-preview', 'id' => 'monthlyDeduction', 'method' => 'GET']) }}
                                <input type="hidden" id="get_month" name="month" value="{{isset($_POST['month']) ? $_POST['month'] : ''}}">
                                <input type="hidden" id="get_from_date" name="from_date" value="{{isset($_POST['from_date']) ? $_POST['from_date'] : ''}} ">
                                <input type="hidden" id="get_to_date" name="to_date" value="{{isset($_POST['to_date']) ? $_POST['to_date'] : ''}} ">
                                <div class="col-md-2">
                                    <div class="form-group">
                                    <input type="submit" id="filter"
                                            style="margin-top: 25px; width: fit-content;" class="btn btn-info text-sm"
                                            value="Generate Salary">
                                        <!-- <input id="bulk-generate-preview" style="margin-top: 25px; width: 100%;"
                                            class="btn btn-info text-sm" value="Generate Salary"> -->
                                    </div>
                                </div>
                                {{ Form::close() }}
                                <!-- <div class="col-md-2">
                                    <div class="form-group">
                                        <input id="bulk-generate" style="margin-top: 25px; width: 100%;"
                                            class="btn btn-info text-sm" value="Generate Salary">
                                    </div>
                                </div> -->
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive"></div>
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

        // $('body').find('#to_time').attr('disabled', true);

        $(document).on("change", "#month", function() {
            $('#get_month').val(this.value);
        });

        $(document).on("focus", ".from_date", function() {            
            $(this).datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                clearBtn: true,
                // startDate: new Date(),
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
                $('#get_from_date').val(this.value);
            });
        });
        $(document).on("focus", ".to_date", function() {
            $(this).datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: true,
                clearBtn: true,
                // startDate: new Date(),
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
                $('#get_to_date').val(this.value);
            });
        });
    });
</script>
@endsection
