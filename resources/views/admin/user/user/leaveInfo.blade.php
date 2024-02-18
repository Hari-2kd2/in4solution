<?php
$SalaryRepository = new App\Repositories\SalaryRepository;
$request1 = request();
?>
<form action="{{ Route('leaveInfo', ['id' => $Employee->employee_id]) }}" method="get" id="emp-form-{{ $Employee->employee_id }}">
    <div class="panel panel-info">
        <table class="table main" style="border-collapse: collapse;">
            <tr>
                <td width="15%" class="bold">@lang('employee.name'): </td>
                <td width="35%">{{ $Employee->fullname() }}</td>
                <td width="15%" class="bold">@lang('employee.emp_code'): </td>
                <td width="35%">{{ $Employee->emp_code }}</td>
            </tr>
            <tr>
                <td class="bold">Designation: </td>
                <td>{{ $Employee->department->date_of_joining ?? '-' }}</td>
                <td class="bold">Department: </td>
                <td>{{ $Employee->designation->designation_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="bold">Date Of Birth:<br><br><br>Date Of Joining:</td>
                <td>
                    <span class="form-control">{{ dateConvertDBtoForm($Employee->date_of_birth) }}</span><br>
                    <input type="date" name="date_of_joining" class="form-control" value="{{ $Employee->date_of_joining }}">
                </td>
                <td class="bold">Other Detail</td>
                <td>
                    <span class="bold">Gender:</span> {{ $Employee->gender }}<br>
                    <span class="bold">Marital Status:</span> {{ $Employee->marital_status ? 'Married' : 'Unmarried' }}<br>
                    <span class="bold">No Of Child:</span> {{ $Employee->no_of_child }}<br>
                </td>
            </tr>
            <tr>
                <td class="bold"></td>
                <td>
                    <table class="table sub">
                        <tr>
                            <th colspan="3" class="text-center">SL Credits Schedule (Probation)</th>
                        </tr>
                        <tr>
                            <td>Montlhy Date</td>
                            <td>Credit / Use</td>
                            <td>Balance</td>
                        </tr>
                        @php
                        $creditSL=[];
                        $totalProbation=0;
                        $totalUsed=0;
                        $yearEnd='';
                        $yearStart='';
                        $noOfMonths = $SalaryRepository->PROBATOIN_MONTHS;// + 1;
                        $day = date('d',strtotime($Employee->date_of_joining));
                        if($day=='01' || $day=='1') {
                            // $noOfMonths = $SalaryRepository->PROBATOIN_MONTHS;
                            // $noOfMonths = $SalaryRepository->PROBATOIN_MONTHS - 1;
                        } else {
                            $noOfMonths = $SalaryRepository->PROBATOIN_MONTHS + 1;
                        }
                        if( (int) getDateOnly($Employee->date_of_joining) <= 15) {
                            $noOfMonths = $SalaryRepository->PROBATOIN_MONTHS;
                        }
                        @endphp
                        @for ($m = 1; $m<=$noOfMonths ; $m++)
                            @php
                                $month='';
                                if($m==1) {
                                    $dateCut =  $Employee->date_of_joining;
                                    if( (int) getDateOnly($Employee->date_of_joining) <= 15) {
                                        $month = getMonthOnly($Employee->date_of_joining);
                                        $creditSL[$month] = 1;
                                        $totalProbation++;
                                    }
                                } else {
                                    $dateCut = nextMonthFirstDate($dateCut);
                                    $month = getMonthOnly($dateCut);
                                    $creditSL[$month] = 1;
                                    nextMonthFirstDate($dateCut);
                                    $totalProbation++;
                                }

                            @endphp
                            <tr>
                                <td>{{ dateConvertDBtoForm($dateCut) }}</td>
                                <td>
                                    @php
                                        $yearEnd = date("Y-m-d", strtotime("Last day of December", strtotime($dateCut)));
                                        $yearStart = date("Y-m-d", strtotime("1st January Next Year", strtotime($dateCut)));
                                        if(isset($request1)) {
                                            $use_sl = $request1->get('use_sl', []);
                                        }
                                        if(isset($creditSL[$month])) {
                                            $selected = isset($use_sl) && isset($use_sl[$m]) && $use_sl[$m] ? ' checked ' : '';
                                            if($selected) {
                                                $totalProbation--;
                                                echo '1';
                                            } else {
                                                echo $creditSL[$month];
                                            }
                                            echo '<span class="pull-right"><input type="checkbox" '.$selected.' name="use_sl['.$m.']" value="1"></span>';
                                        }
                                    @endphp
                                </td>
                                <td>{{ $totalProbation }}</td>
                            </tr>
                        @endfor
                    <tr>
                        <td>
                            @php
                                $advanceCreditDate = nextMonthFirstDate($dateCut);
                            @endphp
                            {{ dateConvertDBtoForm($advanceCreditDate) }}
                            to Calendar Year<br>{{ dateConvertDBtoForm($yearStart) }}
                        </td>
                        <td>
                            Pro-Rata Based <br>
                            @php
                                $monthDiffs = monthDiffs($yearEnd, $dateCut) - 1;
                                @endphp
                            {!! // 9 - per year sick leave allotments / 12 - calendar year months
                            $SlCreditCalendarYear = round((($SalaryRepository->SICK_LEAVE_PER_YEAR/12) * $monthDiffs));
                            !!}
                            {{  ' ('.$monthDiffs.' Months)' }}
                        </td>
                        <td>{{ $totalProbation + $SlCreditCalendarYear }}</td>
                    </tr>
                </table>
            </td>
            <td class="bold"></td>
            <td>
                @php
                        $probationEnd = addMonth($Employee->date_of_joining, $SalaryRepository->PROBATOIN_MONTHS);
                        $monthDiffs = monthDiffs($yearEnd, $probationEnd);
                        $creditClDate = $advanceCreditDate;
                        $day = date('d',strtotime($probationEnd));
                        // echo '$probationEnd='.$probationEnd;
                        // if($day=='01' || $day=='1') {
                        //     // $creditClDate = $probationEnd;
                        //     // $monthDiffs++;
                        // } else {
                        //     $creditClDate = nextMonthFirstDate($probationEnd);
                        // }
                        // $monthDiffs = monthDiffs($yearEnd, $probationEnd);
                    @endphp
                    <table class="table sub">
                        <tr>
                            <th colspan="3" class="text-center">CL Credits Schedule (After Probation End ({{ dateConvertDBtoForm($probationEnd) }}))</th>
                        </tr>
                        <tr>
                            <td width="30%">Credit Month Date</td>
                            <td width="35%">Credit / Use</td>
                            <td width="35%">Balance</td>
                        </tr>
                        <tr>
                            <td>{{ dateConvertDBtoForm($creditClDate) }}</td>
                            <td>
                                {!! // 6 - per year casual leave allotments / 12 - calendar year months
                                    $ClCreditAfterProbation = round((($SalaryRepository->CASUAL_LEAVE_PER_YEAR/12) * ($monthDiffs)));
                                !!}<br>
                                @php
                                    $selected = '';
                                    $ClCreditBalance = $ClCreditAfterProbation;
                                    @endphp
                                @for ($u = 0;  $u<=$ClCreditAfterProbation ; $u++)
                                    @php
                                    if(isset($request1)) {
                                        $use_cl = $request1->get('use_cl', 0);
                                        $selected = $use_cl!='' && $use_cl==$u ? ' checked ' : '';
                                        if($selected) {
                                            $ClCreditBalance = $ClCreditBalance - $use_cl;
                                        }
                                    }
                                    @endphp
                                    <span><input type="radio" {{ $selected }} name="use_cl" value="{{ $u }}"> {{ $u }} day<br></span>
                                @endfor
                            </td>
                            <td>
                                {{ $ClCreditBalance }}
                                {{-- months {{ $monthDiffs }} --}}
                                {{-- day {{ $day }} --}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><span class="text-red">Balance CL will be elapsed when new calendar year starts.</span></td>
                        </tr>
                        <tr>
                            <td>Credit Calander Year</td>
                            <td>Credit</td>
                            <td>Balance</td>
                        </tr>
                        <tr>
                            <td>{{ dateConvertDBtoForm($yearStart) }}</td>
                            <td>{{ $SalaryRepository->CASUAL_LEAVE_PER_YEAR }}</td>
                            <td>{{ $SalaryRepository->CASUAL_LEAVE_PER_YEAR }}</td>
                        </tr>

                    </table>
                </td>
            </tr>
            <tr style="display:none">
                <td class="bold">Privilege Leave: </td>
                <td>
                    <table class="table sub">
                        @php
                        $creditSL=[];
                        $oneYearServiceEnd = addYear($Employee->date_of_joining, $SalaryRepository->SERVICE_COMPLETE_YEAR);
                        $yearStart = date("Y-m-d", strtotime("1st January Next Year", strtotime($Employee->date_of_joining)));
                        $day = date('d',strtotime($oneYearServiceEnd));
                        $monthDiffs = monthDiffs($yearStart, $oneYearServiceEnd) - 1;
                        if($day=='01' || $day=='1') {
                            $creditClDate = $oneYearServiceEnd;
                        } else {
                            $creditClDate = nextMonthFirstDate($oneYearServiceEnd);
                        }

                        // $day = date('d',strtotime($creditClDate));
                        // $month = date('m',strtotime($creditClDate));
                        // if(($day=='01' || $day=='1') && ($month=='01' || $month=='1')) {
                        //     $creditClDate = $oneYearServiceEnd.'';
                        //     // $monthDiffs++;
                        // } else {
                        //     $yearStart = date("Y-m-d", strtotime("1st January Next Year", strtotime($creditClDate)));
                        //     $creditClDate = $yearStart;
                        // }

                        @endphp
                        <tr style="display: none">
                            <th colspan="3" class="text-center">PL Credits Schedule (End of one year service ({{ $oneYearServiceEnd }}))</th>
                        </tr>
                        <tr>
                            <td>Credit Date</td>
                            <td>Previous LOP</td>
                            <td>Credit / Use</td>
                        </tr>
                        <tr>
                            <td>
                                {{ dateConvertDBtoForm($creditClDate) }}
                                {{-- {{ 'months='.$monthDiffs }} --}}
                            </td>
                            <td>
                                @php
                                    $pl_lop1 = $request1->get('pl_lop1', 0);
                                @endphp
                                <select name="pl_lop1" class="form-control">
                                    <option value="0">0 LOP</option>
                                    @for ($ye = 22 ; $ye <=325 ; $ye+=22)
                                        @php
                                        $selected='';
                                        if($pl_lop1) {
                                            $selected = $pl_lop1!='' && $pl_lop1==$ye ? ' selected ' : '';
                                        }
                                        @endphp
                                        <option value="{{ $ye }}" {{ $selected }}>{{ $ye }} days</option>
                                    @endfor
                                </select>
                               @php
                                    if($pl_lop1) {
                                        // get slab caculation
                                        $SLAB = DB::table('privilege_leave_lop_slab')->select('pl_months')->whereRaw("$pl_lop1 >= pl_lop_from AND $pl_lop1 <= pl_lop_to")->first();
                                        $pl_months = ($SLAB->pl_months ?? '');
                                        $PlCreditAfterOneYear = round((($SalaryRepository->PRIVILEGE_LEAVE_PER_YEAR/12) * ($monthDiffs)));
                                        if($pl_months) {
                                            $off_months = $SalaryRepository->PRIVILEGE_LEAVE_PER_YEAR - (12 - $pl_months);
                                            $PlCreditAfterOneYear = round((($off_months/12) * ($monthDiffs)));
                                        }
                                        // echo '<pre>off_months'.print_r($off_months, 1).'</pre>';
                                    } else {
                                        $PlCreditAfterOneYear = round((($SalaryRepository->PRIVILEGE_LEAVE_PER_YEAR/12) * ($monthDiffs)));
                                    }
                                    $use_cl = $request1->get('use_cl', 0);
                                @endphp
                            </td>
                            <td>
                                <span class="bold" style="white-space: nowrap">{!! $PlCreditAfterOneYear . ' Credit(s)<br>' !!}</span>
                                @for ($USE1=0;  $USE1<=$PlCreditAfterOneYear; $USE1++)
                                    @php
                                    $selected = '';
                                    if($use_cl!='') {
                                        $selected = $use_cl!='' && $use_cl==$USE1 ? ' checked ' : '';
                                    }
                                    @endphp
                                <span><input type="radio" {{ $selected }} name="use_cl" value="{{ $USE1 }}"> {{ $USE1 }} day<br></span>
                                @endfor
                            </td>
                        </tr>
                        {{-- <tr>
                            <td>
                                {{ dateConvertDBtoForm(nextMonthFirstDate($dateCut)) }}
                            </td>
                            <td>
                                Pro-Rata Based<br>
                                @php
                                    $monthDiffs = monthDiffs($yearEnd, $dateCut) - 1;
                                    @endphp
                                {!! // 9 - per year sick leave allotments / 12 - calendar year months
                                $SlCreditCalendarYear = round((($SalaryRepository->SICK_LEAVE_PER_YEAR/12) * $monthDiffs));
                                !!}
                                {{  ' ('.$monthDiffs.' Months)' }}
                            </td>
                            <td>{{ $totalProbation + $SlCreditCalendarYear }}</td>
                        </tr> --}}
                    </table>
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <th colspan="4" class="text-center">
                    <button type="button" class="btn btn-warning btn-md re-calc" data-url={{ Route('leaveInfo', ['id' => $Employee->employee_id]) }} data-id="{{ $Employee->employee_id }}">Re Caculate</button>
                </th>
            </tr>
        </table>                                    
    </div>
</form>