<?php

namespace App\Http\Controllers\Payroll;

use DateTime;
use Carbon\Carbon;
use App\Model\Payroll;
use App\Model\Employee;
use App\Components\Common;
use App\Model\OverTimeSetup;
use Illuminate\Http\Request;
use App\Model\HolidayDetails;
use App\Model\LeavePermission;
use App\Model\PayrollSettings;
use App\Model\AdvanceDeduction;
use App\Model\LeaveApplication;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Model\ViewEmployeeInOutData;
use App\Model\ProfessionalTax;
use Illuminate\Support\Facades\Validator;

class BulkGenerateController extends Controller
{
    public function bulkGeneratePreview(Request $request)
    {
        \set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'month' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->getMessageBag()->first()]);
        } 
        $payroll      = [];
            foreach (Employee::where('generate_salary', 1)->where('status', 1)->get() as $key => $employee) {
                $payroll[] = $this->calcuation($request, $employee);
                // $payroll = Payroll::where('employee', $dataSet['employee'])->where('month', $dataSet['month'])->where('year', $dataSet['year'])->first();
            } 
             
        return view('admin.payroll.salary.bulkpreview', ['payroll'=>$payroll, 'month'=>$request->month,'from_date'=>$request->from_date ,'to_date'=>$request->to_date ]); 
        // return redirect('bulkpreview')->with(['payroll'=>$payroll, 'month'=>$request->month,'from_date'=>$request->from_date ,'to_date'=>$request->to_date ]);  
    }
    
    public function bulkGenerate(Request $request)
    {
        \set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'month' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->getMessageBag()->first()]);
        }

        try {

            DB::beginTransaction();
            foreach (Employee::where('generate_salary', 1)->where('status', 1)->get() as $key => $employee) {
                $dataSet = $this->calcuation($request, $employee);
                $payroll = Payroll::where('employee', $dataSet['employee'])->where('month', $dataSet['month'])->where('year', $dataSet['year'])->first();
                if ($payroll) {
                    $payroll->update($dataSet);
                } else {
                    Payroll::create($dataSet);
                }
            }
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Payroll generated successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 200);
        }
    }


    public function calcuation($request, $employee)
    {
        $expl_month_year = explode('-', $request->month);
        $request_month = $expl_month_year[1];
        $request_year = $expl_month_year[0];
        $start_date = DATE('Y-m-d', strtotime($request->from_date));
        $end_date = DATE('Y-m-d', strtotime($request->to_date));

        $present_days = ViewEmployeeInOutData::where('finger_print_id', $employee->finger_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->where('working_time', '!=', '00:00:00')
            ->count();

        $public_holidays = HolidayDetails::whereBetween('from_date', [$start_date, $end_date])->get();
        $allowed_leave = LeaveApplication::where('employee_id', $employee->employee_id)
            ->whereBetween('application_from_date', [$start_date, $end_date])->where('status',2)
            ->get();

        $month_logs = ViewEmployeeInOutData::where('finger_print_id', $employee->finger_id)
            ->whereBetween('date', [$start_date, $end_date])
            ->where('working_time', '!=', '00:00:00')
            ->get();
        $half_day = 0;

        foreach ($month_logs as $Data) {
            /* dd($Data->date); */
            $check_leave_permissions = LeavePermission::where('employee_id', $employee->employee_id)
                ->where('status', 1)
                ->where('leave_permission_date', '=', $Data->date)
                ->where('department_approval_status', 1)
                ->where('plant_approval_status', 1)
                /* ->whereYear('leave_permission_date', '=', $request_year) */
                ->first();

            if (!$check_leave_permissions) {
                if (strtotime('06:00:00') >= strtotime($Data->working_time)) {
                    $half_day += 0.5;
                }
            } elseif ($check_leave_permissions) {
                if (strtotime('04:00:00') >= strtotime($Data->working_time)) {
                    $half_day += 0.5;
                }
            }
        }

        $request_date = DATE('d-m-Y', strtotime($request->from_date));
        $diff = strtotime($request->to_date) - strtotime($request->from_date);
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        $total_days_fromdates = abs(round($diff / 86400)) + 1;

        $holiday_count = 0;
        foreach ($public_holidays as $HolidayData) {
            $exclude_sunday = count(Common::excludesundays($HolidayData->from_date, $HolidayData->to_date));
            $holiday = Common::dayscount($HolidayData->from_date, $HolidayData->to_date);
            $holiday_count += $holiday - $exclude_sunday;
        }

        $allow_leave = 0;
        $cl_count = $sl_count = $el_count = 0;
        foreach ($allowed_leave as $LeaveData) {
            $allow_leave += $LeaveData->number_of_day;
                                    if ($LeaveData->leave_type_id == 1) {
                                        if($LeaveData->day_type == 1){
                                            $cl_count += $LeaveData->number_of_day;
                                        }else{
                                            $cl_count += ($LeaveData->number_of_day*0.5);
                                        }
                                    } elseif($LeaveData->leave_type_id == 2) {
                                        if($LeaveData->day_type == 1){
                                            $sl_count += $LeaveData->number_of_day;
                                        }else{
                                            $sl_count += ($LeaveData->number_of_day*0.5);
                                        }                                        
                                    } elseif($LeaveData->leave_type_id == 3) {
                                        if($LeaveData->day_type == 1){
                                            $el_count += $LeaveData->number_of_day;
                                        }else{
                                            $el_count += ($LeaveData->number_of_day*0.5);
                                        }
                                    }
        }
        $sundays = 0;
        $sundays = Common::datestotalsundays($start_date, $end_date);

        $working_days_count = 26;

        $worked_days_count = $present_days + $holiday_count + $allow_leave;

        if ($working_days_count < $worked_days_count) {
            $worked_days = $working_days_count;
            $working_days = $working_days_count;
        } else {
            $worked_days = $worked_days_count;
            $working_days = $working_days_count;
        }

        $settings = PayrollSettings::find($employee->category_id);
        $ctc = $employee->salary;
        $month_salary = $employee->salary / 12;
        $day_salary = round($month_salary / $working_days, 2);
        $per_hour = round($day_salary / $settings->day_hour);
        $worked_salary = round($day_salary * $worked_days);

        $basic = round($worked_salary * ($settings->basic / 100));
        $da = round($basic * ($settings->da / 100));
        $hra = round($basic * ($settings->hra / 100));

        $full_worked_salary = round($day_salary * $working_days);
        $full_basic = round($full_worked_salary * ($settings->basic / 100));
        $full_da = round($full_basic * ($settings->da / 100));
        $full_hra = round($full_basic * ($settings->hra / 100));

        $conveyance = $settings->conveyance_allowance;
        $medical = $settings->medical_allowance;
        $children = $settings->children_allowance;
        $lta = $settings->lta;

        //Convert to Per month
        $conveyance = $conveyance / 12;
        $medical = $medical / 12;
        $children = $children / 12;
        $lta = $lta / 12;
        /* Advance Deduction Calculation  */

        $salary_date = $request->month . '-01';
        $advdeductionamount = 0;
        $advancededuction = AdvanceDeduction::where('employee_id', '=', $employee->employee_id)
            ->where('status', '=', '1')
            ->first();
        if (!empty($advancededuction)) {
            $amount = $advancededuction->deduction_amouth_per_month;
            $date = $advancededuction->date_of_advance_given;
            $start_date = new DateTime($date);
            $total_period = $advancededuction->no_of_month_to_be_deducted + 1;
            $end_period = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->addMonth($total_period);
            if (date('d-m-Y', strtotime($end_period)) > date('d-m-Y', strtotime($salary_date))) {
                $advdeductionamount = $advancededuction->deduction_amouth_per_month;
            } else {
                $advdeductionamount = 0;
            }
        }

        /* End OT Calculation */
        $profident_fund = $month_salary * (12 / 100);
	if($employee->pf_status == 2){
        	if ($profident_fund > 1800) {
            		$pf = 1800;
        	} else {
            		$pf = $profident_fund;
        	}
	}else{
		$pf = 0;
	}
        $special = $worked_salary - ($basic + $da + $hra + $conveyance + $medical + $children + $lta + $pf);
        $other = $worked_salary - ($basic + $da + $hra + $conveyance + $medical + $children + $lta + $pf);
        //   dd($special);
        if ($special < 0) {
            $special = 0;
        }
        if ($other < 0) {
            $other = 0;
        }

        if ($employee->category_id == 3) {
            $special = 0;
        } else {
            $other = 0;
        }

        $annual_basic = $employee->salary * ($settings->basic / 100);
        if ($da) {
            $annual_da = $annual_basic * ($settings->da / 100);
        } else {
            $annual_da = 0;
        }
        if ($hra) {
            $annual_hra = $annual_basic * ($settings->hra / 100);
        } else {
            $annual_hra = 0;
        }

        $annual_medical = $settings->medical_allowance + 0;
        $annual_conveyance = $settings->conveyance_allowance + 0;
        $annual_children = $settings->children_allowance + 0;
        $annual_lta = $settings->lta + 0;
        if($employee->pf_status == 2){
		    $annual_pf = $pf * 12;
	    }else{
		    $annual_pf =0;
        }

        if ($special) {
            $annual_special = $employee->salary - $annual_basic - $annual_hra - $annual_da - $annual_conveyance - $annual_medical - $annual_lta - $annual_pf - $annual_children;
        } else {
            $annual_special = 0;
        }

        $annual_gross = $annual_basic + $annual_hra + $annual_da + $annual_conveyance + $annual_medical + $annual_lta + $annual_pf + $annual_children;
        $month_grosssalary = $basic + $da + $hra + $conveyance + $lta + $pf + $children + $medical;

        $perday_salary = ($employee->salary / 12 - $pf) / $working_days;
        $hour_salary = $perday_salary / $settings->day_hour;
        $worked_salary = $perday_salary * $worked_days;

        if ($other) {
            $annual_other = $employee->salary - $annual_basic - $annual_hra - $annual_da - $annual_conveyance - $annual_medical - $annual_lta - $annual_pf - $annual_children;
        } else {
            $annual_other = 0;
        }

        /* End Advance Deduction Calculation */
        if ($employee->ot_status== 2) {
            if (isset($empovertime) && $empovertime != 0) {
                $checkovertimesetup = OverTimeSetup::where('ot_designation_id', $employee->designation_id)->first();
                if ($checkovertimesetup) {
                    $overtime_amount = $empovertime * $checkovertimesetup->ot_amount;
                } else {
                    $overtime_amount = $empovertime * $hour_salary;
                }
            } else {
                $empovertime = 0;
                $overtime_amount = 0;
            }
        } else {
            $empovertime = 0;
            $overtime_amount = 0;
        }

        $month = (int) DATE('m');

        $professinoal_tax = ProfessionalTax::whereRaw('FIND_IN_SET(' . $month . ',months)')
            ->where('status', 1)
            ->first();
 		if($employee->pt_status == 2){
			$professinoal_tax_amt = $professinoal_tax->amount;
		}else{
			$professinoal_tax_amt = 0;
		}


        $income_tax_month = 0;

        $lop = $half_day;
        $lop_amount = $perday_salary * $lop;
        $absent_count = $working_days - $worked_days;
        if ($absent_count >= 0) {
            $absent = $working_days - $worked_days;
        } elseif ($absent_count < 0) {
            $absent = 0;
        }

        $leave_permissions = LeavePermission::where('employee_id', $employee->employee_id)
            ->where('status', 1)
            ->whereBetween('leave_permission_date', [$start_date, $end_date])
            ->where('department_approval_status', 1)
            ->where('plant_approval_status', 1)
            ->count();

        foreach ($allowed_leave as $LeaveData) {
            if ($LeaveData->leave_type_id == 4) {
                $lop = $lop + $LeaveData->number_of_day;
                $lop_amount = $lop_amount + $LeaveData->number_of_day * $perday_salary;
            }
        }
        $lop_amount = $lop_amount + $perday_salary * $absent;
        $gross_amount = $full_basic + $full_da + $full_hra + $conveyance + $medical + $children + $lta + $special + $other + 0 + $overtime_amount;

        $net_amount = (($annual_basic / 12) + ($annual_da / 12) + ($annual_hra / 12) + ($annual_conveyance / 12) + ($annual_medical / 12) + ($annual_lta / 12) + ($annual_pf / 12) + ($annual_children / 12) + ($other / 12) + ($annual_special / 12) - $pf) - ($professinoal_tax_amt + $pf + $lop_amount + $advdeductionamount + 0);
        
        $tempArr['employee'] = $employee->employee_id;
        $tempArr['employee_name'] = $employee->first_name.''.$employee->last_name;
        $tempArr['finger_print_id'] = $employee->finger_id;
        $tempArr['department'] = $employee->department_id;
        $tempArr['department_name'] = $employee->department->department_name;
        $tempArr['yearly_ctc'] = $employee->salary;
        $tempArr['month'] = $request_month;
        $tempArr['year'] = $request_year;
        $tempArr['days_in_month'] = $total_days_fromdates;
        $tempArr['sundays'] = $sundays;
        $tempArr['holidays'] = $holiday_count;
        $tempArr['cl'] = $cl_count;
        $tempArr['sl'] = $sl_count;
        $tempArr['el'] = $el_count;
        $tempArr['lop'] = $lop;
        $tempArr['absent'] = $absent;
        $tempArr['lop_amount'] = round($lop_amount,2);
        $tempArr['working_days'] = $working_days;
        $tempArr['worked_days'] = $worked_days;
        $tempArr['month_salary'] = round($month_salary,2);
        $tempArr['day_salary'] = round($perday_salary,2);
        $tempArr['hour_salary'] =round( $hour_salary,2);
        $tempArr['worked_salary'] = round($worked_salary,2);
        $tempArr['basic'] = $basic;
        $tempArr['da'] = $da;
        $tempArr['hra'] = $hra;
        $tempArr['conveyance'] = $conveyance;
        $tempArr['medical'] = $medical;
        $tempArr['children'] = $children;
        $tempArr['lta'] = $lta;
        $tempArr['special'] = $special;
        $tempArr['other'] = $other;
        $tempArr['income_tax'] = 0;
        $tempArr['professional_tax'] = $professinoal_tax_amt;
        $tempArr['wages_earnings'] = round((($annual_basic / 12) + ($annual_da / 12) + ($annual_hra / 12) + ($annual_conveyance / 12) + ($annual_medical / 12) + ($annual_lta / 12) + ($annual_pf / 12) + ($annual_children / 12) + ($other / 12) + ($annual_special / 12) - $pf),2);
        $tempArr['deduction'] = round($professinoal_tax_amt + $lop_amount + $pf + $advdeductionamount,2);
        $tempArr['net_amount'] = $net_amount > 0 ? round($net_amount,2) : 0;
        $tempArr['basic_percentage'] = $settings->basic;
        $tempArr['hra_percentage'] = $settings->hra;
        $tempArr['da_percentage'] = $settings->da;
        $tempArr['full_basic'] = $full_basic;
        $tempArr['full_da'] = $full_da;
        $tempArr['full_hra'] = $full_hra;
        $tempArr['full_conveyance'] = $conveyance;
        $tempArr['full_medical'] = $medical;
        $tempArr['full_lta'] = $lta;
        $tempArr['full_special'] = $special;
        $tempArr['full_other'] = $other;
        $tempArr['ot_amount'] = $overtime_amount;
        $tempArr['ot_hour'] = $empovertime;
        $tempArr['pf_amount'] = $pf;
        $tempArr['advance_deduction'] = $advdeductionamount;
        $tempArr['full_wages_earnings'] = ($full_basic + $full_da + $full_hra + $conveyance + $medical + $children + $lta + $special + $other + 0 + $overtime_amount);

        return $tempArr;
    }
}
