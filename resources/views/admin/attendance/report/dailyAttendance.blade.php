@php
    use App\Model\EmployeeInOutData;
    use App\Model\Employee;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.daily_attendance')
@endsection
<script>
    jQuery(function() {
        $("#dailyAttendanceReport").validate();
    });
</script>
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
    @php
        // dd($departmentList);
    @endphp
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
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
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div id="searchBox">
                            {{ Form::open([
                                'route' => 'dailyAttendance.dailyAttendance',
                                'id' => 'dailyAttendanceReport',
                                // 'class' => 'form-horizontal',
                            ]) }}


                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.department')</label>
                                        <select name="department_id" class="form-control department_id  select2">
                                            <option value="">--- @lang('employee.select_department') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == old('department_id')) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('employee.designation')</label>
                                        <select name="designation_id" class="form-control designation_id select2">
                                            <option value="">--- @lang('employee.select_designation') ---</option>
                                            @foreach ($designationList as $value)
                                                <option value="{{ $value->designation_id }}"
                                                    @if ($value->designation_id == old('designation_id')) {{ 'selected' }} @endif>
                                                    {{ $value->designation_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('common.branch_name')</label>
                                        <select name="branch_id" class="form-control branch_id select2">
                                            <option value="">--- @lang('employee.select_branch') ---</option>
                                            @foreach ($branchList as $value)
                                                <option value="{{ $value->branch_id }}"
                                                    @if ($value->branch_id == old('branch_id')) {{ 'selected' }} @endif>
                                                    {{ $value->branch_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.status')</label>
                                        <select name="attendance_status"
                                            class="form-control attendance_status  select2">
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($AttendanceStatusID as $key => $value)
                                                <option value="{{ $key }}"
                                                    @if ($key == $attendance_status) {{ 'selected' }} @endif>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('common.name')</label>
                                        <div id="custom-search-input">
                                            <div class="input-group col-md-12">
                                                <input type="text" class="search-query form-control employee_name"
                                                    placeholder=" @lang('employee.name')" />
                                                <span class="input-group-btn">
                                                    <button class="btn btn-cancel" type="button">
                                                        <span class="glyphicon glyphicon-search"></span>
                                                    </button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.from_date')<span
                                                class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control dateField required" id="from_date"
                                                readonly placeholder="@lang('common.date')" name="from_date"
                                                value="{{ $from_date }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.to_date')<span
                                                class="validateRq">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control dateField required"
                                                id="to_date" readonly placeholder="@lang('common.date')"
                                                name="to_date" value="{{ $to_date }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 28px;"
                                            class="btn btn-info btn-md" value="@lang('common.filter')">
                                    </div>
                                </div>

                            </div>
                            {{ Form::close() }}

                        </div>
                        <hr>

                        <div id="btableData">
                            <div class="table-responsive">
                                <table id="myDataTable" class="table table-bordered" style="font-size: 12px;">
                                    <thead class="tr_header bg-title">
                                        <tr>
                                            <th style="width:50px;">@lang('common.serial')</th>
                                            <th style="font-size:12px;">@lang('common.date')</th>
                                            <th style="font-size:12px;">@lang('common.employee_name')</th>
                                            <th style="font-size:12px;">@lang('common.id')</th>
                                            <th style="font-size:12px;">@lang('attendance.department')</th>
                                            <th style="font-size:12px;">@lang('attendance.shift')</th>
                                            <th style="font-size:12px;">@lang('attendance.in_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.out_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.duration')</th>
                                            <th style="font-size:12px;">@lang('attendance.early_by')</th>
                                            <th style="font-size:12px;">@lang('attendance.late_by')</th>
                                            <th style="font-size:12px;">@lang('attendance.over_time')</th>
                                            <th style="font-size:12px;">@lang('attendance.history_of_records')</th>
                                            <th style="font-size:12px;;">@lang('attendance.status')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {!! $sl = null !!}
                                        @foreach ($results as $dept => $result)
                                            @foreach ($result as $key => $value)
                                                @if (session('logged_session_data.role_id') == 1)
                                                    @php
                                                        $EmployeeInOutData = EmployeeInOutData::find($value->employee_attendance_id) ?? new EmployeeInOutData();
                                                        $Employee =
                                                            DB::table('employee')
                                                                ->where('employee_id', $value->employee_id)
                                                                ->first() ?? new Employee();
                                                        // fileter only half day present
                                                        if ($EmployeeInOutData) {
                                                            if ($request->attendance_status == '.' && !$EmployeeInOutData->halfday_status) {
                                                                continue;
                                                            }
                                                            if ($request->attendance_status == 1 && $EmployeeInOutData->halfday_status > 0) {
                                                                continue;
                                                            }
                                                        }

                                                        $zero = '-:-';
                                                        $isHoliday = false;
                                                        $holidayDate = '';
                                                    @endphp
                                                    <tr>
                                                        <td style="font-size:12px;">{{ ++$sl }}</td>
                                                        <td style="font-size:12px;">
                                                            {{ dateConvertDBtoForm($value->date) }}</td>
                                                        <td style="font-size:12px;"
                                                            title="{{ $value->branch_name }}">
                                                            {{ $value->fullName }}</td>
                                                        <td style="font-size:12px;">{{ $value->finger_print_id }}
                                                        </td>
                                                        <td style="font-size:12px;">{{ $dept }}</td>
                                                        <td style="font-size:12px;">
                                                            {{ $value->shift_name ?? 'N/A' }}</td>
                                                        <td style="font-size:12px;white-space:nowrap">
                                                            @php
                                                                if ($value->in_time != '') {
                                                                    echo $value->in_time;
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td style="font-size:12px;white-space:nowrap">
                                                            @php
                                                                if ($value->out_time != '') {
                                                                    echo $value->out_time;
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td style="font-size:12px;">
                                                            @php
                                                                if ($value->working_time != null) {
                                                                    echo date('H:i', strtotime($value->working_time));
                                                                    // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td style="font-size:12px;">
                                                            @php
                                                                if ($value->early_by != null) {
                                                                    echo date('H:i', strtotime($value->early_by));
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td style="font-size:12px;">
                                                            @php
                                                                if ($value->late_by != null) {
                                                                    echo date('H:i', strtotime($value->late_by));
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>
                                                        <td style="font-size:12px;">
                                                            @php
                                                                $OVER_TIME = '-';
                                                                if ($EmployeeInOutData && $EmployeeInOutData->over_time) {
                                                                    $date = new DateTime($EmployeeInOutData->over_time);
                                                                    if ($Employee->overtime_status == 1) {
                                                                        $startTime = Carbon::parse('00:00:00');
                                                                        $finishTime = Carbon::parse($EmployeeInOutData->over_time);
                                                                        $overTimes = $finishTime->diffInMinutes($startTime);
                                                                        $hours = $overTimes / 60;
                                                                        $OT_TOTAL += $hours;
                                                                        $OVER_TIME = '<span class="over-time yes">OT Hr: ' . $date->format('H:i') . '</span>';
                                                                    } else {
                                                                        $OVER_TIME = '<span class="over-time no">Exctra ' . $date->format('H:i') . '</span>';
                                                                    }
                                                                }
                                                                echo $OVER_TIME;
                                                                // if (isset($value->overtime_approval->actual_overtime) && $value->overtime_approval->actual_overtime != null) {
                                                                //     echo 'OT Hr: ' . date('H:i', strtotime($value->overtime_approval->actual_overtime)) . '<br>' . 'Appr. Hr: ' . date('H:i', strtotime($value->overtime_approval->approved_overtime)) . '<br>' . 'Remark: ' . $value->overtime_approval->remark;
                                                                // } else {
                                                                //     echo 'OT Hr: ' . ($value->over_time ? date('H:i', strtotime($value->over_time)) : '-') . '<br>' . 'Appr. Hr: ' . '-' . '<br>' . 'Remark: ' . '-';
                                                                // }
                                                            @endphp
                                                        </td>

                                                        <td style="font-size:12px;">
                                                            @php
                                                                if ($value->in_out_time != null) {
                                                                    echo $value->in_out_time;
                                                                } else {
                                                                    echo $zero;
                                                                }
                                                            @endphp
                                                        </td>

                                                        <td style="font-size:12px;">
                                                            <?php
                                                            if ($value->attendance_status == 13) {
                                                                echo 'OD';
                                                            } else {
                                                                echo attStatus($value->attendance_status) . ($EmployeeInOutData->halfday_status ? ' ½' : '');
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @if (session('logged_session_data.branch_id') == $value->branch_id)
                                                        @php
                                                            $zero = '00:00';
                                                            $isHoliday = false;
                                                            $holidayDate = '';
                                                        @endphp
                                                        <tr>
                                                            <td style="font-size:12px;">{{ ++$sl }}</td>
                                                            <td style="font-size:12px;">{{ $value->date }}</td>
                                                            <td style="font-size:12px;">{{ $value->fullName }}</td>
                                                            <td style="font-size:12px;">{{ $value->finger_print_id }}
                                                            </td>
                                                            <td style="font-size:12px;">{{ $dept }}</td>
                                                            <td style="font-size:12px;">
                                                                {{ $value->shift_name ?? 'N/A' }}</td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->in_time != '') {
                                                                        echo $value->in_time;
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->out_time != '') {
                                                                        echo $value->out_time;
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->working_time != null) {
                                                                        echo date('H:i', strtotime($value->working_time));
                                                                        // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->early_by != null) {
                                                                        echo date('H:i', strtotime($value->early_by));
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->late_by != null) {
                                                                        echo date('H:i', strtotime($value->late_by));
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>
                                                            <td style="font-size:12px;">
                                                                @php
                                                                    $OVER_TIME = '-';
                                                                    if ($EmployeeInOutData && $EmployeeInOutData->over_time) {
                                                                        $date = new DateTime($EmployeeInOutData->over_time);
                                                                        if ($Employee->overtime_status == 1) {
                                                                            $startTime = Carbon::parse('00:00:00');
                                                                            $finishTime = Carbon::parse($EmployeeInOutData->over_time);
                                                                            $overTimes = $finishTime->diffInMinutes($startTime);
                                                                            $hours = $overTimes / 60;
                                                                            $OT_TOTAL += $hours;
                                                                            $OVER_TIME = '<span class="over-time yes">OT Hr: ' . $date->format('H:i') . '</span>';
                                                                        } else {
                                                                            $OVER_TIME = '<span class="over-time no">Exctra ' . $date->format('H:i') . '</span>';
                                                                        }
                                                                    }
                                                                    echo $OVER_TIME;
                                                                    // if (isset($value->overtime_approval->actual_overtime) && $value->overtime_approval->actual_overtime != null) {
                                                                    //     echo 'OT Hr: ' . date('H:i', strtotime($value->overtime_approval->actual_overtime)) . '<br>' . 'Appr. Hr: ' . date('H:i', strtotime($value->overtime_approval->approved_overtime)) . '<br>' . 'Remark: ' . $value->overtime_approval->remark;
                                                                    // } else {
                                                                    //     echo 'OT Hr: ' . ($value->over_time ? date('H:i', strtotime($value->over_time)) : '-') . '<br>' . 'Appr. Hr: ' . '-' . '<br>' . 'Remark: ' . '-';
                                                                    // }
                                                                @endphp
                                                            </td>

                                                            <td style="font-size:12px;">
                                                                @php
                                                                    if ($value->in_out_time != null) {
                                                                        echo $value->in_out_time;
                                                                    } else {
                                                                        echo $zero;
                                                                    }
                                                                @endphp
                                                            </td>

                                                            <td
                                                                style="font-size:12px;color:darkgreen;font-weight:bold;background:gold">
                                                                @php
                                                                    $status = ($AttendanceStatusID[$value->attendance_status] ?? '') . ($EmployeeInOutData->halfday_status ? ' ½' : '');
                                                                @endphp
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
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
    $(document).ready(function() {
        $("#excelexport").click(function(e) {
            //getting values of current time for generating the file name
            var dt = new Date();
            var day = dt.getDate();
            var month = dt.getMonth() + 1;
            var year = dt.getFullYear();
            var hour = dt.getHours();
            var mins = dt.getMinutes();
            var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
            //creating a temporary HTML link element (they support setting file names)
            var a = document.createElement('a');
            //getting data from our div that contains the HTML table
            var data_type = 'data:application/vnd.ms-excel';
            var table_div = document.getElementById('btableData');
            var table_html = table_div.outerHTML.replace(/ /g, '%20');
            a.href = data_type + ', ' + table_html;
            //setting the file name
            a.download = 'attendance_details_' + postfix + '.xls';
            //triggering the function
            a.click();
            //just in case, prevent default behaviour
            e.preventDefault();
        });


    });
</script>
@endsection
