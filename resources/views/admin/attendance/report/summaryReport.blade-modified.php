@php
    use App\Model\Employee;
    use App\Model\WorkShift;
    use App\Model\WorkShiftCase;
    use Illuminate\Support\Carbon;
    use App\Model\EmployeeInOutData;
    use App\Lib\Enumerations\AttendanceStatus;
@endphp
@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.attendance_summary_report')
@endsection
<style>
    .day-status {
        text-align: center;
        background: #e6e6e6
    }

    .nowrap {
        white-space: nowrap;
        font-size: 11px;
    }

    .in-time.punctual,
    .out-time.punctual,
    .over-time.yes,
    .correct {
        color: #389b03;
        font-weight: 700;
    }

    .in-time.late,
    .not-correct {
        color: #c20000;
        font-weight: 700;
    }

    .over-time.no {
        color: #ec3cfc;
        font-weight: 700;
    }

    .notice-time,
    .in-time.early,
    .out-time.early {
        color: #e46e00 !important;
        font-weight: 700;
    }

    .bold {
        font-weight: 700;
        color: #2c2c2c
    }

    .present {
        color: #7ace4c;
        font-weight: 700;
        cursor: pointer;
    }

    .text-red {
        color: #c20000;
    }

    .absence {
        color: #c20000;
        font-weight: 700;
        cursor: pointer;
    }

    .leave {
        color: #f5981e;
        font-weight: 700;
        cursor: pointer;
    }

    .rhleave,
    .compoff,
    .OD,
    .holiday,
    .publicHoliday {
        color: #f5981e;
        font-weight: 700;
        cursor: pointer;
    }

    /* .OD {
        color: #41b3f9;
        font-weight: 700;
        cursor: pointer;
    }

    .holiday {
        color: #41b3f9;
        font-weight: 700;
        cursor: pointer;
    }

    .publicHoliday {
        color: #41b3f9;
        font-weight: 700;
        cursor: pointer;
    } */

    .left {
        color: #000000;
        font-weight: 700;
        cursor: pointer;
    }

    .absence {
        color: #ff0000;
        font-weight: 700;
        cursor: pointer;
    }

    .bolt {
        font-weight: 700;
    }
