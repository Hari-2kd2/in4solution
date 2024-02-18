<?php

use App\Model\Device;
?>

@extends('admin.master')
@section('content')
@section('title')
    @lang('employee.profile')
@endsection
<style>
    .panel-custom {
        background-color: #F1F1F1;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        padding: 10px 15px;
    }

    .item {
        padding: 13px 21px;
    }

    .mt-1 {
        margin-top: 10px
    }

    #salary-history-container,
    #subordinates-container {
        display: none
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

        @if (session('logged_session_data.employee_id') == $employeeInfo->employee_id)
            <div class="col-lg-9 col-md-8 col-sm-8 col-xs-12">
                <a href="{{ route('profile.edit', $employeeInfo->employee_id) }}"
                    class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                        class="fa fa-pencil-square-o" aria-hidden="true"></i> @lang('common.edit-profile')</a>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>
                    @lang('employee.profile')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">x</span></button>
                                @foreach ($errors->all() as $error)
                                    <strong>{!! $error !!}</strong><br>
                                @endforeach
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <div class="">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div id="resume">
                                    <p><b>@lang('employee.emp_code') : </b><strong>{{ $employeeInfo->emp_code }}</strong></p>
                                    <p><b>@lang('employee.name') : </b><strong>{{ $employeeInfo->first_name }}
                                            {{ ' ' }}{{ $employeeInfo->last_name }}</strong></p>
                                    @if (session('logged_session_data.role_id') < 8)
                                        <p><b>@lang('employee.password') : </b><a style="color: inherit" class="show-pass"
                                                href="javascript:;"><i class="fa fa-eye"></i></a> <strong
                                                class="hide password-element form-control">{!! $User->org_password ?? '<span class="text-muted">N/A</span>' !!}</strong>
                                        </p>
                                    @endif
                                    <p><b>@lang('employee.email') :</b> {{ $employeeInfo->email }}</p>
                                    <p></p>
                                    <p class="applicant_address"> <b>@lang('employee.address') :
                                        </b>{{ $employeeInfo->address }}</p>
                                    <p> <b>@lang('employee.phone') :</b> {{ $employeeInfo->phone }}</p>
                                    <p></p>
                                    <p> <b>@lang('common.branch') :</b> {{ $employeeInfo->branch->branch_name ?? '' }}</p>
                                    <p></p>
                                    <p> <b>@lang('employee.work_shift') :</b> {!! $employeeInfo->shiftDetail() !!}</p>
                                    @php
                                        $subordinateIds = $employeeInfo->subordinateIds();
                                        $subordinateText = [];
                                        foreach ($subordinateIds as $key => $D) {
                                            $eachSubordinate = \App\Model\Employee::find($D);
                                            $subordinateText[] = $key + 1 . '. ' . $eachSubordinate->detailname();
                                        }
                                    @endphp
                                    @if (count($subordinateIds))
                                        <p> <b>@lang('employee.subordinate') :</b> <a href="javascript:;"
                                                class="btn btn-xs btn-primary"
                                                id="subordinates-container-btn">Detail</a><br>
                                        <div id="subordinates-container" class="mt-1">{!! implode(',<br>', $subordinateText) !!}</div>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="applicant_pic text-right">
                                    <?php
                                    if ($employeeInfo->photo != '') {
                                    ?>
                                    <img style="width: 124px;height:135px" src="{!! asset('uploads/employeePhoto/' . $employeeInfo->photo) !!}">
                                    <?php  } else { ?>
                                    <img style="width: 124px;height:135px" src="{!! asset('admin_assets/img/default.png') !!}">
                                    <?php } ?>
                                </div>
                                <br>
                            </div>
                            <!----------------------
                                'ACADEMIC QUALIFICATION:
                                ------------------------>
                            <div class="education_qualification" hidden>
                                <section class="content">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="panel-custom">
                                                <h3 class="panel-title"><i class="fa fa-graduation-cap"></i>
                                                    @lang('employee.educational_qualification')</h3>
                                            </div>
                                            <div class="box">
                                                <div class="box-body">
                                                    <table id="example1" class="table table-bordered table-hover">
                                                        <thead class="education_lable">
                                                            <tr>
                                                                <th>@lang('employee.institute')</th>
                                                                <th>@lang('employee.degree')</th>
                                                                <th>@lang('employee.board') / @lang('employee.university')</th>
                                                                <th>@lang('employee.result')</th>
                                                                <th>@lang('employee.gpa') / @lang('employee.cgpa')</th>
                                                                <th>@lang('employee.passing_year')</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="education_lable">
                                                            @if (count($employeeEducation) > 0)
                                                                @foreach ($employeeEducation as $education)
                                                                    <tr>
                                                                        <td>{{ $education->institute }}</td>
                                                                        <td>{{ $education->degree }}</td>
                                                                        <td>{{ $education->board_university }}</td>
                                                                        <td>{{ $education->result }}</td>
                                                                        <td>{{ $education->cgpa }}</td>
                                                                        <td>{{ $education->passing_year }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr class="text-center">
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <br>
                            </div>

                            <div class="education_qualification" hidden>
                                <section class="content">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="panel-custom">
                                                <h3 class="panel-title"><i class="fa fa-laptop"></i>
                                                    @lang('employee.professional_experience')</h3>
                                            </div>
                                            <div class="box">
                                                <div class="box-body">
                                                    <table id="example1" class="table table-bordered table-hover">
                                                        <thead class="education_lable">
                                                            <tr>
                                                                <th>@lang('employee.organization_name')</th>
                                                                <th>@lang('employee.designation')</th>
                                                                <th>@lang('employee.duration')</th>
                                                                <th>@lang('employee.skill')</th>
                                                                <th>@lang('employee.responsibility')</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="education_lable">
                                                            @if (count($employeeExperience) > 0)
                                                                @foreach ($employeeExperience as $experience)
                                                                    <tr>
                                                                        <td>{{ $experience->organization_name }}
                                                                        </td>
                                                                        <td>{{ $experience->designation }}</td>
                                                                        <td>{{ $experience->from_date }} To
                                                                            {{ $experience->to_date }}
                                                                        </td>
                                                                        <td>{{ $experience->skill }}</td>
                                                                        <td>{{ $experience->responsibility }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                    <td>--</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <br>
                            </div>
                            <!-------------personal info --------->

                            <div class="personal_info">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="panel-custom">
                                            <h3 class="panel-title"><i class="fa fa-info-circle"></i>
                                                @lang('employee.personal_information')</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="personal_info">
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.name')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->first_name }}
                                                {{ $employeeInfo->last_name }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.email')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->email }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.address')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->address }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.phone')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->phone }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.date_of_joining')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ dateConvertDBtoForm($employeeInfo->date_of_joining) }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.date_of_birth')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ dateConvertDBtoForm($employeeInfo->date_of_birth) }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.date_of_leaving')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ dateConvertDBtoForm($employeeInfo->date_of_leaving) ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.gender')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->gender }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.religion')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->religion ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.marital_status')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->marital_status }}</div>
                                        </div>
                                        @if ($employeeInfo->marital_status == 'Married')
                                            <div class="item">
                                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.no_of_child')</div>
                                                <div class="col-xs-10 col-sm-10 col-md-9">
                                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->no_of_child }}</div>
                                            </div>
                                        @endif
                                        <p>&nbsp;</p>

                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="panel-custom">
                                                    <h3 class="panel-title"><i class="fa fa-info-circle"></i>
                                                        @lang('employee.office_information')</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.status')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;
                                                @if ($employeeInfo->status == 1)
                                                    <span class="label label-success">@lang('common.active')</span>
                                                @elseif($employeeInfo->status == 2)
                                                    <span class="label label-warning">@lang('common.inactive')</span>
                                                @else
                                                    <span
                                                        class="label label-danger">{{ \App\Lib\Enumerations\UserStatus::statusList($employeeInfo->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.role')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->userName->role->role_name ?? '' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.user_name')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->userName->user_name ?? '' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.finger_print_no')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->finger_id ?? '' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.supervisor')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->supervisorDetail() ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.functional_head')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->functionalHeadDetail() ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.department')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->department_disp() ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.designation')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->designation_disp() ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.salary_ctc')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;<span
                                                    class="inr">₹</span>{{ $employeeInfo->salary_ctc ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.salary_gross')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;<span
                                                    class="inr">₹</span>{{ $employeeInfo->salary_gross ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.overtime_status')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->overtime_status ? 'Applicable' : 'Not Applicable' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.permanent_status')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->permanent_status == 1 ? 'Permanent' : 'Probation' }}
                                            </div>
                                        </div>

                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.salary_revision')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ dateConvertDBtoForm($employeeInfo->salary_revision) ?? '-' }}
                                                @if ($employeeInfo->salary_revision)
                                                    @php
                                                        $SalaryRevisionsAll = $employeeInfo->SalaryRevisions;
                                                    @endphp
                                                    <a href="javascript:;" class="btn btn-xs btn-primary"
                                                        id="salary-history-btn">@lang('employee.salary_history')</a>
                                                    <div id="salary-history-container" class="mt-1">
                                                        <table class="table table-border">
                                                            <tr>
                                                                <td>@lang('employee.sal_reviside_on')</td>
                                                                <td>@lang('employee.sal_ctc')</td>
                                                                <td>@lang('employee.sal_gross')</td>
                                                                <td>@lang('employee.sal_department_id')</td>
                                                                <td>@lang('employee.sal_designation_id')</td>
                                                                <td></td>
                                                            </tr>
                                                            @foreach ($SalaryRevisionsAll as $key => $SalaryRevisions)
                                                                <tr id="revision-{{ $SalaryRevisions->sal_id }}">
                                                                    <td>{{ dateConvertDBtoForm($SalaryRevisions->sal_reviside_on) }}
                                                                    </td>
                                                                    <td>{{ $SalaryRevisions->sal_ctc }}</td>
                                                                    <td>{{ $SalaryRevisions->sal_gross }}</td>
                                                                    <td>{{ $SalaryRevisions->department->department_name ?? '-' }}
                                                                    </td>
                                                                    <td>{{ $SalaryRevisions->designation->designation_name ?? '-' }}
                                                                    </td>
                                                                    <td><button style="display:none"
                                                                            class="btn btn-danger btn-xs remove-revision"
                                                                            data-id="{{ $SalaryRevisions->sal_id }}"
                                                                            data-eid="{{ $SalaryRevisions->sal_employee_id }}">
                                                                            <i
                                                                                class="fa fa-trash text-red"></i></button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($employeeInfo->relieving_reason)
                                            <div class="item">
                                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.relieving_reason')</div>
                                                <div class="col-xs-10 col-sm-10 col-md-9">
                                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ \App\Lib\Enumerations\UserStatus::leavingList($employeeInfo->relieving_reason) }}
                                                </div>
                                            </div>
                                        @endif

                                        <p>&nbsp;</p>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="panel-custom">
                                                    <h3 class="panel-title"><i class="fa fa-info-circle"></i>
                                                        @lang('employee.other_information')</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.emergency_contact')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->emergency_contacts ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.cost_centre')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->cost_centre ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.pan_gir_no')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->pan_gir_no ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.bank_name')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->bank_name ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.bank_account')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->bank_account ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.bank_ifsc')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->bank_ifsc ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.pf_account_number')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->pf_account_number ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.uan')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->uan ?? '-' }}</div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.esi_card_number')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->esi_card_number ?? 'Not Applicable' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">@lang('employee.pf_status')</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->pf_status ? 'Yes' : 'No' }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">Basic</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->basic ?? 0 }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">HRA</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->hra ?? 0 }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">DA</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->da ?? 0 }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">PF</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->pf ?? 0 }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">EPF</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->epf ?? 0 }}
                                            </div>
                                        </div>
                                        <div class="item">
                                            <div class="col-xs-2 col-sm-2 col-md-3">Insurance</div>
                                            <div class="col-xs-10 col-sm-10 col-md-9">
                                                :&nbsp;&nbsp;&nbsp;&nbsp;{{ $employeeInfo->insurance ?? 0 }}
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>

                            <!-- Document Information -->
                            <div class="education_qualification" style="padding-top: 1cm;">
                                <section class="content">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="panel-custom">
                                                <h3 class="panel-title"><i class="fa fa-laptop"></i> Document
                                                    Information</h3>
                                            </div>
                                            <div class="box">
                                                <div class="box-body">
                                                    <table id="example1" class="table table-bordered table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Sl.No</th>
                                                                <th>Document Title</th>
                                                                <th>Document File</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>1</td>
                                                                <td>{{ $employeeInfo->document_title ?? '-' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>2</td>
                                                                <td>{{ $employeeInfo->document_title2 ?? '-' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name2)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name2 }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>3</td>
                                                                <td>{{ $employeeInfo->document_title3 ?? '-' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name3)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name3 }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>4</td>
                                                                <td>{{ $employeeInfo->document_title4 ?? '-' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name4)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name4 }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>5</td>
                                                                <td>{{ $employeeInfo->document_title5 ?? '-' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name5)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name5 }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>6</td>
                                                                <td>{{ $employeeInfo->document_title6 ?? 'Relieving Letter' }}</td>
                                                                <td>
                                                                    @if ($employeeInfo->document_name6)
                                                                        <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $employeeInfo->document_name6 }}"
                                                                            download><span class="text-info">Download
                                                                                File</span></a>
                                                                    @else
                                                                        {{ '-' }}
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <br>
                            </div>

                            <!-- Connected Devices -->
                            @if (isset($employeeConDevice))
                                <div class="personal_info" hidden>
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <div class="panel-custom">
                                                <h3 class="panel-title"><i class="fa fa-info-circle"></i>
                                                    @lang('employee.conntected_device')</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="personal_info">
                                            <div class="item">
                                                @foreach ($employeeConDevice as $con_device)
                                                    @php
                                                        $device = Device::where('id', $con_device->device)->first();
                                                    @endphp
                                                    <div class="col-xs-6 col-sm-6 col-md-3">{{ $device->name }} (
                                                        {{ $device->model }})
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('page_scripts')
    <script>
        $(document).ready(function() {
            $('.show-pass').click(function(e) {
                e.preventDefault();
                $('.password-element').toggleClass('hide');
                if ($('.password-element').hasClass('hide')) {
                    $('.show-pass .fa').addClass('fa-eye');
                    $('.show-pass .fa').removeClass('fa-eye-slash');
                } else {
                    $('.show-pass .fa').removeClass('fa-eye');
                    $('.show-pass .fa').addClass('fa-eye-slash');
                }
            });
            $('#salary-history-btn').click(function(e) {
                e.preventDefault();
                $('#salary-history-container').slideToggle();
            });
            $('#subordinates-container-btn').click(function(e) {
                e.preventDefault();
                $('#subordinates-container').slideToggle();
            });
            $('.remove-revision').click(function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'GET',
                    url: "{{ Route('employee.salaryRevisionRemoe') }}?sal_id=" + $(this).data(
                        'id') + "&employee_id=" + $(this).data('eid'),
                    success: function(response) {
                        console.log();
                    },
                });

            });
        });
    </script>
@endsection
@endsection
