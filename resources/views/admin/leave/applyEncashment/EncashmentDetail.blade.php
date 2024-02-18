@php
use App\Repositories\LeaveRepository;
$Employee = $LeaveEncashment->Employee;
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
            <a href="{{ route('leave.Encashment') }}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i class="fa fa-list-ul" aria-hidden="true"></i> @lang('leave.view_leave_applicaiton')</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@lang('leave.EncashmentApplyFrom')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @include('flash_message')
                        {{ Form::open(['route' => 'leave.EncashmentApply', 'id' => 'EncashmentActionForm']) }}
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3 class="box-title">PL Encacshment Application Details</h3>
                                    <hr>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-1"></div>
                                            <div class="col-md-5">
                                                <p>@lang('employee.name'):<b>{{ $Employee->fullname() }}</b></p>
                                                <p>@lang('employee.emp_code'): <b>{{ $Employee->emp_code }}</b></p>
                                                <p>@lang('common.designation'): <b>{{  $Employee->designation_disp() }}</b></p>
                                                <p>@lang('common.department'): <b>{{ $Employee->department_disp() }}</b></p>
                                            </div>
                                            <div class="col-md-5">
                                                <p>@lang('employee.phone'): <b>{{ $Employee->phone ?? '-' }}</b></p>
                                                <p>@lang('employee.supervisor'): <b>{{ $Employee->supervisorDetail() }}</b></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="box-title">PL Review</h3>
                                    @php
                                    // dd($DATA);
                                        // $LeaveEncashmentData = $LeaveEncashment->LeaveEncashmentData();
                                    @endphp
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('employee.sal_ctc'): </label>
                                        <p class="col-md-6 col-sm-6 bold"><i class="fa fa-inr"></i>{{ $DATA['CTC'] }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('employee.basic'): </label>
                                        <p class="col-md-6 col-sm-6 bold"><i class="fa fa-inr"></i>{{ $DATA['BASIC'] }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('leave.enc_open'): </label>
                                        <p class="col-md-6 col-sm-6 bold">{{ $LeaveEncashment->enc_open }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('leave.enc_days'): </label>
                                        <p class="col-md-6 col-sm-6 bold">{{ $LeaveEncashment->enc_days }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('leave.enc_amount'): </label>
                                        <p class="col-md-6 col-sm-6 bold"><i class="fa fa-inr"></i>{{ $LeaveEncashment->enc_amount }}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-6 col-sm-6 ">@lang('leave.enc_status'): </label>
                                        <p class="col-md-6 col-sm-6 bold">{{ $LeaveEncashment->enc_status_display }}</p>
                                    </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <h3 class="box-title">PL &nbsp;&nbsp;Approval / Rejection</h3>
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td>@lang('leave.enc_remark'): </td>
                                                <td>
                                                    <textarea name="enc_remark" id="enc_remark" class="form-control" rows="3"></textarea>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('leave.enc_action'): </td>
                                                <td>
                                                    @if ($LeaveEncashment->enc_status==$LeaveEncashment::PENDING)
                                                        <input type="hidden" value="" name="enc_status" id="enc_status">
                                                        <button type="button" class="btn-action btn btn-primary" value="{{ $LeaveEncashment::APPROVED }}" name="approve" id="approve">Approve</button>
                                                        <button type="button" class="btn-action btn btn-danger" value="{{ $LeaveEncashment::REJECTED }}" name="reject" id="reject">Reject</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
    var prevent=false;
    var promptCheck=false;
    $(document).ready(function () {
        $('.btn-action').click(function (e) { 
            let action = $(this).val();
            let enc_remark = $('#enc_remark').val().trim();
            if(!enc_remark) {
                bootbox.alert({message: 'Please enter remarks field!',});
                return false;
            }
            let actionName = '';
            if(action=='{{ $LeaveEncashment::APPROVED }}') {
                $('#enc_status').val({{ $LeaveEncashment::APPROVED }});
                actionName = 'Approve';
            } else if(action=='{{ $LeaveEncashment::REJECTED }}') {
                $('#enc_status').val({{ $LeaveEncashment::REJECTED }});
                actionName = 'Rejected';
            }
            bootbox.confirm('<h4 class="text-alert">Are you sure want to proceed encashment '+actionName+'?<h4>',
            function(result) {
                if(result) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ Route('leave.EncashmentAction', ['id' => $LeaveEncashment->enc_entry_id]) }}',
                        data: $('#EncashmentActionForm').serialize(),
                        dataType: 'html',
                        success: function(data, textStatus, request) {
                            if(data=='1') {
                                location.href = '{{ Route('leave.EncashmentApplications') }}';
                            }
                            slog(data);
                        }
                    }).always(function (dataOrjqXHR, textStatus, jqXHRorErrorThrown) {
                        prevent=false;
                    });
                    return result;
                }
            });
            return false;
        });

          
    });    

</script>
@endsection
