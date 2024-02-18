@php
    use App\Repositories\LeaveRepository;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.leave_application_form')
@endsection
<style>
    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
        background: none;
        color: red !important;
        cursor: default;
    }

    .display_none {
        display: none !important;
    }

    td {
        color: black !important;
    }

    #leave-information-table tr td {
        padding: 5px;
    }
</style>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>

            </ol>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('applyForLeave.index') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.view_leave_applicaiton')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.leave_application_form')</div>
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
                                <strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        {{ Form::open(['route' => 'applyForLeave.store', 'enctype' => 'multipart/form-data', 'id' => 'leaveApplicationForm']) }}
                        <div class="form-body">
                            <div class="row">
                                {!! Form::hidden(
                                    'employee_id',
                                    isset($getEmployeeInfo) ? $getEmployeeInfo->employee_id : '',
                                    $attributes = ['class' => 'employee_id'],
                                ) !!}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('common.employee_name')<span
                                                class="validateRq">*</span></label>
                                        <label
                                            class="form-control">{{ isset($getEmployeeInfo) ? $getEmployeeInfo->first_name . ' ' . $getEmployeeInfo->last_name : '' }}</label>
                                        {{-- {!! Form::text(
                                            '',
                                            isset($getEmployeeInfo) ? $getEmployeeInfo->first_name . ' ' . $getEmployeeInfo->last_name : '',
                                            $attributes = ['class' => 'form-control', 'readonly' => 'readonly'],
                                        ) !!} --}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.leave_type')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('leave_type_id', $leaveTypeList, '', ['class' => 'form-control leave_type_id select2 required']) }}
                                    </div>
                                </div>
                                <div class="col-md-4 half_day_section normal-section">
                                    <div class="form-group">
                                        <label for="exampleInput"
                                            title="{{ PHP_EOL }}1 will be 0.5 day,{{ PHP_EOL }}2 will be 1.5 days{{ PHP_EOL }}if select @lang('leave.half_day')">@lang('leave.day_option')
                                            <i class="fa fa-exclamation-circle"></i></label>
                                        {{ Form::select('half_day', ['' => 'Full Day', '0.5' => __('leave.half_day')], null, $attributes = ['class' => 'form-control half_day', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="exampleInput">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'application_from_date',
                                            Input::old('application_from_date'),
                                            $attributes = [
                                                'class' => 'form-control application_from_date',
                                                'readonly' => 'readonly',
                                                'required' => 'required',
                                                'placeholder' => __('common.from_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-4 met-pet-section-show hide">
                                    <label for="exampleInput">@lang('common.to_date')</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'application_to_date_disp',
                                            Input::old('application_to_date_disp'),
                                            $attributes = [
                                                'class' => 'form-control application_to_date_disp',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('common.to_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-4 normal-section">
                                    <label for="exampleInput">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'application_to_date',
                                            Input::old('application_to_date'),
                                            $attributes = [
                                                'class' => 'form-control application_to_date',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('common.to_date'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-4 met-pet-section-show hide">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.durations')</label>
                                        {!! Form::text(
                                            'durations',
                                            '',
                                            $attributes = [
                                                'class' => 'form-control durations',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('leave.durations'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-4 normal-section">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.current_balance')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::text(
                                            'current',
                                            Input::old('current'),
                                            $attributes = [
                                                'class' => 'form-control current_balance',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('leave.current_balance'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-4 normal-section">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.number_of_day')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::text(
                                            'number_of_day',
                                            '',
                                            $attributes = [
                                                'class' => 'form-control number_of_day',
                                                'readonly' => 'readonly',
                                                'placeholder' => __('leave.number_of_day'),
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-4" id="mFile">
                                    <label for="photo">@lang('employee.medical_certificate')<span class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="	fa fa-picture-o"></i></span>
                                        <input class="form-control mfile" id="mfile" name="mfile" type="file"
                                            onchange="SizeCheck();"
                                            accept="image/jpeg, image/pjpeg, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    </div>
                                    <label>
                                        <small class="">File Size: {{ $certificateFile['sizeInMB'] }} MB, Types:
                                            {{ implode(', ', $certificateFile['type']) }} </small><br>
                                        <small><label id="upload_error" class="alert alert-danger"
                                                style=" padding:3px">Size
                                                should be less than
                                                {{ $certificateFile['sizeInMB'] }} MB</label></small>
                                    </label>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('leave.purpose')<span
                                                class="validateRq">*</span></label>
                                        {!! Form::textarea(
                                            'purpose',
                                            Input::old('purpose'),
                                            $attributes = [
                                                'class' => 'form-control purpose',
                                                'id' => 'purpose',
                                                'placeholder' => __('leave.purpose'),
                                                'cols' => '30',
                                                'required' => true,
                                                'rows' => '3',
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <button type="submit" id="formSubmit" class="btn btn-info "><i
                                                class="fa fa-paper-plane"></i> @lang('leave.send_application')</button>
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
    var prevent = false;
    var fileCheck = true;
    var fileCheckMessage = '';
    var PL_MIN = parseInt('{{ LeaveRepository::MIN_PL_APPLY_DAYS }}');

    function SizeCheck() {
        var fileInput = document.getElementById('mfile').files;
        var fsize = fileInput[0].size;
        var vsize = {{ $certificateFile['sizeInBytes'] }};
        slog('SizeCheck vsize=' + vsize + ', fsize=' + fsize)
        fileCheck = false;
        if (fsize >= {{ $certificateFile['sizeInBytes'] }}) { // in bytes
            $('#upload_error').show();
            fileCheck = false;
            fileCheckMessage = 'Uploaded file size (' + Math.round((fsize / 1024 / 1024)) +
                'MB) is to larger than allowed size(' + Math.round((vsize / 1024 / 1024)) + 'MB)!'
        } else {
            $('#upload_error').hide();
            fileCheck = true;
            fileCheckMessage = '';
        }
    }

    jQuery(function() {
        var today = new Date('{{ Config('leave.past_date') }}');
        var innext = new Date('{{ Config('leave.past_date') }}').setDate(today.getDate() + 2);

        var toStartDate = '{{ Config('leave.past_date') }}';
        $('body').find('#formSubmit').attr('disabled', false);

        // $(".leave_type_id").change(function() {
        //     if ($(this).val() == 2) {
        //         $('#mFile').removeClass('display_none');
        //     } else {
        //         $('#mFile').addClass('display_none');
        //     }
        // });

        $(document).on("focus", ".application_from_date", function() {
            $(this).datepicker({
                format: 'dd/mm/yyyy',
                todayHighlight: true,
                clearBtn: true,
                startDate: '{{ Config('leave.past_date') }}',
                // endDate: '{{ Config('leave.future_date') }}',
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
            });
        });

        $(document).on("focus", ".application_to_date", function() {
            $(this).datepicker({
                format: 'dd/mm/yyyy',
                todayHighlight: true,
                clearBtn: true,
                startDate: toStartDate,
                // endDate: '{{ Config('leave.future_date') }}',
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
            });
        });
        // if($('.leave_type_id'))



        $('#leaveApplicationForm').submit(function(e) {
            let type_id = $('.leave_type_id ').val();
            let noofdays = $('.number_of_day').val();
            let application_from_date = $('.application_from_date').val();
            let application_to_date = $('.application_to_date').val();
            let number_of_day = $('.number_of_day').val();
            let medicaldays = 2;

            if (!fileCheck) {
                bootbox.alert({
                    message: fileCheckMessage,
                });
                return false;
            }

            if (type_id == 3 && noofdays < PL_MIN) {
                bootbox.alert({
                    message: 'Please select minimum ' + PL_MIN + ' working days for PL',
                });
                return false;
            }
            // alert(type_id,noofdays,medicaldays);
            if (type_id == 2 && noofdays > medicaldays) {
                if ($('#mFile').css('display') == 'block' && !$('#mfile').val()) {
                    bootbox.alert({
                        message: 'Please attach <b>@lang('employee.medical_certificate')</b> if more than ' + (
                            medicaldays) + ' days leaves',
                    });
                    return false;
                }
            }
            if (type_id == 5 || type_id == 6) {
                let application_from_date = $('.application_from_date').val();
                let application_to_date = $('.application_to_date').val();
                if (application_from_date == application_to_date || !application_from_date || !
                    application_to_date) {
                    bootbox.alert({
                        message: 'Please select From Date once',
                    });
                    return false;
                }
            }
            if (!application_from_date || !application_to_date) {
                bootbox.alert({
                    message: 'Please select From Date and To Date',
                });
                return false;
            }
            if (!number_of_day) {
                bootbox.alert({
                    message: 'Number of Day not valid, please select From and To Date',
                });
                return false;
            }
            return true;
        });
        // $(document).on("change", ".application_from_date,.application_to_date ", function(e) {
        $('.application_from_date,.application_to_date').change(function(e) {
            if (prevent == true) {
                return false;
            }

            $('body').find('#formSubmit').attr('disabled', false);
            var application_from_date = $('.application_from_date').val();
            var application_to_date = $('.application_to_date').val();
            let type_id = $('.leave_type_id ').val();
            if (application_to_date = application_from_date) {
                $('.half_day').val('');
            }
            var action = "{{ URL::to('applyForLeave/applyForTotalNumberOfDays') }}";
            prevent = true;
            $.ajax({
                type: 'POST',
                url: action,
                data: {
                    'application_from_date': application_from_date,
                    'application_to_date': application_to_date,
                    'type_id': type_id,
                    'leave_type_id': type_id,
                    '_token': $('input[name=_token]').val()
                },
                dataType: 'json',
                success: function(data, textStatus, request) {
                    let info;
                    if (request.getResponseHeader('info')) {
                        info = JSON.parse(request.getResponseHeader('info'));
                    }
                    $('.current_balance').val(typeof(info.balance) != 'undefined' ? info
                        .balance : '');

                    var currentBalance = $('.current_balance').val();
                    // any leave type but balance leave days > than current balace error
                    if ($('.half_day').val() != '') {
                        data = parseInt(data) - parseFloat($('.half_day').val());
                    }
                    if (data > parseInt(currentBalance)) {
                        $.toast({
                            heading: 'Warning',
                            text: 'You have to apply ' + currentBalance + ' days!',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'warning',
                            hideAfter: 5000,
                            stack: 6
                        });
                        $('body').find('#formSubmit').attr('disabled', true);
                        $('.number_of_day,.half_day').val('');

                    } else if (data == 0) { // current balace error
                        $.toast({
                            heading: 'Warning',
                            text: 'You can not apply for leave !',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'warning',
                            hideAfter: 5000,
                            stack: 6
                        });
                        $('body').find('#formSubmit').attr('disabled', true);
                        $('.number_of_day,.half_day').val('');
                    } else {
                        $('body').find('#formSubmit').attr('disabled', false);
                        var leave_type_id = $('.leave_type_id ').val();
                        var employee_id = $('.employee_id ').val();
                        if (employee_id != '' && leave_type_id != '') {
                            if (data > 1 && leave_type_id == 1) {
                                // Casual Leave maximum restriction per application
                                $('body').find('#formSubmit').attr('disabled',
                                    true);
                                $('.number_of_day,.half_day').val('');
                                $.toast({
                                    heading: 'Warning',
                                    text: 'CL cannot be availed for more than 2 days at a stretch!',
                                    position: 'top-right',
                                    loaderBg: '#ff6849',
                                    icon: 'warning',
                                    hideAfter: 5000,
                                    stack: 1
                                });
                                return false;
                            } else {
                                $('.number_of_day').val(data);
                            }
                            $('body').find('#formSubmit').attr('disabled', false);
                        }
                        $('.number_of_day').val(data);
                        $('body').find('#formSubmit').attr('disabled', false);
                    }
                }
            }).always(function(dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
                prevent = false;
            });
            return true;
        });

        $(document).on("change", ".half_day", function() {
            $('#leaveApplicationForm input[type="text"]').val('');
        });

        $(document).on("change", ".leave_type_id", function() {
            // $('#mFile').addClass('display_none');
            $('body').find('#formSubmit').attr('disabled', false);
            var leave_type_id = $('.leave_type_id ').val();
            var total_leave_taken = $('.total_leave_taken ').val();
            ilog('leave_type_id=' + leave_type_id);

            $('.half_day').val('');
            $('.application_from_date,.application_to_date,.application_to_date_disp,.number_of_day,.durations,.current_balance')
                .val('');
            $('.normal-section').removeClass('hide');
            $('.met-pet-section-show').addClass('hide');
        });

    });
</script>
@endsection
