<?php
use App\Model\Employee;
?>
@extends('admin.master')
@section('content')
@section('title')
    @lang('employee.leave_test')
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
    table, td, th {
        border-left: 1px solid #999999;
        border-right: 1px solid #999999;
        border-bottom: 1px solid #999999;
        border-top: 1px solid #999999 !important;
    }
    td, th {
        padding:8px !important;
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

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>
                    @lang('employee.leave_test')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @php
                                    for ($i=1; $i <=15 ; $i++) { 
                                        $year = rand((2023-62),(2023-18));
                                        $month = rand(1,12);
                                        $day = rand(1,28);
                                        $doj = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
                                        // echo $doj.'<br>';
                                    }
                                @endphp
                                @foreach ($EmployeeList as $Employee)
                                <div id="emp-div-{{ $Employee->employee_id }}">
                                {!! 
                                view('admin.user.user.leaveInfo', compact('Employee'))
                                !!}
                                </div>
                                @endforeach
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
    $(document).ready(function () {
        $("body").on("click", ".re-calc", function () {
            let id = $(this).data("id");
            if(id) {
                $.ajax({
                    type: "GET",
                    url: $(this).data('url'),
                    data: $("#emp-form-" + id).serialize(),
                    cache: false,
                    beforeSend: function (xhr, settings) {
                        $("#busy").show();
                    },
                    complete: function (event, request) {
                        $("#busy").hide();
                    },
                    success: function (response) {
                        $("#emp-div-"+id).html(response);
                    },
                    error: function (data) {
                        $("#busy").hide();
                    },
                });
            }
        });
    });
</script>
@endsection