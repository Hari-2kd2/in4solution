<?php
use App\Model\Employee;

$cron_executed_on = DB::table('leave_credit_settings')->max('cron_executed_on');
if(!$cron_executed_on) {
    $cron_executed_on = dateConvertDBtoForm('2021-01-01');
} else {
    $cron_executed_on = dateConvertDBtoForm($cron_executed_on);
}
?>
@extends('admin.master')
@section('content')
@section('title')
    @lang('Leave Credits')
@endsection
<style>
    .panel-custom {
        background-color: #F1F1F1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        padding: 10px 15px;
    }

    .text-red {
        color: rgb(196, 3, 3);
    }

    .item {
        padding: 13px 21px;
    }

    .bold {
        font-weight: bold;
    }

    table.main {
        margin-bottom: 40px !important;
    }

    table.sub {
        margin-top: 10px !important;
    }

    table,
    td,
    th {
        border-left: 1px solid #999999;
        border-right: 1px solid #999999;
        border-bottom: 1px solid #999999;
        border-top: 1px solid #999999 !important;
    }

    td,
    th {
        padding: 8px !important;
    }

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
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

    </div>
    @if (session('error') || session('success'))
    <div class="row">
        <div class="col-sm-12">
            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @lang('Leave Credits')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {{ Form::open(['route' => 'leaveCredits', 'id' => 'leaveApplicationForm']) }}
                        @php
                            $db_cron_executed_on = dateConvertFormtoDB($cron_executed_on);
                            $LeaveCreditSetting = \App\Model\LeaveCreditSetting::where('cron_executed_on', $db_cron_executed_on)->first();
                            $limit = 10;
                            $LeaveCreditSettingAll = \App\Model\LeaveCreditSetting::where('cron_changes', '>' , 0)->offset(0)->take($limit)->orderBy('id', 'DESC')->get();
                            $SalaryRepository = new \App\Repositories\SalaryRepository;
                            $addYear = addYear('2021-01-01', $SalaryRepository->SERVICE_COMPLETE_YEAR);
                        @endphp
                        <div class="row">
                            <div class="col-md-3">
                                <label for="exampleInput">Execute Date</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    {!! Form::text(
                                        'cron_executed_on',
                                        $cron_executed_on,
                                        $attributes = [
                                            'class' => 'form-control cron_executed_on',
                                            'readonly' => 'readonly',
                                            'placeholder' => 'Execute Date',
                                        ],
                                        ) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="exampleInput">&nbsp;<br></label>
                                <div class="input-group">
                                    <button type="submit" class="btn btn-primary">Execute</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="exampleInput">&nbsp;<br></label>
                                <div class="input-group">
                                    <button type="submit" name="reset_leaves" id="reset_leaves" value="reset_leaves" class="btn btn-danger">Reset All Leaves Data</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                            </div>
                            <div class="col-md-3">
                            </div>
                        </div>
                        @if ($LeaveCreditSetting)
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 15px">
                                <h1>Last Executed Info</h1>
                                <pre>{{ $LeaveCreditSetting->cron_log }}</pre>
                            </div>
                        </div>
                        @endif
                        @if (count($LeaveCreditSettingAll)>0)
                        <div class="row">
                            <div class="col-md-12" style="margin-top: 15px">
                                <h1>Last {{ $limit }} Credits Changes</h1>
                                @foreach ($LeaveCreditSettingAll as $in => $LeaveCreditSettingOne)
                                    <pre>{{ $LeaveCreditSettingOne->cron_log }}
Number of changes {{ $LeaveCreditSettingOne->cron_changes }}</pre>
                                @endforeach
                            </div>
                        </div>
                        @endif
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
    $(document).ready(function() {
        $("body").on("click", "#reset_leaves", function() {
            if(confirm('Are sure want to delete leave credits, transaction and cron setting data?')) {
                return true;
            }
            return false;
        });

        $(document).on("focus", ".cron_executed_on", function() {
            $(this).datepicker({
                format: 'dd/mm/yyyy',
                todayHighlight: true,
                clearBtn: true,
                startDate: '<?= $cron_executed_on ?>',
            }).on('changeDate', function(e) {
                $(this).datepicker('hide');
            });
        });
    });
</script>
@endsection
