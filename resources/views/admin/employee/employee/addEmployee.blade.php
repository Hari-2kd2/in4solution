@extends('admin.master')
@section('content')
@section('title')
    @lang('employee.add_employee')
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
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>

        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <a href="{{ route('employee.index') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('employee.view_employee')</a>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    {{ Form::open(['route' => 'employee.store', 'enctype' => 'multipart/form-data', 'id' => 'employeeForm']) }}
                    <div class="panel-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                        aria-hidden="true">X</span></button>
                                @foreach ($errors->all() as $error)
                                    <strong>{!! $error !!}</strong><br>
                                @endforeach
                            </div>
                        @endif

                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">X</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">X</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div class="form-body" style="padding:0 4px;">
                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                Login Information</h4>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.role')<span
                                                class="validateRq">*</span></label>
                                        <select name="role_id" class="form-control user_id required" required>
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($roleList as $value)
                                                <option value="{{ $value->role_id }}"
                                                    @if ($value->role_id == old('role_id')) {{ 'selected' }} @endif>
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
                                        <input class="form-control required user_name" required id="user_name"
                                            placeholder="@lang('employee.user_name')" name="user_name" type="text"
                                            value="{{ old('user_name') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="password">@lang('employee.password')<span class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="ti-lock"></i></div>
                                        <input class="form-control required password" required id="password"
                                            placeholder="@lang('employee.password')" name="password" type="password">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="password_confirmation">@lang('employee.confirm_password')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="ti-lock"></i></div>
                                        <input class="form-control required password_confirmation" required
                                            id="password_confirmation" placeholder="@lang('employee.confirm_password')"
                                            name="password_confirmation" type="password">
                                    </div>
                                </div>
                            </div>

                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                @lang('employee.personal_information')</h4>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="first_name">@lang('employee.first_name')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control required first_name" required id="first_name"
                                            placeholder="@lang('employee.first_name')" name="first_name" type="text"
                                            value="{{ old('first_name') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="last_name">@lang('employee.last_name')</label>
                                        <input class="form-control last_name" id="last_name"
                                            placeholder="@lang('employee.last_name')" name="last_name" type="text"
                                            id="last_name" value="{{ old('last_name') }}">
                                    </div>

                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="gender">@lang('employee.gender')<span
                                                class="validateRq">*</span></label>
                                        <select name="gender" class="form-control gender " required id="gender">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            <option value="Male"
                                                @if ('Male' == old('gender')) {{ 'selected' }} @endif>
                                                @lang('employee.male')
                                            </option>

                                            <option value="Female"
                                                @if ('Female' == old('gender')) {{ 'selected' }} @endif>
                                                @lang('employee.female')
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="blood_group">@lang('employee.blood_group')</label>
                                        <input class="form-control blood_group" id="blood_group"
                                            placeholder="@lang('employee.blood_group')" id="blood_group" name="blood_group"
                                            type="text" value="{{ old('blood_group') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="religion">@lang('employee.religion')</label>
                                        <input class="form-control religion" id="religion"
                                            placeholder="@lang('employee.religion')" id="religion" name="religion"
                                            type="text" value="{{ old('religion') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="email">@lang('employee.email')</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                        <input class="form-control email" id="email"
                                            placeholder="@lang('employee.email')" name="email" type="email"
                                            value="{{ old('email') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="phone">@lang('employee.phone')</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                        <input class="form-control number phone" id="phone"
                                            placeholder="@lang('employee.phone')" name="phone" type="number"
                                            value="{{ old('phone') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="date_of_birth">@lang('employee.date_of_birth')<span
                                                class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control date_of_birth dateField" readonly required
                                                id="date_of_birth" placeholder="@lang('employee.date_of_birth')"
                                                name="date_of_birth" type="text"
                                                value="{{ old('date_of_birth') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="photo">@lang('employee.photo')</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="	fa fa-picture-o"></i></span>
                                            <input class="form-control photo" id="photo"
                                                accept="image/png, image/jpeg, image/gif,image/jpg" name="photo"
                                                type="file">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="marital_status">@lang('employee.marital_status') <span
                                                class="validateRq">*</span></label>
                                        <select name="marital_status" class="form-control marital_status required"
                                            required id="marital_status">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            <option value="Unmarried"
                                                @if ('Unmarried' == old('marital_status')) {{ 'selected' }} @endif>
                                                @lang('employee.unmarried')
                                            </option>

                                            <option value="Married"
                                                @if ('Married' == old('marital_status')) {{ 'selected' }} @endif>
                                                @lang('employee.married')
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="emergency_contacts">@lang('employee.emergency_contact')</label>
                                        <textarea class="form-control emergency_contacts" id="emergency_contacts" placeholder="@lang('employee.emergency_contact')"
                                            cols="30" rows="1" name="emergency_contacts">{{ old('emergency_contacts') }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="no_of_child">@lang('employee.no_of_child')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('no_of_child', Input::old('no_of_child') != '' ? Input::old('no_of_child') : 0, ['class' => 'form-control no_of_child required', 'placeholder' => 'No of Child', 'id' => 'no_of_child']) }}
                                    </div>
                                </div>
                               

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">@lang('common.status')<span
                                                class="validateRq">*</span></label>
                                        <select name="status" class="form-control status" required id="status">
                                            <option value="1"
                                                @if ('1' == old('status')) {{ 'selected' }} @endif>
                                                @lang('common.active')</option>

                                            <option value="2"
                                                @if ('2' == old('status')) {{ 'selected' }} @endif>
                                                @lang('common.inactive')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="address">@lang('employee.address')</label>
                                        <textarea class="form-control address" id="address" placeholder="@lang('employee.address')" cols="30"
                                            rows="2" name="address">{{ old('address') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                Company Information</h4>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="branch_id">@lang('branch.branch_name')<span
                                                class="validateRq">*</span></label>
                                        <select name="branch_id" class="form-control branch_id select2" required
                                            id="branch_id">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @if (session()->get('logged_session_data.role_id') == 1)
                                                @foreach ($branchList as $value)
                                                    <option value="{{ $value->branch_id }}"
                                                        @if ($value->branch_id == old('branch_id')) {{ 'selected' }} @endif>
                                                        {{ $value->branch_name }}
                                                    </option>
                                                @endforeach
                                            @else
                                                @foreach ($branchList as $value)
                                                    <option value="{{ $value->branch_id }}"
                                                        @if ($value->branch_id == old('branch_id')) {{ 'selected' }} @elseif(session()->get('logged_session_data.branch_id') == $value->branch_id)  {{ 'selected hold-disabled' }} @else {{ 'hold-disabled' }} @endif>
                                                        {{ $value->branch_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="department_id">@lang('department.department_name')<span
                                                class="validateRq">*</span></label>
                                        <select name="department_id" class="form-control department_id select2"
                                            id="department_id" required>
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == old('department_id')) {{ 'selected' }} @endif>
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
                                        <select name="designation_id" class="form-control designation_id select2"
                                            id="designation_id" required>
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($designationList as $value)
                                                <option value="{{ $value->designation_id }}"
                                                    @if ($value->designation_id == old('designation_id')) {{ 'selected' }} @endif>
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
                                        {{ Form::select('work_shift', $workShift, Input::old('work_shift'), ['id' => 'work_shift', 'class' => 'form-control work_shift required select2']) }}
                                    </div>
                                </div>


                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="supervisor_id">@lang('employee.supervisor')</label>
                                        <select name="supervisor_id"
                                            class="form-control supervisor_id required select2" id="supervisor_id">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if ($value->employee_id == old('employee_id')) {{ 'selected' }} @endif>
                                                    {{ $value->detailname() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.functional_head')<span
                                                class="validateRq">*</span></label>
                                        <select name="functional_head_id" id="functional_head_id"
                                            class="form-control functional_head_id required select2" required>
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if ($value->employee_id == old('functional_head_id')) {{ 'selected' }} @endif>
                                                    {{ $value->detailname() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="finger_id">@lang('employee.finger_print_no')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control number finger_id" required id="finger_id"
                                            placeholder="@lang('employee.finger_print_no')" name="finger_id" type="text"
                                            value="{{ old('finger_id') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="emp_code">@lang('employee.emp_code')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control number emp_code" id="emp_code"
                                            placeholder="@lang('employee.emp_code')" id="emp_code" name="emp_code"
                                            type="text" value="{{ old('emp_code') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="cost_centre">@lang('employee.cost_centre')</label>
                                        {{ Form::text('cost_centre', Input::old('cost_centre'), ['class' => 'form-control cost_centre', 'placeholder' => 'Cost center', 'id' => 'cost_centre']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="date_of_joining">@lang('employee.date_of_joining')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input class="form-control date_of_joining dateField" readonly required
                                            id="date_of_joining" placeholder="@lang('employee.date_of_joining')"
                                            name="date_of_joining" type="text"
                                            value="{{ old('date_of_joining') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="date_of_leaving">@lang('employee.date_of_leaving')</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control  date_of_leaving dateField" readonly
                                                id="date_of_leaving" placeholder="@lang('employee.date_of_leaving')"
                                                name="date_of_leaving" type="text"
                                                value="{{ old('date_of_leaving') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        @php
                                            $leavingList = \App\Lib\Enumerations\UserStatus::leavingList();
                                        @endphp

                                        <label for="relieving_reason">@lang('employee.relieving_reason')</label>
                                        <select name="relieving_reason"
                                            class="form-control relieving_reason required " id="relieving_reason">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($leavingList as $leavingValue => $leavingText)
                                                <option value="{{ $leavingValue }}"
                                                    @if ($leavingValue == Input::old('relieving_reason')) {{ 'selected' }} @endif>
                                                    {{ $leavingText }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="relieving_remark">@lang('employee.relieving_remark')</label>
                                        <textarea class="form-control relieving_remark" id="reliving_remark" placeholder="@lang('employee.relieving_remark')" cols="30"
                                            rows="2" name="relieving_remark">{{ old('relieving_remark') }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="permanent_status">@lang('common.permanent_status')<span
                                                class="validateRq">*</span></label>
                                        <select name="permanent_status" class="form-control permanent_status" required
                                            id="permanent_status">
                                            <option value="0"
                                                @if ('0' == old('permanent_status')) {{ 'selected' }} @endif>
                                                @lang('employee_permanent.probation_period')</option>

                                            <option value="1"
                                                @if ('1' == old('permenant_status')) {{ 'selected' }} @endif>
                                                @lang('employee_permanent.permanent')</option>
                                            <option value="2"
                                                @if ('2' == old('permenant_status')) {{ 'selected' }} @endif>
                                                @lang('employee_permanent.resigned')</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="overtime_status">@lang('employee.overtime_status')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('overtime_status', $overTime, Input::old('overtime_status'), ['class' => 'form-control overtime_status required', 'id' => 'overtime_status']) }}
                                    </div>
                                </div>


                            </div>

                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                Account Information</h4>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_name">@lang('employee.bank_name')</label>
                                        {{ Form::text('bank_name', Input::old('bank_name') != '' ? Input::old('bank_name') : '', ['class' => 'form-control bank_name required', 'placeholder' => 'Name of the Bank', 'id' => 'bank_name']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_account">@lang('employee.bank_account')</label>
                                        {{ Form::number('bank_account', Input::old('bank_account') != '' ? Input::old('bank_account') : '', ['class' => 'form-control bank_account required', 'placeholder' => 'Account No', 'id' => 'bank_account']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="bank_ifsc">@lang('employee.bank_ifsc')</label>
                                        {{ Form::text('bank_ifsc', Input::old('bank_ifsc') != '' ? Input::old('bank_ifsc') : '', ['class' => 'form-control bank_ifsc required', 'placeholder' => 'IFSC Code', 'id' => 'bank_ifsc']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="uan">@lang('employee.uan')</label>
                                        {{ Form::text('uan', Input::old('uan'), ['class' => 'form-control uan', 'placeholder' => 'UAN No', 'id' => 'uan']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pan_gir_no">@lang('employee.pan_gir_no')</label>
                                        {{ Form::text('pan_gir_no', Input::old('pan_gir_no'), ['class' => 'form-control pan_gir_no', 'placeholder' => 'PAN/GIR NO', 'id' => 'pan_gir_no']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pf_account_number">@lang('employee.pf_account_number')</label>
                                        {{ Form::text('pf_account_number', Input::old('pf_account_number'), ['class' => 'form-control pf_account_number', 'placeholder' => 'PF Number', 'id' => 'pf_account_number']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="esi_card_number">@lang('employee.esi_card_number')</label>
                                        {{ Form::text('esi_card_number', Input::old('esi_card_number'), ['class' => 'form-control esi_card_number', 'placeholder' => 'ESI No', 'id' => 'esi_card_number']) }}
                                    </div>
                                </div>

                            </div>

                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                Salary Information</h4>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pf_status">@lang('employee.pf_status')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::select('pf_status', [1 => 'Yes', 0 => 'No'], Input::old('pf_status'), ['class' => 'form-control pf_status required', 'id' => 'pf_status']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="salary_ctc">@lang('employee.salary_ctc')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('salary_ctc', Input::old('salary_ctc') != '' ? Input::old('salary_ctc') : '', ['class' => 'form-control salary_ctc required', 'placeholder' => 'CTC Amount', 'id' => 'salary_ctc']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="salary_gross">@lang('employee.salary_gross')<span
                                                class="validateRq">*</span></label>
                                        {{ Form::number('salary_gross', Input::old('salary_gross') != '' ? Input::old('salary_gross') : '', ['class' => 'form-control salary_gross required', 'placeholder' => 'Gross Salary', 'id' => 'salary_gross']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="basic">Basic<span class="validateRq">*</span></label>
                                        {{ Form::number('basic', Input::old('basic') != '' ? Input::old('basic') : '', ['class' => 'form-control basic required', 'placeholder' => 'Basic %', 'id' => 'basic']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="hra">HRA<span class="validateRq">*</span></label>
                                        {{ Form::number('hra', Input::old('hra') != '' ? Input::old('hra') : '', ['class' => 'form-control hra required', 'placeholder' => 'HRA %', 'id' => 'hra']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="da">DA<span class="validateRq">*</span></label>
                                        {{ Form::number('da', Input::old('da') != '' ? Input::old('da') : '', ['class' => 'form-control da required', 'placeholder' => 'DA %', 'id' => 'da']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pf">PF<span class="validateRq">*</span></label>
                                        {{ Form::number('pf', Input::old('pf') != '' ? Input::old('pf') : '', ['class' => 'form-control pf required', 'placeholder' => 'PF Amount', 'id' => 'pf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="epf">EPF<span class="validateRq">*</span></label>
                                        {{ Form::number('epf', Input::old('epf') != '' ? Input::old('epf') : '', ['class' => 'form-control epf required', 'placeholder' => 'EPF Amount', 'id' => 'epf']) }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="insurance">Insurance<span class="validateRq">*</span></label>
                                        {{ Form::number('insurance', Input::old('insurance') != '' ? Input::old('insurance') : '', ['class' => 'form-control insurance required', 'placeholder' => 'Insurance Amount', 'id' => 'insurance']) }}
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="salary_revision">@lang('employee.salary_revision')</label>
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input class="form-control salary_revision dateField" id="salary_revision"
                                                readonly placeholder="@lang('employee.salary_revision')" name="salary_revision"
                                                type="text"
                                                value="{{ dateConvertDBtoForm(old('salary_revision')) }}"
                                                title="ESI End: {{ dateConvertDBtoForm(old('salary_esi_stop')) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="bg-title bg-white"
                                style="text-transform: uppercase;font-weight:400;padding: 6px 12px;">
                                Document Information</h4>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title"
                                            value="10th Mark sheet" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file"
                                            type="file">
                                    </div>
                                </div>
                            </div>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title2"
                                            value="Degree Certificate" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file2"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file2"
                                            type="file">
                                    </div>
                                </div>
                            </div>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title3"
                                            value="Address Proof" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file3"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file3"
                                            type="file">
                                    </div>
                                </div>
                            </div>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title4"
                                            value="Aadhar Card" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file4"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file4"
                                            type="file">
                                    </div>
                                </div>
                            </div>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title5"
                                            value="PAN card" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file5"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file5"
                                            type="file">
                                    </div>
                                </div>
                            </div>

                            <div class="row p-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="document_title6"
                                            value="Relieving Letter" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input class="form-control photo" id="document-file6"
                                            accept="image/png, image/jpeg, application/pdf" name="document_file6"
                                            type="file">
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- if need any other detail admin.employee.employee.employeeOtherDetail we can add future --}}
                        {{-- @include('admin.employee.employee.employeeOtherDetail') --}}
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info btn_style"><i
                                            class="fa fa-pencil"></i> @lang('common.save')</button>
                                </div>
                            </div>
                        </div>
                    </div> <!-- panel body end -->
                    {{ Form::close() }}
                </div> <!-- panel wrapper end -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    $(document).ready(function() {
        $('#addEducationQualification').click(function() {
            $('.education_qualification_append_div').append(
                '<div class="education_qualification_row_element">' + $('.row_element1').html() +
                '</div>');
        });

        $('#addExperience').click(function() {
            $('.experience_append_div').append('<div class="experience_row_element">' + $(
                '.row_element2').html() + '</div>');
        });

        $(document).on("click", ".deleteEducationQualification", function() {
            $(this).parents('.education_qualification_row_element').remove();
            var deletedID = $(this).parents('.education_qualification_row_element').find(
                '.educationQualification_cid').val();

            if (deletedID) {
                var prevDelId = $('#delete_education_qualifications_cid').val();
                if (prevDelId) {
                    $('#delete_education_qualifications_cid').val(prevDelId + ',' + deletedID);
                } else {
                    $('#delete_education_qualifications_cid').val(deletedID);
                }
            }
        });



        $(document).on("click", ".deleteExperience", function() {
            $(this).parents('.experience_row_element').remove();
            var deletedID = $(this).parents('.experience_row_element').find('.employee_experience_id')
                .val();

            if (deletedID) {
                var prevDelId = $('#delete_experiences_cid').val();
                if (prevDelId) {
                    $('#delete_experiences_cid').val(prevDelId + ',' + deletedID);
                } else {
                    $('#delete_experiences_cid').val(deletedID);
                }
            }
        });

        $(document).on('change', '.pay_grade_id', function() {
            var data = $('.pay_grade_id').val();
            if (data) {
                $('.hourly_pay_grade_id').val('');
                $('.pay_grade_id').attr('required', false);
                $('.hourly_pay_grade_id').attr('required', false);
            } else {
                $('.pay_grade_id').attr('required', true);
                $('.hourly_pay_grade_id').attr('required', true);
            }
        });

        $(document).on('change', '.hourly_pay_grade_id', function() {
            var data = $('.hourly_pay_grade_id').val();
            if (data) {
                $('.pay_grade_id').val('');
                $('.pay_grade_id').attr('required', false);
                $('.hourly_pay_grade_id').attr('required', false);
            } else {
                $('.pay_grade_id').attr('required', true);
                $('.hourly_pay_grade_id').attr('required', true);
            }
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('change', '.branch_id', function() {
            var branch_id = $(this).val();
            $('#designation_id').html('');
            $('#department_id').html('');
            $('#work_shift').html('');
            $('#supervisor_id').html('');
            $('#functional_head_id').html('');

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
                        $('#designation_id').html(html);
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
                        $('#department_id').html(html1);
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
                        $('#work_shift').html(html2);
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
                $.ajax({
                    type: 'GET',
                    url: "{{ route('employee.functionalhead') }}",
                    data: {
                        'branch_id': branch_id
                    },
                    dataType: "json",
                    success: function(response) {
                        let html4 = '<option value>- Select Functional Head -</option>';
                        for (k in response) {
                            html4 += '<option value="' + response[k].employee_id + '">' +
                                response[k].detailname + '</option>'
                        }
                        $('#functional_head_id').html(html4);
                    },
                });
            }

        });
    });
</script>
@endsection
