@php
    use App\Lib\Enumerations\LeaveStatus;
    use App\Components\Common;
    $PayrollStatement = \App\Model\PayrollStatement::where('payroll_upload_id', $ExcelEmployee->payroll_upload_id)->first();
    $logo = 'data:image/png;base64,' . base64_encode(file_get_contents(asset('/admin_assets/img/in4_logo.jpg')));
    $Employee = $ExcelEmployee->Employee;
    $select_month = $salary_month = $PayrollStatement->salary_month;
    $date = new DateTime($select_month);
    $count_month = $date->format('Y-m');
    $SalaryRepository = new \App\Repositories\SalaryRepository;
    $vars = ['employee_id','finger_print_id','emp_code','fullname','SALARY_MONTH','salary_freeze','date_of_birth','date_of_joining','LOP','Basic','HRA','LTA','Special_Allowance','EarnedGross','Other_earnings','OT_PER_HOUR','Over_Time','OT_ESI_Employer','OT_ESI_Employee','Nett_Gross','EarnedCTC','PF_Employee','ESI_Employee','TDS','Salary_Advance','Excess_Telephoone_Usage','Labour_Welfare','Professional_Tax','ESI_Employer','PF_Employer','Bonus','Total_Deduction','Net_Salary','payroll_upload_id'];
    
    $total_month_days = count(findMonthToAllDate($count_month));
 
    $Total_Work_Days    = $total_month_days;
    $TODAY              = date('Y-m-d');
    $SALARY_MONTH       = $select_month.'-01';
    $Common             = new \App\Components\Common;
    $MONTH_NAME                 = strtoupper(date('F', strtotime($SALARY_MONTH)));
    $MONTH_YEAR                 = date('Y', strtotime($SALARY_MONTH));
    $employee_id                = $PayrollStatement->employee_id;
    $finger_print_id            = $PayrollStatement->finger_print_id;
    $emp_code                   = $PayrollStatement->emp_code;
    $fullname                   = $PayrollStatement->fullname;
     $LOP                        = $PayrollStatement->LOP;
    $Basic                      = $PayrollStatement->Basic;
    $HRA                        = $PayrollStatement->HRA;
    $LTA                        = $PayrollStatement->LTA;
    $Special_Allowance          = $PayrollStatement->Special_Allowance;
    $EarnedGross                = $PayrollStatement->EarnedGross;
    $Other_earnings             = $PayrollStatement->Other_earnings;
    $OT_PER_HOUR                = $PayrollStatement->OT_PER_HOUR;
    $Over_Time                  = $PayrollStatement->Over_Time;
    $OT_HOURS                   = $PayrollStatement->OT_HOURS;
    $OT_ESI_Employer            = $PayrollStatement->OT_ESI_Employer;
    $OT_ESI_Employee            = $PayrollStatement->OT_ESI_Employee;
    $Nett_Gross                 = $PayrollStatement->Nett_Gross;
    $EarnedCTC                  = $PayrollStatement->EarnedCTC;
    $PF_Employee                = $PayrollStatement->PF_Employee;
    $ESI_Employee               = $PayrollStatement->ESI_Employee;
    $TDS                        = $PayrollStatement->TDS;
    $Salary_Advance             = $PayrollStatement->Salary_Advance;
    $Excess_Telephoone_Usage    = $PayrollStatement->Excess_Telephoone_Usage;
    $Labour_Welfare             = $PayrollStatement->Labour_Welfare;
    $Professional_Tax           = $PayrollStatement->Professional_Tax;
    $ESI_Employer               = $PayrollStatement->ESI_Employer;
    $PF_Employer                = $PayrollStatement->PF_Employer;
    $Bonus                      = $PayrollStatement->Bonus;
    $Total_Deduction            = $PayrollStatement->Total_Deduction;
    $Net_Salary                 = $PayrollStatement->Net_Salary;
    $EmployeeLeaves             = $Employee->EmployeeLeaves;
    $optionalDeduction          = ['ESI_EMPLOYEE' => ($ESI_Employee + $OT_ESI_Employee), 'SALARY_ADVANCE' => $Salary_Advance, 'LABOUR_WELFARE_FUND' => $Labour_Welfare, 'TDS' => $TDS, 'EXCESS_TELEPHONE_USAGE' => $Excess_Telephoone_Usage];
    $displayDeduction           = [];
    $optionalEarnings           = ['SPECIAL_ALLOWANCE' => $Special_Allowance, 'OVER_TIME' => $Over_Time];
    $displayEarnings            = [];
    foreach ($optionalDeduction as $label => $value) {
        if($value>0) {
            $displayOne['label'] = $label;
            $displayOne['value'] = float2($value);
            $displayDeduction[] = $displayOne;
        }
    }

    $offset=1;
    foreach ($optionalEarnings as $label => $value) {
        if($value>0) {
            $displayOne['label'] = $label;
            $displayOne['value'] = float2($value);
            $displayEarnings[$offset] = $displayOne;
            $offset++;
        }
    }
    $set1 = count($displayDeduction);
    $set2 = count($displayEarnings);
    $maxIndex = $set2 > $set1 ? $set2 : $set1;
    $FirstLabel = '';
    $FirstValue = '';
    if($displayDeduction && isset($displayDeduction[0]) && isset($displayDeduction[0]['label']) && isset($displayDeduction[0]['value']) && $displayDeduction[0]['value']>0) {
        $FirstValue = $displayDeduction[0]['value'];
        $FirstLabel = $displayDeduction[0]['label'];
    }