</style>
<script>
    jQuery(function() {
        $("#attendanceSummaryReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
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
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @include('flash_message')
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open([
                                    'route' => 'attendanceSummaryReport.attendanceSummaryReport',
                                    'id' => 'attendanceSummaryReport',
                                ]) }}
                                <div class="col-md-3"></div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($from_date)) {{ $from_date }}@else {{ dateConvertDBtoForm(date('Y-m-01')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.to_date')" name="to_date"
                                            value="@if (isset($to_date)) {{ $to_date }}@else {{ dateConvertDBtoForm(date('Y-m-t', strtotime(date('Y-m-01')))) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 26px;"
                                            class="btn btn-info btn-md" value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <br>
                        <div class="table-responsive">

                            <table id="myDataTable" class="table table-bordered table-striped table-hover"
                                style="font-size: 12px">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('employee.employee_id')</th>
                                        <th>@lang('common.name')</th>
                                        @if (session('logged_session_data.role_id') == 1)
                                            <th>@lang('common.branch')</th>
                                        @endif
                                        <th>@lang('employee.designation')</th>
                                        <th>@lang('employee.department')</th>
                                        <th>@lang('employee.gender')</th>
                                        <th>@lang('employee.status')</th>
                                        <th>Status<br>IN/OUT<br>Shift/OT</th>
                                        @foreach ($monthToDate as $head)
                                            <th class="text-center">{{ $head['day'] . ' ' . $head['day_name'] }}</th>
                                        @endforeach

                                        <th>@lang('attendance.over_time')</th>
                                        <th>@lang('attendance.day_of_worked')</th>
                                        <th>@lang('attendance.public_holiday')</th>
                                        @foreach ($leaveTypes as $leaveType)
                                            <th>{{ $leaveType->leave_type_name }}</th>
                                        @endforeach
                                        <th>@lang('attendance.total_paid_days')</th>
                                        <th>@lang('attendance.total_days')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sl = null;
                                        $totalPresent = 0;
                                        $leaveData = [];
                                        $totalCol = 0;
                                        $totalWorkHour = 0;
                                        $totalWeeklyHoliday = 0;
                                        $totalGovtHoliday = 0;
                                        $totalAbsent = 0;
                                        $totalLeave = 0;
                                        $totalOD = 0;
                                        $AttendanceRepository = new \App\Repositories\AttendanceRepository();

                                    @endphp
                                    @foreach ($results as $key => $value)
                                        @php
                                            $employee_attendance_id = $value[0]['employee_attendance_id'] ?? null;
                                            $employee = Employee::find($value[0]['employee_id'] ?? new Employee());
                                        @endphp
                                        <tr>
                                            <td title="{{ $value[0]['employee_id'] . ' ' . $employee_attendance_id }}">
                                                {{ ++$sl }}</td>
                                            <td title="{{ $employee->supervisorDetail() }}">
                                                {{ $value[0]['finger_id'] }}</td>
                                            <td>{{ $value[0]['fullName'] }}</td>
                                            @if (session('logged_session_data.role_id') == 1)
                                                <td>{{ $value[0]['branch_name'] }}</td>
                                            @endif
                                            <td>{{ $value[0]['designation_name'] }}</td>
                                            <td>{{ $value[0]['department_name'] }}</td>
                                            <td>{{ $value[0]['gender'] }}</td>
                                            <td>{{ userStatus($value[0]['status']) }}</td>
                                            <td>
                                                <span
                                                    class="nowrap bold">Status<br>Shift<br>InTime<br>OutTime<br>WorkingHrs<br>{{ $employee->overtime_status ? 'OT' : 'Extra Time' }}</span>
                                            </td>
                                            @php
                                                $NA = '-';
                                                $NATIME = '-:-';
                                                $OT_TOTAL = 0;
                                            @endphp
                                            @foreach ($value as $v)
                                                @php
                                                    $TIME_INFO = '';
                                                    // set defaults
                                                    $IN_TIME = $OUT_TIME = $WORKING_TIME = $OVER_TIME = $NATIME;

                                                    $EmployeeInOutData = $v['EmployeeInOutData'] ?? new EmployeeInOutData();
                                                    $SHIFT_NAME = $EmployeeInOutData->shift_name ?? $NA;
                                                    $WORK_SHIFT_ID = $EmployeeInOutData->work_shift_id ?? '';
                                                    $WorkShift = WorkShiftCase::where('work_shift_id', $WORK_SHIFT_ID)->first() ?? new WorkShiftCase();
                                                    $SHIFT_NAME = $WorkShift ? '<span title="' . $WorkShift->shiftDetail() . '">' . $EmployeeInOutData->shift_name . '</span>' : $SHIFT_NAME;
                                                    $Employee = DB::table('employee')
                                                        ->where('employee_id', $v['employee_id'])
                                                        ->first();
                                                    $SHIFT_HOURS = $WorkShift ? timeDiffInHoursFormat($WorkShift->start_time, $WorkShift->end_time) : [];
                                                    if ($EmployeeInOutData && $EmployeeInOutData->in_time) {
                                                        $date = new DateTime($EmployeeInOutData->in_time);
                                                        $IN_TIME_CLASS = 'in-time';
                                                        if ($WorkShift && date('H:i:s', strtotime($EmployeeInOutData->in_time)) > $WorkShift->late_count_time) {
                                                            $IN_TIME_CLASS .= ' late';
                                                        } elseif ($WorkShift && date('H:i:s', strtotime($EmployeeInOutData->in_time)) < $WorkShift->late_count_time) {
                                                            $IN_TIME_CLASS .= ' early';
                                                        } else {
                                                            $IN_TIME_CLASS .= ' correct';
                                                        }

                                                        $IN_TIME = '<span class="' . $IN_TIME_CLASS . '" title="' . $WorkShift->late_count_time . '">' . $date->format('H:i') . ' <i class="fa fa-arrow-left"></i></span>';
                                                    }
                                                    if ($EmployeeInOutData && $EmployeeInOutData->out_time) {
                                                        $date = new DateTime($EmployeeInOutData->out_time);
                                                        $OUT_TIME_CLASS = 'out-time';
                                                        if ($WorkShift && date('H:i:s', strtotime($EmployeeInOutData->out_time)) < $WorkShift->end_time) {
                                                            $OUT_TIME_CLASS .= ' early';
                                                        } else {
                                                            $OUT_TIME_CLASS .= ' correct';
                                                        }
                                                        $OUT_TIME = '<span class="' . $OUT_TIME_CLASS . '">' . $date->format('H:i') . ' <i class="fa fa-arrow-right"></i></span>';
                                                    }

                                                    if ($EmployeeInOutData && $EmployeeInOutData->finger_print_id == 'T0010') {
                                                        // dd('$EmployeeInOutData', $EmployeeInOutData);
                                                    }
                                                    if ($EmployeeInOutData && $EmployeeInOutData->working_time) {
                                                        $date = new DateTime($EmployeeInOutData->working_time);
                                                        if ($EmployeeInOutData->halfday_status > 0) {
                                                            $WORKING_TIME = '<span class="notice-time">' . $date->format('H:i') . ' <i class="fa fa-hourglass-half"></i></span>';
                                                        } elseif ($EmployeeInOutData->attendance_status == AttendanceStatus::$PRESENT) {
                                                            $WORKING_TIME = '<span class="correct">' . $date->format('H:i') . ' <i class="fa fa-clock"></i></span>';
                                                        } else {
                                                            $WORKING_TIME = '<span class="not-correct">' . $date->format('H:i') . ' <i class="fa fa-times"></i></span>';
                                                        }
                                                        if ($EmployeeInOutData && $EmployeeInOutData->finger_print_id == 'T0010') {
                                                            // dd(__LINE__. ' EmployeeInOutData', $EmployeeInOutData, 'WORKING_TIME', $WORKING_TIME);
                                                        }
                                                    }
                                                    if ($EmployeeInOutData && $EmployeeInOutData->over_time) {
                                                        $date = new DateTime($EmployeeInOutData->over_time);
                                                        if ($Employee->overtime_status == 1) {
                                                            $startTime = Carbon::parse('00:00:00');
                                                            $finishTime = Carbon::parse($EmployeeInOutData->over_time);
                                                            $overTimes = $finishTime->diffInMinutes($startTime);
                                                            $hours = $overTimes / 60;
                                                            $OT_TOTAL += $hours;
                                                            $OVER_TIME = '<span class="over-time yes">' . $date->format('H:i') . ' <i class="fa fa-plus-circle"></i></span>';
                                                        } else {
                                                            $OVER_TIME = '<span class="over-time no">' . $date->format('H:i') . ' <i class="fa fa-plus-circle"></i></span>';
                                                        }
                                                    }

                                                    $STATUS_CLASS = '';
                                                    $STATUS_TITLE = '';
                                                    if ($EmployeeInOutData && $EmployeeInOutData->attendance_status == AttendanceStatus::$PRESENT) {
                                                        $TIME_INFO .= $SHIFT_NAME . '<br>';
                                                        $TIME_INFO .= $IN_TIME . '<br>' . $OUT_TIME . '<br>';
                                                        $TIME_INFO .= $WORKING_TIME . '<br>' . $OVER_TIME . '<br>';
                                                    } else {
                                                        $TIME_INFO .= $SHIFT_NAME . '<br>';
                                                        $TIME_INFO .= $IN_TIME . '<br>' . $OUT_TIME . '<br>';
                                                        $TIME_INFO .= $WORKING_TIME . '<br>' . $OVER_TIME . '<br>';
                                                    }

                                                    if ($sl == 1) {
                                                        $totalCol++;
                                                    }
                                                    $STATUS_LABEL = '';
                                                    $leave_name = '';
                                                    if ($EmployeeInOutData->attendance_status && isset($AttendanceRepository->AttendanceStatus[$EmployeeInOutData->attendance_status])) {
                                                        $attendance_label = $AttendanceRepository->AttendanceStatus[$EmployeeInOutData->attendance_status];
                                                        $STATUS_LABEL = $AttendanceRepository->AttendanceStatus[$EmployeeInOutData->attendance_status];
                                                        if (isset($AttendanceRepository->AttendanceStatusLegend[$STATUS_LABEL])) {
                                                            $STATUS_TITLE = $AttendanceRepository->AttendanceStatusLegend[$STATUS_LABEL];
                                                        }
                                                    }
                                                    if (isset($AttendanceRepository->AttendanceStatusClass[$EmployeeInOutData->attendance_status])) {
                                                        $STATUS_CLASS = $AttendanceRepository->AttendanceStatusClass[$EmployeeInOutData->attendance_status];
                                                    }
                                                    if (isset($v['leave_name'])) {
                                                        $STATUS_TITLE .= ' ' . ($leave_name = $v['leave_name']);
                                                        $STATUS_LABEL = $leave_name=$v['leave_name'];
                                                    }

                                                    switch ($EmployeeInOutData->attendance_status) {
                                                        case AttendanceStatus::$PRESENT:
                                                            if ($EmployeeInOutData->halfday_status) {
                                                                $totalPresent += $EmployeeInOutData->halfday_status;
                                                            } else {
                                                                $totalPresent++;
                                                            }
                                                            break;
                                                        case AttendanceStatus::$LEAVE:
                                                            $leaveArray = json_decode($EmployeeInOutData->notes, true) !== null ? json_decode($EmployeeInOutData->notes, true)['LEAVE'] : null;
                                                            if (isset($leaveArray)) {
                                                                // dd($leaveArray);
                                                                if (!isset($leaveData[$key][$leaveArray['leave_type_id']][$leaveArray['leave_application_id']])) {
                                                                    $leaveData[$key][$leaveArray['leave_type_id']][$leaveArray['leave_application_id']] = 0;
                                                                }
                                                                $leaveData[$key][$leaveArray['leave_type_id']][$leaveArray['leave_application_id']] = $leaveArray['number_of_day'];
                                                            }
                                                            $totalLeave++;
                                                            break;
                                                        case AttendanceStatus::$HOLIDAY:
                                                            $totalWeeklyHoliday++;
                                                            break;

                                                        default:
                                                            break;
                                                    }
                                                    $half_lavel = $EmployeeInOutData->halfday_status ? '½' : '';
                                                @endphp
                                                <td>
                                                    <div class="day-status {{ $STATUS_CLASS }}"
                                                        title="{{ $STATUS_TITLE }}">{{ $STATUS_LABEL }}
                                                        {{ $half_lavel }}</div>
                                                    <span class="nowrap">{!! $TIME_INFO !!}</span>
                                                </td>
                                            @endforeach
                                            <td><span class="bolt">{{ round($OT_TOTAL, 2) }}</span></td>
                                            <td><span class="bolt">{{ $totalPresent }}</span></td>
                                            <td><span class="bolt">{{ $totalGovtHoliday }}</span></td>

                                            @foreach ($leaveTypes as $leaveType)
                                                <td>
                                                    <span class="bolt">
                                                        @php
                                                            if ($sl == 1) {
                                                                $totalCol++;
                                                            }

                                                            $currentLeave = 0;
                                                            if (isset($leaveData[$key][$leaveType->leave_type_id])) {
                                                                foreach ($leaveData[$key][$leaveType->leave_type_id] as $key => $value) {
                                                                    $currentLeave += $value;
                                                                }
                                                            } else {
                                                                $currentLeave = 0;
                                                            }
                                                        @endphp
                                                        {{ $currentLeave }}
                                                    </span>
                                                </td>
                                            @endforeach
                                            <td><span
                                                    class="bolt">{{ $totalPresent + $totalLeave + $totalGovtHoliday }}</span>
                                            </td>
                                            <td><span
                                                    class="bolt">{{ $totalPresent + $totalWeeklyHoliday + $totalAbsent + $totalLeave + $totalOD }}</span>
                                            </td>
                                            @php
                                                $totalPresent = 0;
                                                $totalWeeklyHoliday = 0;
                                                $totalAbsent = 0;
                                                $totalLeave = 0;
                                                $totalGovtHoliday = 0;
                                                $totalOD = 0;
                                            @endphp
                                        </tr>
                                    @endforeach
                                    <script>
                                        // {!! "$('.totalCol').attr('colspan',$totalCol+3);" !!}
                                    </script>
                                </tbody>
                            </table>
                            @if (count($results))
                                <p><br></p>
                                <div>
                                    <ul>
                                        <li>&nbsp;&nbsp;-:-&nbsp;&nbsp;&nbsp; Data Not Available</li>
                                        <li><span class="not-correct">03:15 <i class="fa fa-times"></i></span> Less Then
                                            Half Day</li>
                                        <li><span class="notice-time">08:16 <i class="fa fa-hourglass-half"></i></span>
                                            Half Day Present</li>
                                        <li><span class="correct">08:52 <i class="fa fa-clock"></i></span> Full Day
                                            Present</li>
                                        <li><span class="over-time yes">01:00 <i class="fa fa-plus-circle"></i></span>
                                            Over Time Worked</li>
                                        <li><span class="over-time no">01:10 <i class="fa fa-plus-circle"></i></span>
                                            Extra Time Worked</li>
                                        <li><span class="in-time early" title="08:40:00">08:35 <i
                                                    class="fa fa-arrow-left"></i></span> Early In</li>
                                        <li><span class="out-time early">16:56 <i class="fa fa-arrow-right"></i></span>
                                            Early Out</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
