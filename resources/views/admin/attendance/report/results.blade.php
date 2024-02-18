@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.attendance_summary_report')
@endsection
<style type="text/css">
    .form-check label {
        font-weight: normal;
    }

    .inner-section .panel-heading {
        background-color: transparent !important;
        padding: 8px 13px;
    }

    .inner-section .panel-body {
        height: 250px;
        overflow: auto;
        padding: 10px;
    }
	.salary-statment-table thead > tr > th {
		white-space: nowrap;
	}
</style>
<div class="container-fluid">
    <br>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>Generate Report</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body inner-section">
                        <div class="table-responsive">
                            <table class="table table-bordered salary-statment-table">
                                <thead>
                                    <tr>
                                        <th>S. No</th>
                                        <th>@lang('employee.finger_print_no')</th>
                                        @php
                                            $header = ['username' => 'Username','empcode' => 'Emp Code','email' => 'Email ID','mobie' => 'Mobile No','gender' => 'Gender','uan' => 'UAN','costcenter' => 'Cost Center','pan' => 'PAN/GIR No','pfno' => 'PF Account No','esi' => 'ESI No','religion' => 'Religion','dob' => 'Date of Birth','doj' => 'Date of Joining','salrev' => 'Salary Revision','dol' => 'Date of Leaving','marital' => 'Martial Status','childs' => 'No of Childs','status' => 'Status','ota' => 'Overtime Allowed','epf' => 'EPF Status','emgencycno' => 'Emergency Contact No','address' => 'Address','photo' => 'Photo','role' => 'Role','acno' => 'Bank A/c No','ifsc' => 'Bank IFSC','bankname' => 'Bank Name','bonus' => 'Bonus','branch' => 'Branch','department' => 'Department','designation' => 'Designation','workshif' => 'Work Shift','hod' => 'HOD','ctc' => 'CTC','cw' => 'CW','ood' => 'OOD','cl' => 'CL','sl' => 'SL','pl' => 'PL','l' => 'L','weekoff' => 'Weekly Off','generalholiday' => 'General Holiday','paydays' => 'Pay Days','present' => 'Present Days','salcaldays' => 'Sal Cal Days','basic' => 'Basic','fixgross' => 'Fixed Gross','lta' => 'LTA','spl' => 'SPL','gross' => 'GROSS',];
                                            $header = keyFields('header');
											$classification=keyFields('classifications');
											$payroll=keyFields('payroll');
											$additionals=keyFields('additionals');
											$attendance=keyFields('attendance');
                                        @endphp
                                        @foreach ($header as $key => $Data)
                                            @if (in_array($key, $filterset))
                                                <th>{{ $Data }}</th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $SNO = 1;
                                        $select_month_form = $salary_month = request()->get('month_year', '');
                                        $select_month = request()->get('salary_month', date('m/Y'));
                                        $select_month = dateConvertFormtoDB('01/' . $select_month);

                                        $Total_Work_Days = count(findMonthToAllDate($select_month));
                                    @endphp
                                    @foreach ($employeeList as $empIndex => $ExcelEmployee)
                                        @php
                                            $TODAY = date('Y-m-d');
                                            $SALARY_MONTH = dateConvertFormtoDB('01/' . $salary_month);
                                            $EsiClass = '';
                                            $OvertimeClass = '';
                                            $Esi21Class = '';
                                            $ProbationClass = '';

                                            $employee = $ExcelEmployee->statmentEmployee;
                                            /*if(!$employee || !isset($ExcelEmployee->ctc)) {
												continue;
											}*/
                                            $jLog = 'Code: ' . $employee->emp_code . ' ≈ ';
                                            $PayrollStatement = \App\Model\PayrollStatement::where('payroll_upload_id', $ExcelEmployee->payroll_upload_id)->first();
                                            if (!$PayrollStatement) {
                                                $PayrollStatement = new \App\Model\PayrollStatement();
                                                $PayrollStatement->payroll_upload_id = $ExcelEmployee->payroll_upload_id;
                                            }
                                            $PayrollStatementData = $PayrollStatement->getAttributes();
                                            $ExcelEmployeeData = $ExcelEmployee->getAttributes();
                                            $employeeData = $employee->getAttributes();
                                            $userData = $employee->userName->getAttributes();
                                            $testData = array_merge($employeeData, $userData, $ExcelEmployeeData, $PayrollStatementData);
                                            // info($testData);
                                            $SNO++;
                                            $Common = new \App\Components\Common();
                                            // in later here we give employee table $employee->salary_ctc, $employee->salary_gross
                                            $CTC = $ExcelEmployee->ctc;
                                            $Gross = $ExcelEmployee->gross_salary;
                                            $EarnedCTC = $CTC;
                                            $EarnedGross = $Gross;
                                            $Basic = ($EarnedCTC / 100) * $Common::PERCENTAGE_BASIC;
                                            $jLog .= '$EarnedCTC=' . $EarnedCTC . ', $EarnedGross=' . $EarnedGross;
                                            $jLog .= ', $Basic=' . $Basic;
                                            $Basic = round($Basic);
                                            $jLog .= ', Basic=' . $Basic;
                                            $BasicAbs = round(($EarnedCTC / 100) * $Common::PERCENTAGE_BASIC);
                                            $Over_Time = $ExcelEmployee->over_time;
                                            $Age = yearDiffs($employee->date_of_birth, date('Y-m-d'));
                                            $DAYS_FROM_DOJ = false;
                                            $MONYHS_FROM_DOJ = false;
                                            $semi_salary = false;
                                            $leaving_salary = false;
                                            $MONYHS_WORKED = monthDiffs($employee->date_of_joining, date('Y-m-d'));
                                            $DAYS_FROM_DOJ_TITLE = $DAYS_FROM_DOL_TITLE = '';
                                            if ($employee->permanent_status == 0) {
                                                $SAL_DATE = strtotime($SALARY_MONTH);
                                                $SAL_MON = (int) date('m', $SAL_DATE);
                                                $SAL_YEA = (int) date('Y', $SAL_DATE);

                                                $DOJ_DATE = strtotime($employee->date_of_joining);
                                                $DOJ_DAY = (int) date('d', $DOJ_DATE);
                                                $DOJ_MON = (int) date('m', $DOJ_DATE);
                                                $DOJ_YEA = (int) date('Y', $DOJ_DATE);

                                                if ($DOJ_DAY > 1 && $SAL_MON == $DOJ_MON && $DOJ_YEA == $SAL_YEA) {
                                                    $DAYS_FROM_DOJ = daysDiffs($employee->date_of_joining, $SALARY_MONTH);
                                                    $MONYHS_FROM_DOJ = monthDiffs($employee->date_of_joining, $SALARY_MONTH);

                                                    $EarnedCTC = ($EarnedCTC * ($Total_Work_Days - $DAYS_FROM_DOJ)) / $Total_Work_Days;
                                                    $jLog .= ', $EarnedCTC(MID)=' . $EarnedCTC;
                                                    $EarnedCTC = round(($EarnedCTC * ($Total_Work_Days - $DAYS_FROM_DOJ)) / $Total_Work_Days) . '.00';
                                                    $jLog .= ', EarnedCTC(MID)=' . $EarnedCTC;
                                                    $EarnedGross = round(($EarnedGross * ($Total_Work_Days - $DAYS_FROM_DOJ)) / $Total_Work_Days);
                                                    $Basic = round(($EarnedCTC / 100) * $Common::PERCENTAGE_BASIC);
                                                    $semi_salary = ' semi-salary ';
                                                    $DAYS_FROM_DOJ_TITLE = $DAYS_FROM_DOJ . ' days';
                                                }
                                            }

                                            if ($employee->date_of_leaving) {
                                                $SAL_DATE = strtotime($SALARY_MONTH);
                                                $SAL_MON = (int) date('m', $SAL_DATE);
                                                $SAL_YEA = (int) date('Y', $SAL_DATE);

                                                $DOL_DATE = strtotime($employee->date_of_leaving);
                                                $DOL_DAY = (int) date('d', $DOL_DATE);
                                                $DOL_MON = (int) date('m', $DOL_DATE);
                                                $DOL_YEA = (int) date('Y', $DOL_DATE);
                                                if ($DOL_DAY > 1 && $SAL_MON == $DOL_MON && $DOL_YEA == $SAL_YEA) {
                                                    $DAYS_FROM_DOL = daysDiffs($employee->date_of_leaving, $SALARY_MONTH) + 1; // last day included
                                                    $MONYHS_FROM_DOL = monthDiffs($employee->date_of_leaving, $SALARY_MONTH);

                                                    $EarnedCTC = ($EarnedCTC * ($Total_Work_Days - $DAYS_FROM_DOL)) / $Total_Work_Days;
                                                    $jLog .= ', $EarnedCTC(DOL)=' . $EarnedCTC;
                                                    $EarnedCTC = round($EarnedCTC);
                                                    $jLog .= ', EarnedCTC(DOL)=' . $EarnedCTC;
                                                    $EarnedGross = round(($EarnedGross * ($Total_Work_Days - $DAYS_FROM_DOL)) / $Total_Work_Days);
                                                    $Basic = round(($EarnedCTC / 100) * $Common::PERCENTAGE_BASIC);
                                                    $leaving_salary = ' leaving-salary ';
                                                    $DAYS_FROM_DOL_TITLE = $DAYS_FROM_DOL . ' days';
                                                }
                                            }

                                            $addYear = addYear($employee->date_of_birth, $SalaryRepository->NO_ESI_AGE_ABOVE);
                                            $AgeEsiDate = nextMonthFirstDate($addYear);
                                            $AgeFlag = false;
                                            // up come the first date include
                                            $HRA_PERCENT = $Common::PERCENTAGE_HRA;
                                            $BONUS_PERCENT = $Common::PERCENTAGE_BONUS;
                                            $LOP = 0;
                                            if ($ExcelEmployee->days_absents > 0) {
                                                $EarnedCTC = round(($EarnedCTC * ($Total_Work_Days - $ExcelEmployee->days_absents)) / $Total_Work_Days);
                                                $jLog .= ', $EarnedGross=' . $EarnedGross;
                                                $EarnedGross = ($EarnedGross * ($Total_Work_Days - $ExcelEmployee->days_absents)) / $Total_Work_Days;
                                                $EarnedGross = round($EarnedGross);
                                                $jLog .= ', EarnedGross=' . $EarnedGross;
                                                $jLog .= ', $HRA=' . $HRA;
                                                $Basic = round(($EarnedCTC / 100) * $Common::PERCENTAGE_BASIC);
                                            }

                                            $HRA = ($EarnedGross / 100) * $HRA_PERCENT;
                                            $jLog .= ', $HRA=' . $HRA;
                                            $HRA = round($HRA);
                                            $jLog .= ', HRA=' . $HRA;
                                            $LTA = ($Basic * 2) / 12;
                                            $jLog .= ', $LTA=' . $LTA;
                                            $LTA = round($LTA);
                                            $jLog .= ', LTA=' . $LTA;
                                            $Bonus = ($Basic / 100) * $BONUS_PERCENT;
                                            $jLog .= ', $Bonus=' . $Bonus;
                                            $Bonus = round($Bonus);
                                            $jLog .= ', Bonus=' . $Bonus;

                                            $ESI_TOTAL = 0;
                                            $ESI_Employee_Total = 0;
                                            $ESI_Employer_Total = 0;
                                            $PF_TOTAL = 0;
                                            $PF_Employer = 0;
                                            $ESI_Employer = 0;

                                            $PF_Employee = 0;
                                            $ESI_Employee = 0;

                                            $Other_earnings = $ExcelEmployee->other_earnings;
                                            $TDS = $ExcelEmployee->tds;
                                            $Salary_Advance = $ExcelEmployee->salary_advance;
                                            $Labour_Welfare = $ExcelEmployee->labour_welfare;
                                            $Professional_Tax = $ExcelEmployee->professional_tax;
                                            $Excess_Telephoone_Usage = $ExcelEmployee->excess_telephone_usage;

                                            if ($employee->pf_status == 1) {
                                                $PF_Employer = (($EarnedGross - $HRA) / 100) * $SalaryRepository->PERCENTAGE_EPF_EMPLOYER;
                                                $jLog .= ', $PF_Employer=' . $PF_Employer;
                                                $PF_Employer = round($PF_Employer);
                                                $jLog .= ', PF_Employer=' . $PF_Employer;
                                                $PF_Employee = (($EarnedGross - $HRA) / 100) * $SalaryRepository->PERCENTAGE_EPF_EMPLOYEE;
                                                $jLog .= ', $PF_Employee=' . $PF_Employee;
                                                $PF_Employee = round($PF_Employee);
                                                $jLog .= ', PF_Employee=' . $PF_Employee;
                                            } else {
                                                $PF_Employee = $PF_Employer = 0;
                                            }

                                            // ESI calculate if employee gross is < 21000 thousands
                                            if ($Gross < 21000) {
                                                // input gross base ESI calulation in previously $EarnedGross < 21000
                                                $jLog .= ', * $EarnedGross=' . $EarnedGross;
                                                $ESI_Employer = ($EarnedGross / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER;
                                                $jLog .= ', $ESI_Employer=' . $ESI_Employer;
                                                $ESI_Employer = round($ESI_Employer, 2);
                                                $jLog .= ', ESI_Employer=' . $ESI_Employer;
                                                $ESI_Employee = ($EarnedGross / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE;
                                                $jLog .= ', $ESI_Employee=' . $ESI_Employee;
                                                $ESI_Employee = ceil($ESI_Employee);
                                                $jLog .= ', ESI_Employee=' . $ESI_Employee;
                                            } elseif ($employee->salary_revision && $employee->salary_esi_stop) {
                                                $esi_month = $employee->salary_revision;
                                                $esi_stop = $employee->salary_esi_stop;
                                                $date = new DateTime($employee->salary_revision);
                                                $salary_revision_month = $date->format('Y-m-01');
                                                if ($SALARY_MONTH >= $salary_revision_month && $SALARY_MONTH <= $employee->salary_esi_stop) {
                                                    $ESI_Employer = round(($EarnedGross / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER, 2);
                                                    $ESI_Employee = round(($EarnedGross / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE, 2);
                                                    $DAYS_FROM_DOJ_TITLE .= PHP_EOL . 'salary_revision_month=' . dateConvertDBtoForm($employee->salary_revision) . ', ESI_Employer=' . $ESI_Employer . ', ESI_Employee=' . $ESI_Employee;
                                                    $EsiClass = 'esi-revision';
                                                }
                                            }
                                            $OT_PER_HOUR = '0';
                                            $OT_HOURS = '0';
                                            $OT_ESI_Employer = 0;
                                            $OT_ESI_Employee = 0;
                                            // if Over Time Hours > 0
                                            if ($Over_Time > 0) {
                                                $OT_HOURS = $Over_Time;
                                                $OT_PER_HOUR = $Basic / 208;
                                                $Over_Time = round($Over_Time * $OT_PER_HOUR);
                                                if ($EarnedGross < 21000) {
                                                    $OT_ESI_Employer = ($Over_Time / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYER;
                                                    $jLog .= ', $OT_ESI_Employer=' . $OT_ESI_Employer;
                                                    $OT_ESI_Employer = round($OT_ESI_Employer, 2);
                                                    $jLog .= ', OT_ESI_Employer=' . $OT_ESI_Employer;

                                                    $OT_ESI_Employee = ($Over_Time / 100) * $SalaryRepository->PERCENTAGE_ESI_EMPLOYEE;
                                                    $jLog .= ', $OT_ESI_Employee=' . $OT_ESI_Employee;
                                                    $OT_ESI_Employee = round($OT_ESI_Employee);
                                                    $jLog .= ', OT_ESI_Employee=' . $OT_ESI_Employee;
                                                }
                                                $OvertimeClass = 'green-color';
                                            }

                                            // Check is employee age is above 60
                                            if ($employee->date_of_birth != '' && $TODAY >= $AgeEsiDate && $Age >= $SalaryRepository->NO_ESI_AGE_ABOVE) {
                                                $AgeFlag = ' above-60 ';
                                                $ESI_Employer = $Bonus = $ESI_Employee = $OT_ESI_Employer = $OT_ESI_Employee = 0;
                                                // $PF_Employer = $PF_Employee = 0;
                                            }
                                            // CTC - Basic - HRA - LTA - Bonus - EPF - ESI
                                            $Special_Allowance = $EarnedCTC - ($Basic + $HRA + $LTA + $Bonus + $PF_Employer + $ESI_Employer + $OT_ESI_Employer);
                                            $jLog .= ', $Special_Allowance=' . $Special_Allowance;
                                            $Special_Allowance = round($Special_Allowance);
                                            $jLog .= ', Special_Allowance=' . $Special_Allowance;
                                            // Gross + OT Amount
                                            $Nett_Gross = $EarnedGross + $Over_Time + $Other_earnings;
                                            $jLog .= ', Nett_Gross=' . $Nett_Gross;

                                            // $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax manually upload by superadmin
                                            $Total_Deduction = $PF_Employee + $ESI_Employee + $OT_ESI_Employee + $TDS + $Salary_Advance + $Excess_Telephoone_Usage + $Labour_Welfare + $Professional_Tax;
                                            $jLog .= ', Total_Deduction=' . $Total_Deduction;
                                            $Net_Salary_Abs = $Nett_Gross - $Total_Deduction;

                                            $jLog .= ', $Net_Salary=' . $Net_Salary_Abs;
                                            $Net_Salary = round($Net_Salary_Abs);
                                            $jLog .= ', Net_Salary=' . $Net_Salary;
                                            $Net_SalaryRou = round($Net_Salary_Abs);

                                            if ($CTC == 0 && $Gross == 0) {
                                                $PF_Employer = $ESI_Employer = $PF_Employee = $ESI_Employee = $Special_Allowance = $EarnedCTC = $Basic = $HRA = $LTA = $Bonus = $PF_Employer = $ESI_Employer = $Nett_Gross = $EarnedGross = $Over_Time = $Other_earnings = $Total_Deduction = $PF_Employee = $ESI_Employee = $OT_ESI_Employee = $TDS = $Salary_Advance = $Excess_Telephoone_Usage = $Labour_Welfare = $Professional_Tax = $LOP = $Net_Salary = 0;
                                            }

                                            // ESI TOTALS
                                            $ESI_Employee_Total = $ESI_Employee + $OT_ESI_Employee;
                                            $ESI_Employer_Total = $ESI_Employer + $OT_ESI_Employer;
                                            $ESI_TOTAL = $ESI_Employee_Total + $ESI_Employer_Total;
                                            // PF TOTAL
                                            $PF_TOTAL = $PF_Employee + $PF_Employer;
                                            $jLog .= ', ESI_Employee_Total=' . $ESI_Employee_Total . ', ESI_Employer_Total=' . $ESI_Employer_Total . ', ESI_TOTAL=' . $ESI_TOTAL . ', PF_TOTAL=' . $PF_TOTAL;
                                            $vars = ['employee_id', 'finger_print_id', 'emp_code', 'fullname', 'SALARY_MONTH', 'salary_freeze', 'date_of_birth', 'date_of_joining', 'LOP', 'Basic', 'HRA', 'LTA', 'Special_Allowance', 'EarnedGross', 'Other_earnings', 'OT_PER_HOUR', 'Over_Time', 'OT_ESI_Employer', 'OT_ESI_Employee', 'ESI_Employee_Total', 'ESI_Employer_Total', 'ESI_TOTAL', 'PF_TOTAL', 'Nett_Gross', 'EarnedCTC', 'PF_Employee', 'ESI_Employee', 'TDS', 'Salary_Advance', 'Excess_Telephoone_Usage', 'Labour_Welfare', 'Professional_Tax', 'ESI_Employer', 'PF_Employer', 'Bonus', 'Total_Deduction', 'Net_Salary', 'payroll_upload_id'];

                                            $employee_id = $employee->employee_id;
                                            $emp_code = $employee->emp_code;
                                            $finger_print_id = $employee->finger_id;
                                            $fullname = $employee->fullname();
                                            $salary_freeze = $ExcelEmployee->salary_freeze;
                                            $t = 1;
                                            foreach ($vars as $key => $field) {
                                                if (isset($$field) && $PayrollStatement->salary_freeze == 0) {
                                                    $PayrollStatement->$field = $$field;
                                                    $t++;
                                                }
                                            }

                                            if ($PayrollStatement->salary_freeze == 0) {
                                                $branch_id = request()->get('branch_id');
                                                if (!$branch_id) {
                                                    if ($PayrollStatement->payroll_id) {
                                                        $PayrollStatement->update();
                                                    } else {
                                                        $PayrollStatement->save();
                                                    }
                                                }
                                            } else {
                                                foreach ($vars as $key => $field) {
                                                    if (isset($$field)) {
                                                        $$field = $PayrollStatement->$field;
                                                        $t++;
                                                    }
                                                }
                                            }
                                            $jLog .= ', EarnedGross=' . $EarnedGross;
                                        @endphp
                                        <tr>
                                            <td>{{ $SNO }}</td>
                                            <td>{{ $employee->finger_id }}</td>
                                            @foreach ($header as $key => $Data)
                                                @if (in_array($key, $filterset))
                                                    {{-- <td>{{ data($key, $testData) }}</td> --}}
                                                    <td>{{ displayData($key, $testData) }}</td>
                                                    <!-- $ExcelEmployee,$salary_month, -->
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>


                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<?php

function displayData($key, $Data) {
	global $header, $classification, $payroll, $additionals, $attendance;
	return $Data[$key] ?? '-';
	// if(in_array($key, $header)) {
	// 	return $Data[$key];
	// }
}

function data($set, $Data)
{
    switch ($set) {
        case 'username':
            return $Data['user_name'] ?? '-';
            break;
        case 'empcode':
            return $Data['emp_code'] ?? '-';
            break;
        case 'empcode':
            return $Data['emp_code'] ?? '-';
            break;
        case 'email':
            return $Data['email'] ?? '-';
            break;
        case 'mobie':
            return $Data['phone'] ?? '-';
            break;
        case 'gender':
            return $Data['gender'] ?? '-';
            break;
        case 'uan':
            return $Data['uan'] ?? '-';
            break;
		case 'costcenter':
            return $Data['cost_centre'] ?? '-';
            break;
        case 'pan':
			return $Data['pan'] ?? '-';
            break;
        case 'pfno':
		return $Data['pf_account_number'] ?? '-';
            break;
        case 'esi':
			return $Data['esi_card_number'] ?? '-';
            break;
        case 'religion':
			return $Data['religion'] ?? '-';
            break;
        case 'dob':
			return $Data['date_of_birth'] ?? '-';
            break;
        case 'doj':
            break;
        case 'salrev':
            break;
        case 'dol':
            break;
        case 'marital':
            break;
        case 'childs':
            break;
        case 'status':
            break;
        case 'ota':
            break;
        case 'epf':
            break;
        case 'emgencycno':
            break;
        case 'address':
		return $Data['address'] ?? '-';
            break;
        case 'photo':
            break;
        case 'role':
            break;
        case 'acno':
            return '1505101018624';
            break;
        case 'ifsc':
            break;
        case 'bankname':
            break;
        case 'bonus':
            break;
        case 'branch':
            break;
        case 'department':
            break;
        case 'designation':
            break;
        case 'workshif':
            break;
        case 'hod':
            break;
        case 'ctc':
            break;
        case 'cw':
            break;
        case 'ood':
            break;
        case 'cl':
            break;
        case 'sl':
            break;
        case 'pl':
            break;
        case 'l':
            break;
        case 'weekoff':
            break;
        case 'generalholiday':
            break;
        case 'paydays':
            break;
        case 'present':
            break;
        case 'salcaldays':
            break;
        case 'basic':
            break;
        case 'fixgross':
            break;
        case 'lta':
            break;
        case 'spl':
            break;
        case 'gross':
            break;

        default:
            break;
    }
}
?>
@section('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#financialyear').change(function() {
                var year = $(this).val();
                $.ajax({
                    url: "{{ url('monthlist') }}",
                    data: {
                        year: year
                    },
                    success: function(data, textStatus, jqXHR) {
                        $('.month-list').html(data);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log("Error");
                    }
                });



            });

        });
    </script>
@endsection