@endphp
<style>
    .inr {
        font-family: DejaVu Sans; sans-serif;
        font-size: 11px;
    }
    .text-center {
        text-align: center !important;
    }
    .t-left {
        text-align: left !important;
    }
    .t-right {
        text-align: right !important;
    }
    .fullwidth {
        width: 100%;
    }
    #leavesTable {
        margin-top: 30px !important;
    }
    #leavesTable {
    }
    #payslipTable, #leavesTable {
        border: 2px solid;
        width: 100%;
    }
    #payslipTable .td-width2{
        width: 20%;
    }
    #payslipTable .td-width3{
        width: 30%;
    }
    #payslipTable > tbody > tr > td {
        padding-bottom: 5px;
        font-size: 11px;
        text-transform: uppercase;
    }
    #leavesTable > tbody > tr > td {
        padding:4px;
        font-size: 12px;
        text-transform: uppercase;
    }
    .bold {
        font-weight: bold;
    }
    .border-t {
        border-top: 1px solid;
    }
    .border-b {
        border-bottom: 1px solid;
    }
    #payslip_detail, #leavesTable {
        padding: 0;
        margin: 0;
    }
    #payslip_detail td {
        font-size: 10px;
        padding-left:5px;
        padding-right:5px;
    }
    .padding-r {
        padding-right: 15px;
    }
    .default-size {
        font-size:12px !important;
    }
    .default-size2 {
        font-size:13px !important;
    }
    .default-size3 {
        font-size:15px !important;
    }
    .nocase {
        text-transform: unset !important;
    }
