@extends('admin.master')

@section('content')

@section('title')
    @lang('payroll.payroll_data_upload')
@endsection
<style>
    #salary_month {
        height: unset !important;
    }
    #salary-month-form-row {
        border: 1px solid #ccc;
        margin-bottom: 5px;
    }
    #salary-data-upload-form {
        border: 1px solid #ccc;
        margin-bottom: 5px;
    }
    #template_download_button {
        margin-right: 10px;
    }
    #template2 {
        margin-bottom: 10px;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
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
                                <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;
                                <strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('info'))
                            <div class="alert alert-info alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;
                                <strong>{{ session()->get('info') }}</strong>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="glyphicon glyphicon-remove"></i>&nbsp;
                                <strong>{{ session()->get('error') }}</strong>

                            </div>
                        @endif
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

                        <div class="row" id="salary-data-upload-form">
                            <div class="col-md-12">
                                <h4>Data Upload</h4>
                            </div>
                            <form action="{{ route('upload.payrollImport') }}" method="post" enctype="multipart/form-data">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="total_month_days">Year & Month</label>
                                        @php
                                            $select_month_form = $salary_month = request()->get('select_month', null);
                                            // $select_month = $salary_month = request()->get('select_month', date('Y-m'));
                                            // $date = new DateTime($select_month.'-01 00:00:00');
                                            // $date->modify('+1 month');
                                            // $select_month = $date->format('Y-m');
                                            // $total_month_days = count(findMonthToAllDate($select_month_form));
                                            // $total_month_days = 31;
                                            // $display_salary_month_from=dateConvertDBtoForm($salary_month.'-01');
                                            // $display_salary_month_to = date('t',strtotime($salary_month.'-01'));
                                            // $display_salary_month_to=dateConvertDBtoForm($salary_month.'-'.$display_salary_month_to);
                                        @endphp
                                        {{ Form::text('salary_month', $select_month_form, ['class' => 'form-control monthFieldNew salary_month input-sm', 'required']) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="form-group">
                                                    {{ csrf_field() }}
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <span><i class="fa fa-upload"></i></span>
                                                            <span>Payroll Excel File.</span>
                                                            <input type="file" name="select_file"
                                                                class="form-control custom-file-upload">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group"><br>
                                                            <button class="btn btn-success btn-sm"
                                                                type="submit"><span><i class="fa fa-upload"
                                                                        aria-hidden="true"></i></span> Upload</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="row" id="salary-month-form-row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-12">
                                        @php
                                            $listNull[null] = 'Select Salary Month';
                                            $listMonth = \App\Model\PayrollUpload::salaryMonthList();
                                            $listMonth = array_merge($listNull, $listMonth);
                                            $select_month_form = request()->get('salary_month', null);
                                        @endphp
                                        <form action="{{ route('upload.payroll_upload') }}" method="get" id="salary-month-form" style="">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h4>Data Actions</h4>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        {{-- <span><i class="fa fa-calendar"></i></span>
                                                        <span for="salary_month">Salary Month</span> --}}
                                                        {!! Form::select('salary_month', $listMonth, $select_month_form, [
                                                            'class' => 'form-control salary_month',
                                                            'id' => 'salary_month',
                                                        ]) !!}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <a href="javascript:;" id="template_download_button">
                                                        <div id="template1" class="btn btn-info btn-sm template1" value="Template" type="submit">
                                                            <i class="fa fa-download" aria-hidden="true"></i> <span>Download</span>
                                                        </div>
                                                    </a>
                                                    <button type="button" id="salary_delete_button" name="delete" value="true" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i><span> Delete</span></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-12 text-right"><br>
                                        <a href="{{ Route('templates.payrollTemplate') }}" id="blank_template_download_button">
                                            <div id="template2" class="btn btn-primary btn-sm template2" value="Template" type="submit">
                                                <i class="fa fa-download" aria-hidden="true"></i> <span>Template</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top: 25px">
                            <div class="col-md-12">
                                @include('admin.payroll.payrollUpload.pagination')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    var tem = '<?= route('templates.uploadPayrollTemplate') ?>';

    $(function() {
        $('#template_download_button').attr('href', tem+'?salary_month='+$('#salary_month').val());

        $('.data').on('click', '.pagination a', function(e) {
            getData($(this).attr('href').split('page=')[1]);
            e.preventDefault();
        });

        $(document).on('change', '#salary_month', function(e) {
            $('#salary-month-form').submit();
        });
        
        $(document).on('click', '#template_download_button', function(e) {
            $(this).attr('href', tem+'?salary_month='+$('#salary_month').val());
        });
        
        $(document).on('click', '#salary_delete_button', function(e) {
            if(confirm('Are you sure want to delete the Salary Month ' + $('#salary_month').val())) {
                // $(this).attr('href', tem+'?salary_month='+$('#salary_month').val()+'&delete=true');
                $('#salary-month-form').append('<input type="hidden" name="delete_salary" value="1">');
                $('#salary-month-form').submit();
            }
            return false;
        });

        $(".monthFieldNew").datepicker({
            format: "mm/yyyy",
            viewMode: "months",
            minViewMode: "months"
        }).on('changeDate', function(e) {
            $(this).datepicker('hide');
        });
    });

    function getData(page) {
        var employee_name = $('.employee_name').val();
        var department_id = $('.department_id').val();
        var designation_id = $('.designation_id').val();
        var role_id = $('.role_id').val();

        $.ajax({
            url: '?page=' + page + "&employee_name=" + employee_name + "&department_id=" + department_id +
                "&designation_id=" + designation_id + "&role_id=" + role_id,
            datatype: "html",
        }).done(function(data) {
            $('.data').html(data);
            $("html, body").animate({
                scrollTop: 0
            }, 800);
        }).fail(function() {
            alert('No response from server');
        });
    }
</script>

<style>
    .bdColor {
        color: #8d9ea7;
    }

    #custom-search-input .search-query {
        padding-right: 3px;
        padding-right: 4px \9;
        padding-left: 3px;
        padding-left: 4px \9;
        /* IE7-8 doesn't have border-radius, so don't indent the padding */
        margin-bottom: 0;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }

    #custom-search-input button {
        border: 0;
        background: none;
        /** belows styles are working good */
        padding: 2px 5px;
        margin-top: 2px;
        position: relative;
        left: -28px;
        /* IE7-8 doesn't have border-radius, so don't indent the padding */
        margin-bottom: 0;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
        color: #ddd;
    }

    .search-query:focus+button {
        z-index: 3;
    }

    .panel-blue a,
    .panel-info a {
        color: black;
    }
</style>
@endsection
