@php
    use App\Components\Common;
    use App\Model\ViewEmployeeInOutData;
    use App\Model\LeaveApplication;
    use App\Model\HolidayDetails;
    use App\Model\ProfessionalTax;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_sheet') {{ $employee->first_name . ' ' . $employee->last_name }} ( {{ $employee->finger_id }} )
@endsection

<style type="text/css">
    td {
        padding: 4px !important;
    }

    .form-control-sm {
        border: none;
        width: 100%;
        background: transparent;
    }

    /* Chrome, Safari, Edge, Opera */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield !important;
    }
</style>
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
                {{ Form::open(['route' => 'salary.store', 'enctype' => 'multipart/form-data', 'id' => 'holidayForm', 'class' => 'form-horizontal']) }}
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

                        <div class="row">

                            <div class="col-md-12">
                                <table class="table table-bordered table-hover table-striped">

                                    <input type="hidden" name="employee" value="{{ $request->employee_id }}">
                                    <input type="hidden" name="finger_print_id" value="{{ $employee->finger_id }}">
                                    <input type="hidden" name="department" value="{{ $employee->department_id }}">

                                    <tbody>
                                        <tr>
                                            <td>Name</td>
                                            <td><input type="text" name="full_name" id="full_name"
                                                    value="{{ $employee->first_name . ' ' . $employee->last_name }}"
                                                    class="form-control-sm" style="" readonly></td>
                                            <td>Days in Month</td>
                                            <td><input type="text" name="total_days_fromdates"
                                                    id="total_days_fromdates" value="{{ 0 }}"
                                                    class="form-control-sm" style="" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Designation</td>
                                            <td>{{ $employee->designation->designation_name }}</td>

                                        </tr>
                                        <tr>
                                            <td>Department</td>
                                            <td>{{ $employee->department->department_name }}</td>
                                            <td>Holidays</td>
                                            <td><input type="text" name="holidays" id="holidays"
                                                    value="{{ 0 }}" class="form-control-sm" style=""
                                                    readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Employee Category</td>
                                            <td>{{ $employee->category->empcategory_name ?? '-' }}</td>
                                            <td>Casual Leave</td>
                                            <td><input type="text" name="cl" id="cl"
                                                    value="{{ 0 }}" class="form-control-sm" style=""
                                                    readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Date of Birth</td>
                                            <td><input type="text" name="date_of_birth" id="date_of_birth"
                                                    value="{{ $employee->date_of_birth && $employee->date_of_birth != '0000-00-00' && !is_null($employee->date_of_birth) ? DATE('d-m-Y', strtotime($employee->date_of_birth)) : '' }}"
                                                    class="form-control-sm" style="" readonly></td>
                                            <td>Sick Leave</td>
                                            <td><input type="text" name="sl" id="sl"
                                                    value="{{ 0 }}" class="form-control-sm" style=""
                                                    readonly></td>

                                        </tr>
                                        <tr>
                                            <td>Date of Joining</td>
                                            <td><input type="text" name="date_of_joining" id="date_of_joining"
                                                    value="{{ $employee->date_of_joining && $employee->date_of_joining != '0000-00-00' && !is_null($employee->date_of_joining) ? DATE('d-m-Y', strtotime($employee->date_of_joining)) : '' }}"
                                                    class="form-control-sm" style="" readonly></td>
                                            <td>Earned Leave</td>
                                            <td><input type="text" name="el" id="el"
                                                    value="{{ 0 }}" class="form-control-sm" style=""
                                                    readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Yearly CTC</td>
                                            <td><input type="text" name="yearly_ctc" id="yearly_ctc"
                                                    value="{{ round((float) 0, 2) }}" class="form-control-sm"
                                                    style="" readonly></td>
                                            <td>LOP</td>
                                            <td><input type="text" name="lop" id="lop"
                                                    value="{{ 0 }}" class="form-control-sm"
                                                    style="" readonly></td>
                                        </tr>

                                        <input type="hidden" name="month" value="{{ 0 }}">
                                        <input type="hidden" name="year" value="{{ 0 }}">

                                        <tr>
                                            <td>Month</td>
                                            <td><input type="text" name="month_name" id="month_name"
                                                    value="{{ DATE('M', strtotime($request->month)) }}"
                                                    class="form-control-sm" style="" readonly></td>
                                            <td>Absent</td>
                                            <td><input type="text" name="absent" id="absent"
                                                    value="{{ 0 }}" class="form-control-sm"
                                                    style="" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Year</td>
                                            <td><input type="text" name="year_name" id="year_name"
                                                    value="{{ DATE('Y', strtotime($request->month)) }}"
                                                    class="form-control-sm" style="" readonly></td>

                                            <td>Total Working Days</td>
                                            <td><input type="text" name="working_days" id="working_days"
                                                    value="{{ 0 }}" class="form-control-sm"
                                                    style="" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Over Time(In Hour)</td>
                                            <td><input type="text" name="ot_hour" id="ot_hour"
                                                    value="{{ 0 }}" class="form-control-sm"
                                                    style="" readonly></td>
                                            <td>Total Worked Days</td>
                                            <td><input type="text" name="worked_days" id="worked_days"
                                                    value="{{ 0 }}" class="form-control-sm"
                                                    style="" readonly></td>

                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Salary Breakups</h3>
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Basic</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->basic, 2, '.', '') }}</td>
                                            <td>DA</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->da, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>HRA</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->hra, 2, '.', '') }}</td>
                                            <td>PF</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->pf, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>EPF</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->epf, 2, '.', '') }}</td>
                                            <td>INSURANCE</td>
                                            <td class="text-right">
                                                {{ number_format((float) $employee->insurance, 2, '.', '') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h3>Earnings</h3>
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>
                                        <tr>
                                            <td>Basic</td>
                                            <td class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>DA</td>
                                            <td class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>HRA</td>
                                            <td class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Over Time Amount</td>
                                            <td class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Earnings</th>
                                            <th class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}
                                            </th>
                                        </tr>

                                        {{-- <tr>
                                            <th>Total Cost to Company (CTC) </th>
                                            <th class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}
                                            </th>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h3>Deduction</h3>
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>
                                        <tr>
                                            <td>PF</td>
                                            <td class="text-right">
                                                <input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none" name="pf_amount"
                                                    id="pf_amount" value="{{ round((float) 0, 2) }}" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>EPF</td>
                                            <td class="text-right">
                                                {{ number_format((float) 0, 2, '.', '') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Professional Tax</td>
                                            <td class="text-right"><input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none"
                                                    name="professional_tax" id="professional_tax"
                                                    value="{{ round((float) 0, 2) }}" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>LOP</td>
                                            <td class="text-right"><input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none" name="lop_amount"
                                                    id="lop_amount" value="{{ round((float) 0, 2) }}" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Deductions</th>
                                            <th class="text-right"><input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none" name="lop_amount"
                                                    id="lop_amount" value="{{ round((float) 0, 2) }}" readonly>
                                            </th>
                                        </tr>
                                        {{-- <tr>
                                            <td>Income Tax</td>
                                            <td class="text-right">
                                                <input type="number" name="income_tax" id="income_tax"
                                                    class="form-control-sm text-right" style="border: none;width:100%"
                                                    placeholder="0.00">
                                            </td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">





                                <table class="table table-bordered table-hover table-striped">

                                    <tr>
                                        <td><b>Wages Earning</b></td>
                                        <td>
                                            <b>
                                                <input class="form-control-sm text-right" type="number"
                                                    style="border: none;width:100%;background:none"
                                                    name="wages_earnings" id="wages_earnings"
                                                    value="{{ round((float) 0, 2) }}" readonly>
                                            </b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>Deduction</b></td>
                                        <td>
                                            <b>
                                                <input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none" name="deduction"
                                                    id="deduction" type="number" value="{{ round((float) 0, 2) }}"
                                                    readonly>
                                            </b>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td><b>Net Amount</b></td>
                                        <td>
                                            <b>
                                                <input class="form-control-sm text-right"
                                                    style="border: none;width:100%;background:none" name="net_amount"
                                                    id="net_amount" type="number" value="{{ round((float) 0, 2) }}"
                                                    readonly>
                                            </b>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">

                            </div>
                        </div>
                        <br>
                    </div>


                    <input type="submit" name="" value="Generate" class="btn btn-info">
                </div>

                {{ Form::close() }}

                <br><br>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('page_scripts')
<script>
    $('#income_tax').keyup(function(e) {
        e.preventDefault();

        var income_tax = Number($('#income_tax').val());
        var deduction = "{{ 0 }}";
        var d = (+deduction) + (+income_tax);

        deduction = $('#deduction').val(d.toFixed(2));
        var net_amount = "{{ 0 }}";
        var n = (net_amount) - (income_tax);
        $('#net_amount').val(n.toFixed(2));

        if ($('#net_amount').val() <= 0) {
            n = 0.00;
            $('#net_amount').val(n.toFixed(2));
        }
    });
</script>
@endsection