</style>
<div id="payslipTableDiv" class="table-responsive" style="margin:0 24px">
    <table id="payslipTable" class="table table-bordered">
        <thead>
            <tr>
                <th style="text-align: left" style=""><img alt="Logo" src="{{ $logo }}" style="object-fit:contain;width:160px;border-radius:6px; margin-top:5px; margin-left:5px"></th>
                <th colspan="3" class="text-center"><span class="logo-lg">{{ Common::COMPANY_NAME }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center nocase" colspan="4" style="font-size:12px;margin-bottom:5px !important">{{ Common::COMPANY_ADDRESS }}</td>
            </tr>
            <tr>
                <td class="text-center bold" colspan="4" style="font-size:14px;margin-top:15px !important;margin-bottom:25px !important">PAYSLIP FOR THE MONTH OF {{ $MONTH_NAME }} {{ $MONTH_YEAR }}</td>
            </tr>
            <tr class="bold">
                <td class="td-width2">@lang('salary.EMPLOYEE_ID')</td>
                <td class="td-width2">{{ $ExcelEmployee->emp_code }}</td>
                <td class="td-width2">@lang('salary.NAME')</td>
                <td class="td-width3">{{ $Employee->fullname() }}</td>
            </tr>
            <tr>
                <td>@lang('salary.UAN')</td>
                <td>{{ $Employee->uan ?? '-' }}</td>
                <td>@lang('salary.DESIGNATION')</td>
                <td>{{ $Employee->designation->designation_name ?? '-' }}</td>
            </tr>
            <tr>
                <td>@lang('salary.PF_NO')</td>
                <td>{{ $Employee->pf_account_number ?? '-' }}</td>
                <td>@lang('salary.DEPARTMENT')</td>
                <td>{{ $Employee->department->department_name ?? '-' }}</td>
                
            </tr>
            <tr>
                <td>@lang('salary.ESI_NO ')</td>
                <td>{{ $Employee->esi_card_number ?? 'NOT APPLICABLE' }}</td>
                <td>@lang('salary.PAN')</td>
                <td>{{ $Employee->pan_gir_no ?? '-' }}</td>
            </tr>
            <tr>
                <td>@lang('salary.DOB')</td>
                <td>{{ dateConvertDBtoForm($Employee->date_of_birth) ?? '-' }}</td>
                <td>@lang('salary.BANK_NAME')</td>
                <td>{{ $Employee->bank_name ?? '-' }}</td>
            </tr>
            <tr>
                <td>@lang('salary.DOJ')</td>
                <td>{{ dateConvertDBtoForm($Employee->date_of_joining) ?? '-' }}</td>
                <td>@lang('salary.ACCOUNT NO')</td>
                <td>{{ $Employee->bank_account ?? '-' }}</td>
            </tr>
            <tr>
                <td>@lang('salary.PAY_DAYS')</td>
                <td>{{ ($PayrollStatement->fullname && is_numeric($PayrollStatement->fullname) ? $PayrollStatement->fullname : $Total_Work_Days)  }}</td>
                <td>@lang('salary.IFSC')</td>
                <td>{{ $Employee->bank_ifsc ?? '-' }}</td>
            </tr>
            <tr>
                <td>@lang('salary.LOP')</td>
                <td>{{ $ExcelEmployee->days_absents }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4">
                    <table class="fullwidth" id="payslip_detail" cellspacing=0 cellpading=0 style="margin-top: 15px;">
                        <tbody>
                            <tr>
                                <td class="border-b border-t" style="width:16%">@lang('salary.EARNINGS')</td>
                                <td class="border-b border-t t-right" style="width:7%">@lang('salary.AMOUNT')</td>
                                <td class="border-b border-t t-right padding-r"  style="width:5%">@lang('salary.CUM_EARNINGS')</td>
                                <td class="border-b border-t" style="width:15%">@lang('salary.DEDUCTIONS')</td>
                                <td class="border-b border-t t-right" style="width:7%">@lang('salary.AMOUNT')</td>
                                <td class="border-b border-t t-right" style="width:7%">@lang('salary.CUM_DEDUCTIONS')</td>
                            </tr>
                            <tr>
                                <td>@lang('salary.BASIC')</td>
                                <td class="t-right"><span class="inr">₹</span>{{ float2($Basic) }}</td>
                                <td class="t-right"></td>
                                <td>@lang('salary.PROVIDENT_FUND')</td>
                                <td class="t-right"><span class="inr">₹</span>{{ float2($PF_Employee) }}</td>
                                <td class="t-right"></td>
                            </tr>
                            <tr>
                                <td>@lang('salary.HRA')</td>
                                <td class="t-right"><span class="inr">₹</span>{{ float2($HRA) }}</span></td>
                                <td class="t-right"></td>
                                <td>@lang('salary.PROFESSIONAL_TAX')</td>
                                <td class="t-right"><span class="inr">₹</span>{{ float2($Professional_Tax) }}</td>
                                <td class="t-right"></td>
                            </tr>
                            <tr>
                                <td>@lang('salary.LEAVE_TRAVEL_ALLOWANCE')</td>
                                <td class="t-right"><span class="inr">₹</span>{{ float2($LTA) }}</td>
                                <td></td>
                                @if ($FirstLabel && $FirstValue)
                                    <td>@lang('salary.'.$FirstLabel)</td>
                                    <td class="t-right"><span class="inr">₹</span>{{ $FirstValue }}</td>
                                @else
                                    <td></td>
                                    <td class="t-right"></td>
                                @endif
                                <td class="t-right"></td>
                            </tr>
                            @for ($key=1; $key<=$maxIndex; $key++)
                                <tr>
                                    @if (isset($displayEarnings[$key]) && isset($displayEarnings[$key]['label']) && isset($displayEarnings[$key]['value']) && $displayEarnings[$key]['value']>0)
                                        <td>@lang('salary.'.$displayEarnings[$key]['label'])</td>
                                        <td class="t-right"><span class="inr">₹</span>{{ $displayEarnings[$key]['value'] }}</td>
                                    @else
                                        <td class="t-right"></td>
                                        <td class="t-right"></td>
                                    @endif
                                    <td></td>

                                    @if ($displayDeduction && isset($displayDeduction[$key]) && isset($displayDeduction[$key]['label']) && isset($displayDeduction[$key]['value']) && $displayDeduction[$key]['value']>0)
                                        <td>@lang('salary.'.$displayDeduction[$key]['label'])</td>
                                        <td class="t-right"><span class="inr">₹</span>{{ $displayDeduction[$key]['value'] }}</td>
                                    @else
                                        <td class="t-right"></td>
                                        <td class="t-right"></td>
                                    @endif
                                    <td></td>
                                </tr>
                            @endfor

                            <tr>
                                <td class="border-b border-t">@lang('salary.GROSS_TOTAL')</td>
                                <td class="border-b border-t t-right"><span class="inr">₹</span>{{ float2($Nett_Gross) }}</td>
                                <td class="border-b border-t"></td>
                                <td class="border-b border-t">@lang('salary.DEDUCTIONS')</td>
                                <td class="border-b border-t t-right"><span class="inr">₹</span>{{ float2($Total_Deduction) }}</td>
                                <td class="border-b border-t"></td>
                            </tr>

                            <tr>
                                <td colspan="6" class="default-size">
                                    <br>
                                    <span>@lang('salary.NET_PAY')</span>: &nbsp;&nbsp;&nbsp; <span class="inr">₹</span><span class="bold">{{  float2($Net_Salary) }}</span><br>
                                    <p>@lang('salary.IN_WORDS'): &nbsp; <span class="bold">{{ strtoupper(amountToWords($Net_Salary)) }}</span></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <br>
                                    <br>
                                </td>
                            </tr>
                        </tbody>
                        
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    
                </td>
            </tr>
        </tbody>
    </table>
    @if ($EmployeeLeaves)
    @php
        $CL  = DB::table('leave_type')->where('leave_type_id', 1)->first();
        $SL  = DB::table('leave_type')->where('leave_type_id', 2)->first();
        $PL  = DB::table('leave_type')->where('leave_type_id', 3)->first();
        $OD  = DB::table('leave_type')->where('leave_type_id', 4)->first();
        $MTL = DB::table('leave_type')->where('leave_type_id', 5)->first();
        $PTL = DB::table('leave_type')->where('leave_type_id', 6)->first();
        $start_date = $SALARY_MONTH;
        $end_date = date("Y-m-t", strtotime($SALARY_MONTH));
        $CREDIT_CL = 0;
        $CREDIT_SL = 0;
        $CREDIT_PL = 0;
        $CREDIT_OD = 0;
        $TOTAL_MTL = 0;
        $TOTAL_PTL = 0;
        $TOTAL_CL = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
            ->where('leave_type.leave_type_id', 1)->count();
        $TOTAL_SL = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
            ->where('leave_type.leave_type_id', 2)->count();
        $TOTAL_PL = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
            ->where('leave_type.leave_type_id', 3)->count();
        $TOTAL_OD = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
            ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
            ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
            ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
            ->where('leave_type.leave_type_id', 4)->count();

        $govtHolidays = DB::select(DB::raw('call SP_getHoliday("' . $start_date . '","' . $end_date . '")'));
        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));


        $maternityPloicy = $Employee->maternity_leave_ploicy();
        $paternityPloicy = $Employee->paternity_leave_ploicy();
        if($Employee->gender=='Male') {
            if(isset($paternityPloicy['status']) && $paternityPloicy['status']) {
                $paternity = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
                    ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
                    ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
                    ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
                    ->where('leave_type.leave_type_id', 6)->first();
                if($paternity) {
                    $TOTAL_PTL = $paternity->number_of_day;
                }
            }
        } else if($Employee->gender=='Female') {
            if(isset($maternityPloicy['status']) && $maternityPloicy['status']) {
                $maternity = DB::table('leave_application')->select('leave_application.application_from_date', 'leave_application.application_to_date', 'employee_id', 'leave_type_name')
                    ->join('leave_type', 'leave_type.leave_type_id', 'leave_application.leave_type_id')
                    ->whereRaw("leave_application.application_from_date >= '" . $start_date . "' and leave_application.application_to_date <=  '" . $end_date . "'")
                    ->where('status', LeaveStatus::$APPROVE)->where('employee_id', $Employee->employee_id)->where('leave_type.leave_type_id', 1)
                    ->where('leave_type.leave_type_id', 5)->first();
                if($maternity) {
                    $TOTAL_MTL = $maternity->number_of_day;
                }
            }
        }

    @endphp
    <table id="leavesTable" class="table table-bordered" cellspacing=0>
        <tr>
            <td colspan="5" class="t-left">@lang('salary.LEAVE_DETAIL')</td>
        </tr>
        <tbody>
            <tr>
                <td class="border-b border-t">@lang('salary.LEAVE_TYPE')</td>
                <td class="border-b border-t">@lang('salary.OPENING')</td>
                <td class="border-b border-t">@lang('salary.CREDIT')</td>
                <td class="border-b border-t">@lang('salary.UTILIZED')</td>
                <td class="border-b border-t">@lang('salary.BALANCE')</td>
            </tr>
            <tr>
                <td>{{ $CL->leave_type_name }}</td>
                <td>{{ float1($EmployeeLeaves->casual_leave) }}</td>
                <td>{{ float1($CREDIT_CL) }}</td>
                <td>{{ float1($TOTAL_CL) }}</td>
                <td>{{ float1(($EmployeeLeaves->casual_leave - $TOTAL_CL) + $CREDIT_CL) }}</td>
            </tr>
            <tr>
                <td>{{ $SL->leave_type_name }}</td>
                <td>{{ float1($EmployeeLeaves->sick_leave) }}</td>
                <td>{{ float1($CREDIT_SL) }}</td>
                <td>{{ float1($TOTAL_SL) }}</td>
                <td>{{ float1(($EmployeeLeaves->sick_leave - $TOTAL_SL) + $CREDIT_SL) }}</td>
            </tr>
            <tr>
                <td>{{ $PL->leave_type_name }}</td>
                <td>{{ float1($EmployeeLeaves->privilege_leave) }}</td>
                <td>{{ float1($CREDIT_PL) }}</td>
                <td>{{ float1($TOTAL_PL) }}</td>
                <td>{{ float1(($EmployeeLeaves->privilege_leave - $TOTAL_PL) + $CREDIT_PL) }}</td>
            </tr>
            @if ($TOTAL_OD>0)
            <tr>
                <td>{{ $OD->leave_type_name }}</td>
                <td>{{ '-' }}</td>
                <td>{{ '-' }}</td>
                <td>{{ float1($TOTAL_OD) }}</td>
                <td>{{ '-' }}</td>
            </tr>
            @endif
            @if ($TOTAL_MTL>0)
            <tr>
                <td>{{ $MTL->leave_type_name }}</td>
                <td>{{ '-' }}</td>
                <td>{{ '-' }}</td>
                <td>{{ float1($TOTAL_MTL) }}</td>
                <td>{{ '-' }}</td>
            </tr>
            @endif
            @if ($TOTAL_PTL>0)
            <tr>
                <td>{{ $PTL->leave_type_name }}</td>
                <td>{{ '-' }}</td>
                <td>{{ '-' }}</td>
                <td>{{ float1($TOTAL_PTL) }}</td>
                <td>{{ '-' }}</td>
            </tr>
            @endif
        </tbody>
    </table>
    @endif
    <br>
    <br>
    <p class="default-size">This is computer generated Payslip does not require Signature.</p>
</div>
