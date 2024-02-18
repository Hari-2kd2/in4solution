@php
use App\Repositories\LeaveRepository;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('leave.EncashmentApplyFrom')
@endsection
<style>
    .datepicker table tr td.disabled,
    .datepicker table tr td.disabled:hover {
        background: none;
        color: red !important;
        cursor: default;
    }
    .text-normal {
        font-weight: bold;
        font-size: 14px;
    }
    .text-red {
        color: #ac2925;
        font-weight: bold;
        font-size: 14px;
    }
    h4.text-alert {
        font-weight: bold;
        color: #ac2925;
    }
    .text-green {
        color: #449d44;
        font-weight: bold;
        font-size: 14px;
    }
    .display_none {
        display: none !important;
    }
    .inr {
        font-family: DejaVu Sans; sans-serif;
    }

    td {
        color: black !important;
    }

    #EncashmentCalculates {
        border: 1px solid #ccc;
        border-collapse: collapse !important;
    }

    #EncashmentCalculates>tbody>tr>td, #EncashmentCalculates>tbody>tr>th, #EncashmentCalculates>tfoot>tr>td, #EncashmentCalculates>tfoot>tr>th, #EncashmentCalculates>thead>tr>td, #EncashmentCalculates>thead>tr>th {
        padding: 5px 8px !important;
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
            <a href="{{ route('leave.Encashment') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.my_encashment_list')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.EncashmentApplyFrom')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @include('flash_message')

                        {{ Form::open(['route' => 'leave.EncashmentApply', 'enctype' => 'multipart/form-data', 'id' => 'EncashmentApplyForm']) }}
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>@lang('common.employee_name')</label>
                                        <label class="form-control">{{ $Employee->fullname() }}</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>@lang('leave.year_id')</label>
                                        <div class="form-control" readonly>{{ $calanderYear->year_name ?? '' }}</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="privilege_leave">@lang('leave.current_pl_balance') </label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <div class="form-control" readonly>{{ $EmployeeLeaves->privilege_leave ?? 0 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <p class="text-red"><i class="fa fa-exclamation-circle"></i> A Minimum of {{ $LeaveEncashmentData['SHOULD_MIN_PL'] }} days balance should be available to the leave credits after encashment.</p>
                                        @php
                                            // dd($LeaveEncashmentData)
                                        @endphp
                                        @if ($LeaveEncashmentData['encahStatus']===true)
                                            <p class="text-green"><i class="fa fa-exclamation-circle"></i> You can use maximum {{ $LeaveEncashmentData['CAN_USE_MAX_PL'] }} days for PL encashment.</p>
                                        @endif
                                        <p class="text-normal"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($LeaveEncashmentData['encahStatus']===true)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('leave.enc_days')</label>
                                    <div class="form-group">
                                        {!! Form::number(
                                            'enc_days',
                                            Input::old('enc_days'),
                                            $attributes = [
                                                'class' => 'form-control enc_days',
                                                'required' => 'required',
                                                'id' => 'enc_days',
                                                'min' => '1',
                                                'max' => $LeaveEncashmentData['CAN_USE_MAX_PL'],
                                                'placeholder' => __('leave.enc_days'),
                                            ],
                                        ) !!}
                                    </div>
                                    <p class="text-red" id="day_error"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group enc_detail" style="display:none">
                                    <div id="enc_detail"></div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                @if ($LeaveEncashmentData['encahStatus']===true)
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary hide" id="apply_encashment_bnt">Apply Encashment</button>
                                        <button name="apply_encashment_bnt_clone" type="button" class="btn btn-info" value="go" id="apply_encashment_bnt_clone">Apply Encashment</button>
                                    </div>
                                    @else
                                        <div class="alert bold">@lang('leave.enc_status'): {{$LeaveEncashmentData['encahStatusMessage']}}</div>
                                    @endif
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
    var prevent=false;
    var promptCheck=false;
    var min_days=0;
    var max_days=0;
    $(document).ready(function () {
        min_days = parseInt($('#enc_days').attr('min'));
        max_days = parseInt($('#enc_days').attr('max'));
        
        $('#enc_days').change(function (e) { 
            let enc_days = parseInt($(this).val());
            e.preventDefault();
            $('#day_error').text('');

            if(enc_days > max_days) {
                $('#day_error').text('Days should be less than or equal to '+max_days);
                elog('max error');
            } else if(enc_days < 1) {
                $('#day_error').text('Days should be grater than or equal to '+min_days);
                elog('min error');
            }
        });

        $('#apply_encashment_bnt_clone').click(function (e) { 
            let enc_days = $('#enc_days').val();
            if(!enc_days) {
                bootbox.alert({message: 'Please enter No Of Leaves Encashment field!',});
                return false;
            }
            if($('#day_error').text()!='') {
                bootbox.alert({message: 'Please enter valid No Of Leaves Encashment field!',});
                return false;
            }


            bootbox.confirm('<h4 class="text-alert">Are you sure want to proceed encashment process? Once you have encashment can not be used current calendar year.<h4>',
            function(result) {
                if(result) {
                    $('#apply_encashment_bnt').click();
                    return result;
                }
            });
            return false;
        });

        $(document).on("change", "#enc_days", function(e) {
            if(prevent==true) {
                return false;
            }
            var enc_days = $(this).val();
            if(enc_days=='') {
                $('.enc_detail').hide();
            }
            var action = "{{ Route('leave.EncashmentCalculates') }}";
            if (!prevent) {
                prevent = true;
            }

            $.ajax({
                type: 'POST',
                url: action,
                data: $('#EncashmentApplyForm').serialize(),
                dataType: 'html',
                success: function(data, textStatus, request) {
                    $('#enc_detail').html(data);
                    if(enc_days=='') {
                        $('.enc_detail').hide();
                    } else {
                        $('.enc_detail').show();
                    }
                }
            }).always(function (dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
                prevent=false;
            });
        });    
    });    

</script>
@endsection
