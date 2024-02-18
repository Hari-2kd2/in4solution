@php
    use App\Model\AccessControl;
    use App\Lib\Enumerations;

@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('employee.profile')
@endsection

<style>
    .appendBtnColor {
        color: #fff;
        font-weight: 700;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i
                            class="fa fa-home"></i>@lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ url('profile') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('common.view-profile')</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    {{ Form::model($editModeData, ['route' => ['profile.store', $editModeData->employee_id], 'method' => 'POST', 'files' => 'true', 'id' => 'employeeProfileForm']) }}

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

                        <div class="form-body" style="padding: 0 4px;">

                            <h3 class="box-title" style="text-transform: uppercase;font-weight:400;padding:12px;">
                                @lang('employee.employee_account')</h3>
                            <hr>
                            <div class="row" style="pointer-events: none;">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="role_id">@lang('employee.role')<span class="validateRq">*</span></label>
                                        <select name="role_id" class="form-control user_id required readonly" required
                                            readonly id="role_id">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($roleList as $value)
                                                <option value="{{ $value->role_id }}"
                                                    @if ($value->role_id == $employeeAccountEditModeData->role_id) {{ 'selected' }} @endif>
                                                    {{ $value->role_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="user_name">@lang('employee.user_name')<span class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="ti-user"></i></div>
                                        <input class="form-control required user_name" required readonly id="user_name"
                                            placeholder="@lang('employee.user_name')" name="user_name" type="text"
                                            value="{{ $employeeAccountEditModeData->user_name }}">
                                    </div>
                                </div>
                            </div>

                            <h3 class="box-title" style="text-transform: uppercase;font-weight:400;padding:12px;">
                                @lang('employee.personal_information')</h3>
                            <hr>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="first_name">@lang('employee.first_name')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control required first_name" id="first_name"
                                            placeholder="@lang('employee.first_name')" id="first_name" name="first_name"
                                            type="text" value="{{ $editModeData->first_name }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="last_name">@lang('employee.last_name')</label>
                                        <input class="form-control last_name" id="last_name"
                                            placeholder="@lang('employee.last_name')" name="last_name" type="text"
                                            value="{{ $editModeData->last_name }}">
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="finger_id">@lang('employee.finger_print_no')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control number finger_id" readonly id="finger_id"
                                            placeholder="@lang('employee.finger_print_no')" id="finger_id" name="finger_id"
                                            type="text" value="{{ $editModeData->finger_id }}">
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="emp_code">@lang('employee.emp_code')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control number emp_code" readonly id="emp_code"
                                            placeholder="@lang('employee.emp_code')" id="emp_code" name="emp_code"
                                            type="text" value="{{ $editModeData->emp_code }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="branch_name">@lang('branch.branch_name')<span
                                                class="validateRq">*</span></label>
                                        <select name="branch_id" id="branch_name"
                                            class="form-control branch_id select2">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($branchList as $value)
                                                <option value="{{ $value->branch_id }}"
                                                    @if ($value->branch_id == $editModeData->branch_id) {{ 'selected' }} @endif>
                                                    {{ $value->branch_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="department_id">@lang('department.department_name')<span
                                                class="validateRq">*</span></label>
                                        <select name="department_id" id="department_id"
                                            class="form-control department_id select2">
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $editModeData->department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="designation_id">@lang('designation.designation_name')<span
                                                class="validateRq">*</span></label>
                                        <select name="designation_id" id="designation_id"
                                            class="form-control designation_id select2">
                                            @foreach ($designationList as $value)
                                                <option value="{{ $value->designation_id }}"
                                                    @if ($value->designation_id == $editModeData->designation_id) {{ 'selected' }} @endif>
                                                    {{ $value->designation_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="work_shift">@lang('employee.work_shift')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('work_shift', $workShift, Input::old('work_shift'), ['class' => 'form-control work_shift required select2', 'id' => 'work_shift']) }}
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.functional_head')</label>
                                        <select name="functional_head_id"
                                            class="form-control functional_head_id select2">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($operationManagerList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if ($value->employee_id == $editModeData->functional_head_id) {{ 'selected' }} @endif>
                                                    {{ $value->first_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none">
                                    <div class="form-group">
                                        @php
                                            $Employee = new \App\Model\Employee();
                                            $supervisor_id = Input::old('employee_id') ? Input::old('employee_id') : $editModeData->supervisor_id;
                                        @endphp

                                        <label for="supervisor_id">@lang('employee.supervisor')</label>
                                        <select name="supervisor_id"
                                            class="form-control supervisor_id required select2" id="supervisor_id">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($supervisorList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if ($value->employee_id == $supervisor_id) {{ 'selected' }} @endif>
                                                    {{ $value->detailname() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="uan">@lang('employee.uan')</label>
                                        {{ Form::text('uan', Input::old('uan'), ['class' => 'form-control uan', 'id' => 'uan']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cost_centre">@lang('employee.cost_centre')</label>
                                        {{ Form::text('cost_centre', Input::old('cost_centre'), ['class' => 'form-control cost_centre', 'id' => 'cost_centre']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pan_gir_no">@lang('employee.pan_gir_no')</label>
                                        {{ Form::text('pan_gir_no', Input::old('pan_gir_no'), ['class' => 'form-control pan_gir_no', 'id' => 'pan_gir_no']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pf_account_number">@lang('employee.pf_account_number')</label>
                                        {{ Form::text('pf_account_number', Input::old('pf_account_number'), ['class' => 'form-control pf_account_number', 'id' => 'pf_account_number']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="esi_card_number">@lang('employee.esi_card_number')</label>
                                        {{ Form::text('esi_card_number', Input::old('esi_card_number'), ['class' => 'form-control esi_card_number', 'id' => 'esi_card_number']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender">@lang('employee.gender')<span
                                                class="validateRq">*</span></label>
                                        <select name="gender" id="gender" class="form-control gender">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            <option value="Male"
                                                @if ('Male' == $editModeData->gender) {{ 'selected' }} @endif>
                                                @lang('employee.male')</option>
                                            <option value="Female"
                                                @if ('Female' == $editModeData->gender) {{ 'selected' }} @endif>
                                                @lang('employee.female')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="religion">@lang('employee.religion')</label>
                                        <input class="form-control religion" id="religion"
                                            placeholder="@lang('employee.religion')" name="religion" type="text"
                                            value="{{ $editModeData->religion }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="email">@lang('employee.email')</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                            <input class="form-control email" id="email"
                                                placeholder="@lang('employee.email')" name="email" type="email"
                                                value="{{ $editModeData->email }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="phone">@lang('employee.phone')<span
                                                class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                            <input class="form-control number phone" id="phone"
                                                placeholder="@lang('employee.phone')" name="phone" type="number"
                                                value="{{ $editModeData->phone }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <label for="date_of_birth">@lang('employee.date_of_birth')<span
                                            class="validateRq">*</span></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control date_of_birth dateField" id="date_of_birth"
                                                readonly placeholder="@lang('employee.date_of_birth')" name="date_of_birth"
                                                type="text"
                                                value="{{ dateConvertDBtoForm($editModeData->date_of_birth) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <label for="date_of_joining">@lang('employee.date_of_joining')<span
                                            class="validateRq">*</span></label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control date_of_joining dateField" id="date_of_joining"
                                                readonly placeholder="@lang('employee.date_of_joining')" name="date_of_joining"
                                                type="text"
                                                value="{{ dateConvertDBtoForm($editModeData->date_of_joining) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <label for="date_of_leaving">@lang('employee.date_of_leaving')</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control  date_of_leaving dateField"
                                                id="date_of_leaving" readonly placeholder="@lang('employee.date_of_leaving')"
                                                name="date_of_leaving" type="text"
                                                value="{{ dateConvertDBtoForm($editModeData->date_of_leaving) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        @php
                                            $leavingList = \App\Lib\Enumerations\UserStatus::leavingList();
                                            $relieving_reason = Input::old('relieving_reason') ? Input::old('relieving_reason') : $editModeData->relieving_reason;
                                        @endphp

                                        <label for="relieving_reason">@lang('employee.relieving_reason')</label>
                                        <select name="relieving_reason"
                                            class="form-control relieving_reason required " id="relieving_reason">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($leavingList as $leavingValue => $leavingText)
                                                <option value="{{ $leavingValue }}"
                                                    @if ($leavingValue == $relieving_reason) {{ 'selected' }} @endif>
                                                    {{ $leavingText }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="marital_status">@lang('employee.marital_status')</label>
                                        <select name="marital_status" class="form-control status required">
                                            <option value="">--- Please select ---</option>
                                            <option value="Unmarried"
                                                @if ('Unmarried' == $editModeData->marital_status) {{ 'selected' }} @endif>
                                                @lang('employee.unmarried')</option>
                                            <option value="Married"
                                                @if ('Married' == $editModeData->marital_status) {{ 'selected' }} @endif>
                                                @lang('employee.married')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="no_of_child">@lang('employee.no_of_child')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('no_of_child', $editModeData->no_of_child != '' ? $editModeData->no_of_child : 0, ['class' => 'form-control no_of_child required', 'id' => 'no_of_child']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Status<span class="validateRq">*</span></label>
                                        <select name="status" id="status" class="form-control status">
                                            <option value="1"
                                                @if ('1' == $editModeData->status) {{ 'selected' }} @endif>
                                                @lang('common.active')</option>
                                            <option value="2"
                                                @if ('2' == $editModeData->status) {{ 'selected' }} @endif>
                                                @lang('common.inactive')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="overtime_status">@lang('employee.overtime_status')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('overtime_status', $overTime, Input::old('overtime_status'), ['class' => 'form-control overtime_status required', 'readonly' => 'readonly', 'id' => 'overtime_status']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="pf_status">@lang('employee.pf_status')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('pf_status', [1 => 'Yes', 0 => 'No'], Input::old('pf_status') ? Input::old('pf_status') : $editModeData->pf_status, ['class' => 'form-control pf_status required', 'readonly' => 'readonly', 'id' => 'pf_status']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="bank_name">@lang('employee.bank_name')</label>
                                        {{ Form::text('bank_name', Input::old('bank_name') != '' ? Input::old('bank_name') : $editModeData->bank_name, ['class' => 'form-control bank_name required', 'id' => 'bank_name']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_account">@lang('employee.bank_account')</label>
                                        {{ Form::number('bank_account', Input::old('bank_account') != '' ? Input::old('bank_account') : $editModeData->bank_account, ['class' => 'form-control bank_account required', 'id' => 'bank_account']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_ifsc">@lang('employee.bank_ifsc')</label>
                                        {{ Form::text('bank_ifsc', Input::old('bank_ifsc') != '' ? Input::old('bank_ifsc') : $editModeData->bank_ifsc, ['class' => 'form-control bank_ifsc required', 'id' => 'bank_ifsc']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="salary_ctc">@lang('employee.salary_ctc')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('salary_ctc', Input::old('salary_ctc') != '' ? Input::old('salary_ctc') : $editModeData->salary_ctc, ['class' => 'form-control salary_ctc required', 'readonly' => 'readonly', 'id' => 'salary_ctc']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="salary_gross">@lang('employee.salary_gross')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('salary_gross', Input::old('salary_gross') != '' ? Input::old('salary_gross') : $editModeData->salary_gross, ['class' => 'form-control salary_gross required', 'readonly' => 'readonly', 'id' => 'salary_gross']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="basic">Basic<span class="validateRq">*</span></label>
                                        {{ Form::number('basic', Input::old('basic') != '' ? Input::old('basic') : $editModeData->basic, ['class' => 'form-control basic required', 'readonly' => 'readonly', 'id' => 'basic']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="hra">HRA<span class="validateRq">*</span></label>
                                        {{ Form::number('hra', Input::old('hra') != '' ? Input::old('hra') : $editModeData->hra, ['class' => 'form-control hra required', 'readonly' => 'readonly', 'id' => 'hra']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="da">DA<span class="validateRq">*</span></label>
                                        {{ Form::number('da', Input::old('da') != '' ? Input::old('da') : $editModeData->da, ['class' => 'form-control da required', 'readonly' => 'readonly', 'id' => 'da']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="pf">PF<span class="validateRq">*</span></label>
                                        {{ Form::number('pf', Input::old('pf') != '' ? Input::old('pf') : $editModeData->pf, ['class' => 'form-control pf required', 'readonly' => 'readonly', 'id' => 'pf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="epf">EPF<span class="validateRq">*</span></label>
                                        {{ Form::number('epf', Input::old('epf') != '' ? Input::old('epf') : $editModeData->epf, ['class' => 'form-control epf required', 'readonly' => 'readonly', 'id' => 'epf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3" style="pointer-events: none;">
                                    <div class="form-group">
                                        <label for="insurance">Insurance<span class="validateRq">*</span></label>
                                        {{ Form::number('insurance', Input::old('insurance') != '' ? Input::old('insurance') : $editModeData->insurance, ['class' => 'form-control insurance required', 'readonly' => 'readonly', 'id' => 'insurance']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="basic">Basic<span class="validateRq">*</span></label>
                                        {{ Form::number('basic', Input::old('basic') != '' ? Input::old('basic') : $editModeData->basic, ['class' => 'form-control basic required', 'readonly' => 'readonly', 'id' => 'basic']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="hra">HRA<span class="validateRq">*</span></label>
                                        {{ Form::number('hra', Input::old('hra') != '' ? Input::old('hra') : $editModeData->hra, ['class' => 'form-control hra required', 'readonly' => 'readonly', 'id' => 'hra']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="da">DA<span class="validateRq">*</span></label>
                                        {{ Form::number('da', Input::old('da') != '' ? Input::old('da') : $editModeData->da, ['class' => 'form-control da required', 'readonly' => 'readonly', 'id' => 'da']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pf">PF<span class="validateRq">*</span></label>
                                        {{ Form::number('pf', Input::old('pf') != '' ? Input::old('pf') : $editModeData->pf, ['class' => 'form-control pf required', 'readonly' => 'readonly', 'id' => 'pf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="epf">EPF<span class="validateRq">*</span></label>
                                        {{ Form::number('epf', Input::old('epf') != '' ? Input::old('epf') : $editModeData->epf, ['class' => 'form-control epf required', 'readonly' => 'readonly', 'id' => 'epf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="insurance">Insurance<span class="validateRq">*</span></label>
                                        {{ Form::number('insurance', Input::old('insurance') != '' ? Input::old('insurance') : $editModeData->insurance, ['class' => 'form-control insurance required', 'readonly' => 'readonly', 'id' => 'insurance']) }}
                                    </div>
                                </div>

                                <div class="col-md-3" style="pointer-events: none;">
                                    <label for="salary_revision">@lang('employee.salary_revision')</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control salary_revision dateField" id="salary_revision"
                                                readonly placeholder="@lang('employee.salary_revision')" name="salary_revision"
                                                type="text"
                                                value="{{ dateConvertDBtoForm($editModeData->salary_revision) }}"
                                                title="ESI End: {{ dateConvertDBtoForm($editModeData->salary_esi_stop) }}">
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="emergency_contact">@lang('employee.emergency_contact')</label>
                                        <textarea class="form-control emergency_contacts" id="emergency_contacts" placeholder="@lang('employee.emergency_contact')"
                                            cols="30" rows="2" name="emergency_contacts">{{ $editModeData->emergency_contacts }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="address">@lang('employee.address')</label>
                                        <textarea class="form-control address" id="address" placeholder="@lang('employee.address')" cols="30"
                                            rows="2" name="address">{{ $editModeData->address }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="photo">@lang('employee.photo')</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="	fa fa-picture-o"></i></span>
                                        <input class="form-control photo" id="photo"
                                            accept="image/png, image/jpeg, image/gif,image/jpg" name="photo"
                                            type="file">
                                    </div>
                                </div>
                            </div>
                            <br>

                            <h3 class="bg-title" style="text-transform: uppercase;font-weight:400;padding:12px;">
                                Employee Document</h3>
                            <hr>

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
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title" value="10th Mark sheet"
                                                                        readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif

                                                            </tr>

                                                            <tr>

                                                                <td>2</td>
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title2"
                                                                        value="Degree Certificate" readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name2)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name2 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file2" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name2)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name2 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file2" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif
                                                            </tr>

                                                            <tr>

                                                                <td>3</td>
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title3" value="Address Proof"
                                                                        readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name3)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name3 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file3" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name3)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name3 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file3" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif
                                                            </tr>

                                                            <tr>

                                                                <td>4</td>
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title4" value="Aadhar Card"
                                                                        readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name4)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name4 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file4" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name4)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name4 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file4" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif
                                                            </tr>

                                                            <tr>

                                                                <td>5</td>
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title5" value="PAN card"
                                                                        readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name5)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name5 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file5" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name5)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name5 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file5" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif
                                                            </tr>
                                                            <tr>

                                                                <td>6</td>
                                                                <td> <input type="text" class="form-control"
                                                                        name="document_title6"
                                                                        value="Relieving Letter" readonly></td>

                                                                @if (session('logged_session_data.role_id') == 1)
                                                                    @if ($editModeData->document_name6)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name6 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file6" type="file">
                                                                        </td>
                                                                    @endif
                                                                @else
                                                                    @if ($editModeData->document_name6)
                                                                        <td>
                                                                            <a href="{{ asset('/uploads/employeeDocuments/') }}/{{ $editModeData->document_name6 }}"
                                                                                download><span
                                                                                    class="text-info">Download
                                                                                    File</span></a>
                                                                        </td>
                                                                    @else
                                                                        <td><input class="form-control photo"
                                                                                id="document-file"
                                                                                accept="image/png, image/jpeg, application/pdf"
                                                                                name="document_file6" type="file">
                                                                        </td>
                                                                    @endif
                                                                @endif
                                                            </tr>

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-info btn_style"><i
                                                class="fa fa-pencil"></i>
                                            <span>@lang('common.request')</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> {{-- panel body end --}}
                        {{ Form::close() }}
                    </div> {{-- panel wrapper end --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            $(document).on('change', '.branch_id', function() {
                var branch_id = $(this).val();
                $('.designation_id').html('');
                $('.department_id').html('');
                $('.work_shift').html('');
                $('.supervisor_id').html('');
                if (branch_id) {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('employee.getdesignation') }}",
                        data: {
                            'branch_id': branch_id
                        },
                        dataType: "json",
                        success: function(response) {
                            let html = '<option value>- Select Designation -</option>';
                            for (i in response) {
                                html += '<option value="' + response[i].designation_id + '">' +
                                    response[i].designation_name + '</option>'
                            }
                            $('.designation_id').html(html);
                        },
                    });
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('employee.getdepartment') }}",
                        data: {
                            'branch_id': branch_id
                        },
                        dataType: "json",
                        success: function(response) {
                            let html1 = '<option value>- Select Department -</option>';
                            for (j in response) {
                                html1 += '<option value="' + response[j].department_id + '">' +
                                    response[j].department_name + '</option>'
                            }
                            $('.department_id').html(html1);
                        },
                    });
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('employee.workshift') }}",
                        data: {
                            'branch_id': branch_id
                        },
                        dataType: "json",
                        success: function(response) {
                            let html2 = '<option value>- Select Work Shift -</option>';
                            for (k in response) {
                                html2 += '<option value="' + response[k].work_shift_id + '">' +
                                    response[k].shift_name + '</option>'
                            }
                            $('.work_shift').html(html2);
                        },
                    });
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('employee.supervisor') }}",
                        data: {
                            'branch_id': branch_id
                        },
                        dataType: "json",
                        success: function(response) {
                            let html3 = '<option value>- Select Supervisor -</option>';
                            for (k in response) {
                                html3 += '<option value="' + response[k].employee_id + '">' +
                                    response[k].detailname + '</option>'
                            }
                            $('#supervisor_id').html(html3);
                        },
                    });
                }

            });
        });
    </script>
@endsection
