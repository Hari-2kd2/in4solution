<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use App\Model\PayrollUpload;
use App\Model\RhApplication;
use App\Model\EmployeeLeaves;
use App\Model\LeaveApplication;
use Illuminate\Console\Command;
use App\Model\RhApplicationCase;
use App\Model\LeavePermissionCase;
use Illuminate\Support\Facades\DB;
use App\Model\LeaveApplicationCase;
use Illuminate\Support\Facades\Log;
use App\Model\EmployeeInOutDataCase;
use App\Lib\Enumerations\LeaveStatus;
use App\Model\LeaveCreditTransactions;
use App\Repositories\SalaryRepository;
use App\Lib\Enumerations\AttendanceStatus;

class LeaveCredit extends Command
{

    protected $signature = 'leaves:credit {--today=false}';


    protected $description = 'Employee leave credit background jobs';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        Log::info('Starts LeaveCredit ' . __FUNCTION__.'()');
        set_time_limit(0);
        $LOG = '';
        $TODAY = $this->option('today');
        if(!$TODAY || $TODAY=='false' || $TODAY===false) {
            $TODAY = date('Y-m-d');
        }
        Log::info('TODAY='.$TODAY);
        $IS_NEW_YEAR = date('z', strtotime($TODAY));
        if($IS_NEW_YEAR==0) {

        }

        $calanderYear = calanderYear::currentYear();
        if(!$calanderYear || !$calanderYear->year_id) {
            // $calanderYear = calanderYear::currentYear(date('Y') - 1);
            Log::info('calanderYear master record not found, please first run calanderYear after few minutes only can run this credit process.');
            return false;
        }
        
        try {
            DB::beginTransaction();
            $this->leavesAndPremissionExpired();
            // pending leave, RH, permissions application if admin side approve or rejection is missing then system will be set expired status to all type applications - starts
            // fetch first pending leaves group by month
            $LeaveApplicationAll = LeaveApplication::where('status', LeaveStatus::$PENDING)->get();
            $salary_date = '2023-12-01';
            $sdate = new \DateTime( $salary_date );
            $edate = new \DateTime( $salary_date );
            $sdate = $sdate->modify('previous month');
            $previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
            $current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
            // pending leave application if admin side approve or rejection is missing then system will be set expired status to leave applications - ends


            $LOG .= 'Executed Data: '.$TODAY.PHP_EOL;
            Log::info("CRON is working fine!");
            // NON PERMANENT LIST (PROBATION PERIOD) CREDIT PROCESS STARTED
            $cron_executed_on = DB::table('leave_credit_settings')->max('cron_executed_on');
            $cron_changes = 0;
            $EmployeeList = Employee::where('permanent_status', 0)->orderBy('date_of_joining', 'ASC')->get();
            // to avoid already executed date given again or less than date give it will not execute
            info("TODAY=$TODAY > cron_executed_on=$cron_executed_on");
            if($TODAY > $cron_executed_on || !$cron_executed_on) {
                $SalaryRepository = new SalaryRepository;
                $ClLeaveType = LeaveType::find(1);
                $SlLeaveType = LeaveType::find(2);
                $PlLeaveType = LeaveType::find(3);
    
                // First year PL starts
                // perform to permanent employee credits every year
                // year service completion status update for DOJ is mid year date 
                $LOG .= PHP_EOL.'First year PL starts'.PHP_EOL;
                $LOG .= '--------------------'.PHP_EOL;
                foreach ($EmployeeList as $Employee) {
                    if(!$Employee->date_of_joining) {
                        $LOG0 = 'Emp Code : '.$Employee->emp_code.PHP_EOL;
                        $LOG0 = 'date_of_joining : '.$Employee->date_of_joining.' is not available'.PHP_EOL;
                        $LOG .= $LOG0.PHP_EOL.PHP_EOL;
                        continue;
                    }
                    $yearCompletionDate = addYear($Employee->date_of_joining, $SalaryRepository->SERVICE_COMPLETE_YEAR);
                    if($yearCompletionDate==$TODAY) {
                        $LOG0 = 'First year complete Emp Code : '.$Employee->emp_code.PHP_EOL;
                        $LOG0 .= 'PL Credits: '.$Employee->emp_code.PHP_EOL;
                        $Employee->permanent_status = 1;
                        $Employee->update();
                        $EmployeeLeaves0 = $Employee->EmployeeLeaves;
                        // LOP take and calc
                        // here we will take past year LOSS OF PAY days from attentance log absent status
                        $LOP_DAYS = EmployeeInOutDataCase::where('finger_print_id', $Employee->finger_id)->where('date', '>=', $Employee->date_of_joining)->where('date', '<=', $yearCompletionDate)->where('attendance_status', AttendanceStatus::$ABSENT)->count();
                        $LOG0 .= 'LOP_DAYS : '.$LOP_DAYS.PHP_EOL;
                        $SLAB = DB::table('privilege_leave_lop_slab')->select('pl_months')->whereRaw("$LOP_DAYS >= pl_lop_from AND $LOP_DAYS <= pl_lop_to")->first();
                        $pl_months = ($SLAB->pl_months ?? '');
                        $PlCreditAfterOneYear0 = round($PlLeaveType->num_of_day * 12 / 12);
                        if($pl_months==0) {
                            $LOG0 .= 'No credits PL, over LOP ' . $LOP_DAYS. ', LOP SLAB=' . $pl_months . PHP_EOL;
                            $PlCreditAfterOneYear0 = 0;
                        } else if($pl_months>0) {
                            $PlCreditAfterOneYear0 = round($PlLeaveType->num_of_day * $pl_months / 12);
                            $LOG0 .= 'Master PL value = ' . $PlLeaveType->num_of_day. ', LOP SLAB=' . $pl_months . PHP_EOL;
                            $LOG0 .= 'Credited PL = month slab based ' . $PlCreditAfterOneYear0 . PHP_EOL;
                        }

                        if(!$EmployeeLeaves0) {
                            $EmployeeLeaves0 = new EmployeeLeaves;
                            $EmployeeLeaves0->employee_id = $Employee->employee_id;
                            $EmployeeLeaves0->branch_id = $Employee->branch_id;
                        }
                        $EmployeeLeaves0->privilege_leave = $PlCreditAfterOneYear0;
                        if($EmployeeLeaves0->leave_id) {
                            $EmployeeLeaves0->update();
                        } else {
                            $EmployeeLeaves0->save();
                        }
                        $LOG .= $LOG0.PHP_EOL.PHP_EOL;
                    }
                }
                // First year PL ends

                if($IS_NEW_YEAR==0) {
                    $LOG .= PHP_EOL.'Year service completion status update for DOJ is new calander year date is same'.PHP_EOL;
                    $LOG .= '--------------'.PHP_EOL;
                    // Year service completion status update for DOJ is new calander year date is same
                    $oneYearEndList = Employee::where('permanent_status', 0)->orderBy('date_of_joining', 'ASC')->get();
                    foreach ($oneYearEndList as $Employee) {
                        $yearCompletionDate = addYear($Employee->date_of_joining, $SalaryRepository->SERVICE_COMPLETE_YEAR);
                        if($yearCompletionDate==$TODAY) {
                            $LOG .= 'Calendar year DOJ Emp Code : '.$Employee->emp_code.PHP_EOL;
                            $LOG .= 'Year Completion Date : '.$$yearCompletionDate.PHP_EOL;
                            $Employee->permanent_status = 1;
                            $Employee->update();
                            // year service completion status update the rest leave credits process will handle // fetch permanent employee to process leave credit
                        }
                    }

                    // fetch permanent employee to process leave credit
                    $LOG .= PHP_EOL.'fetch permanent employee to process leave credit'.PHP_EOL;
                    $LOG .= '--------------'.PHP_EOL;
                    $permanentEmployeeList = Employee::where('permanent_status', 1)->orderBy('date_of_joining', 'ASC')->get();
                    foreach ($permanentEmployeeList as $Employee) {
                        $EmployeeLeaves = $Employee->EmployeeLeaves;
                        // $EmployeeLeaves = EmployeeLeaves::where('employee_id',$Employee->employee_id)->first();
                        if(!$EmployeeLeaves) {
                            $EmployeeLeaves = new EmployeeLeaves;
                            $EmployeeLeaves->employee_id = $Employee->employee_id;
                            $EmployeeLeaves->branch_id = $Employee->branch_id;
                        }
                        $elapsedSl = 0;
                        $LOG1 = 'Emp Code : '.$Employee->emp_code.PHP_EOL;
                        // check if less then from max limit 45 as per levave policy
                        if($EmployeeLeaves->sick_leave<45) {
                            $LOG1 .= 'Yearly SL : '.$TODAY.PHP_EOL;
                            $EmployeeLeaves->sick_leave += $SlLeaveType->num_of_day;
                            // if more then 45 then set max limit 45 as per levave policy
                            if($EmployeeLeaves->sick_leave>45) {
                                $elapsedSl = $EmployeeLeaves->sick_leave - 45;
                                $LOG1 .= '>45 so elapsed SL = ' . $EmployeeLeaves->sick_leave . ' - 45 = ' . $elapsedSl . PHP_EOL;
                                $EmployeeLeaves->sick_leave = 45;
                                $LOG1 .= 'Set SL max = 45 ' . PHP_EOL;
                            } else {
                                $LOG1 .= 'Credited SL = ' . $SlLeaveType->num_of_day . PHP_EOL;
                            }
                        } else {
                            $LOG1 = 'Yearly SL : '.$TODAY.PHP_EOL;
                            $LOG1 = 'Not SL Credit because already have '.$EmployeeLeaves->sick_leave.PHP_EOL;
                        }
                        
                        
                        $LeaveCreditTransactions = new LeaveCreditTransactions;
                        $LeaveCreditTransactions->employee_id = $Employee->employee_id;
                        $LeaveCreditTransactions->branch_id = $Employee->branch_id;
                        $LeaveCreditTransactions->trn_credit_on = Carbon::now();
                        $LeaveCreditTransactions->trn_credit_days = $elapsedSl > 0 ? $elapsedSl : $SlLeaveType->num_of_day;
                        $LeaveCreditTransactions->trn_remark = $LOG1;
                        $LeaveCreditTransactions->trn_leave_type_id = 2; // Sick leave
                        $LeaveCreditTransactions->year_id = $calanderYear->year_id;
                        $LeaveCreditTransactions->save();
                        unset($LeaveCreditTransactions);
                        
                        // every year CL will not carry over, so assgin fresh from master value
                        $EmployeeLeaves->casual_leave = $ClLeaveType->num_of_day;
                        $LOG1 .= 'Yearly CL : '.$TODAY.PHP_EOL;
                        $LOG1 .= 'Credited CL = ' . $ClLeaveType->num_of_day . PHP_EOL;

                        $LeaveCreditTransactions = new LeaveCreditTransactions;
                        $LeaveCreditTransactions->employee_id = $Employee->employee_id;
                        $LeaveCreditTransactions->branch_id = $Employee->branch_id;
                        $LeaveCreditTransactions->trn_credit_on = Carbon::now();
                        $LeaveCreditTransactions->trn_credit_days = $ClLeaveType->num_of_day;
                        $LeaveCreditTransactions->trn_remark = $LOG1;
                        $LeaveCreditTransactions->trn_leave_type_id = 1; // Casual leave
                        $LeaveCreditTransactions->year_id = $calanderYear->year_id;
                        $LeaveCreditTransactions->save();
                        unset($LeaveCreditTransactions);

                        // every year PL will carry over, so assgin fresh from master value
                        $LOG1 .= 'Yearly PL : '.$TODAY.PHP_EOL;
                        if($EmployeeLeaves->privilege_leave<45) {
                            $LOG .= PHP_EOL.'every year PL will carry over'.PHP_EOL;
                            $LOG .= 'employee_id: '.$Employee->employee_id.PHP_EOL;
                            $LOG .= 'emp_code: '.$Employee->emp_code.PHP_EOL;
                            $LOG .= '-----------'.PHP_EOL;
                            // LOP take and calc
                            // here we will take past year LOSS OF PAY days from attentance log absent status
                            $LOP_DAYS = EmployeeInOutDataCase::where('finger_print_id', $Employee->finger_id)->where('date', '>=', $calanderYear->year_start)->where('date', '<=', $calanderYear->year_end)->where('attendance_status', AttendanceStatus::$ABSENT)->count();
                            $LOG1 .= 'LOP_DAYS : '.$LOP_DAYS.PHP_EOL;
                            $SLAB = DB::table('privilege_leave_lop_slab')->select('pl_months')->whereRaw("$LOP_DAYS >= pl_lop_from AND $LOP_DAYS <= pl_lop_to")->first();
                            $pl_months = ($SLAB->pl_months ?? '');
                            $PlCreditAfterOneYear = round($PlLeaveType->num_of_day * 12 / 12);
                            if($pl_months==0) {
                                $LOG1 .= 'No credits PL, over LOP ' . $LOP_DAYS. ', LOP SLAB=' . $pl_months . PHP_EOL;
                                $PlCreditAfterOneYear = 0;
                            } else if($pl_months>0) {
                                $PlCreditAfterOneYear = round($PlLeaveType->num_of_day * $pl_months / 12);
                                $LOG1 .= 'Master PL value = ' . $PlLeaveType->num_of_day. ', LOP SLAB=' . $pl_months . PHP_EOL;
                                $LOG1 .= 'Credited PL = month slab based ' . $PlCreditAfterOneYear . PHP_EOL;
                            }
                            
                            $EmployeeLeaves->privilege_leave += $PlCreditAfterOneYear;

                            // if more then 45 then set max limit 45 as per levave policy
                            if($EmployeeLeaves->privilege_leave>45) {
                                $LOG1 .= '>45 so elapsed PL = ' . $EmployeeLeaves->privilege_leave . ' - 45 = ' . $elapsedSl . PHP_EOL;
                                $elapsedSl = $EmployeeLeaves->privilege_leave - 45;
                                $EmployeeLeaves->privilege_leave = 45;
                                $LOG1 .= 'Set PL = set max 45' . PHP_EOL;
                            }
                        } else {
                            $LOG1 .= 'Not PL Credit because already have '.$EmployeeLeaves->privilege_leave.PHP_EOL;
                            $LOG .= PHP_EOL.'Not PL Credit because already have '.$EmployeeLeaves->privilege_leave.PHP_EOL;
                        }
                        $LOG .= __LINE__ . ': leave_id='.$EmployeeLeaves->leave_id.PHP_EOL;
                        if($EmployeeLeaves->leave_id) {
                            $EmployeeLeaves->update();
                        } else {
                            $EmployeeLeaves->save();
                        }
                        $LOG .= $LOG1.PHP_EOL.PHP_EOL;
                    }
                }
                // end of perform permanent employee credits every year

                // perform first PROBATOIN end status set permanent_status = 1;
                foreach ($EmployeeList as $Employee) {
                    // PROBATOIN end to next calendar end months
                    $PROBATOIN_END_DATE = addMonth($Employee->date_of_joining, $SalaryRepository->PROBATOIN_MONTHS);
                    // echo 'Code: '.$Employee->emp_code.', PROBATOIN_END_DATE='.$PROBATOIN_END_DATE.', TODAY='.$TODAY.'<br>';
                    if($PROBATOIN_END_DATE==$TODAY) {
                        $EmployeeLeaves = $Employee->EmployeeLeaves;
                        if(!$EmployeeLeaves) {
                            $EmployeeLeaves = new EmployeeLeaves;
                            $EmployeeLeaves->employee_id = $Employee->employee_id;
                            $EmployeeLeaves->branch_id = $Employee->branch_id;
                        }
                        $LOG .= 'PROBATOIN END DATE: '.$PROBATOIN_END_DATE.PHP_EOL;
                        $yearEnd = date("Y-m-d", strtotime("Last day of December", strtotime($PROBATOIN_END_DATE)));
                        $monthDiffs = monthDiffs($yearEnd, $PROBATOIN_END_DATE) - 1;
                        $LOG .= 'yearEnd: '.$yearEnd.PHP_EOL;
                        $LOG .= 'monthDiffs: '.$monthDiffs.PHP_EOL;
                        
                        // Sick leave formula pro-rata based
                        $SlCreditCalendarYear = round((($SalaryRepository->SICK_LEAVE_PER_YEAR/12) * $monthDiffs));
                        
                        // Casual leave formula pro-rata based
                        $ClCreditAfterProbation = round((($SalaryRepository->CASUAL_LEAVE_PER_YEAR/12) * ($monthDiffs)));

                        $EmployeeLeaves->sick_leave += $SlCreditCalendarYear;

                        $EmployeeLeaves->casual_leave = $ClCreditAfterProbation;

                        $LeaveCreditTransactions = new LeaveCreditTransactions;
                        $LeaveCreditTransactions->employee_id = $Employee->employee_id;
                        $LeaveCreditTransactions->branch_id = $Employee->branch_id;
                        $LeaveCreditTransactions->trn_credit_on = Carbon::now();
                        $LeaveCreditTransactions->trn_credit_days = $SlCreditCalendarYear;
                        $LeaveCreditTransactions->trn_remark = $LOG;
                        $LeaveCreditTransactions->trn_leave_type_id = 2; // Sick leave
                        $LeaveCreditTransactions->year_id = $calanderYear->year_id;
                        $LeaveCreditTransactions->save();

                        $LeaveCreditTransactions = new LeaveCreditTransactions;
                        $LeaveCreditTransactions->employee_id = $Employee->employee_id;
                        $LeaveCreditTransactions->branch_id = $Employee->branch_id;
                        $LeaveCreditTransactions->trn_credit_on = Carbon::now();
                        $LeaveCreditTransactions->trn_credit_days = $ClCreditAfterProbation;
                        $LeaveCreditTransactions->trn_remark = $LOG;
                        $LeaveCreditTransactions->trn_leave_type_id = 1; // Casual leave
                        $LeaveCreditTransactions->year_id = $calanderYear->year_id;
                        $LeaveCreditTransactions->save();
                        $LOG .= __LINE__ . ': leave_id='.$EmployeeLeaves->leave_id.PHP_EOL;
                        if($EmployeeLeaves->leave_id) {
                            $EmployeeLeaves->update();
                        } else {
                            $EmployeeLeaves->save();
                        }

                        $Employee->permanent_status = 1;
                        $Employee->update();
                    }
                } // end first loop $EmployeeList update permanent_status based

                // permanent_status=0 re-fetch after PROBATOIN end updated records
                $EmployeeList = Employee::where('permanent_status', 0)->orderBy('date_of_joining', 'ASC')->get();
                foreach ($EmployeeList as $Employee) {
                    $PROBATOIN_END_DATE2 = addMonth($Employee->date_of_joining, $SalaryRepository->PROBATOIN_MONTHS);
                    $CREDIT_FLAG = '';
                    $EmployeeLeaves = $Employee->EmployeeLeaves;
                    $monthDiffs = monthDiffs($TODAY, $Employee->date_of_joining);
                    $FIRST_DAY_THIS_MONTH = '';
                    if($TODAY) {
                        $TODAY_PART = explode('-', $TODAY);
                        if(isset($TODAY_PART[0]) && isset($TODAY_PART[1]) && isset($TODAY_PART[1])) {
                            $FIRST_DAY_THIS_MONTH = $TODAY_PART[0].'-'.$TODAY_PART[1].'-01';
                        }
                    }
                    
                    if(!$EmployeeLeaves) {
                        $EmployeeLeaves = new EmployeeLeaves;
                        $EmployeeLeaves->employee_id = $Employee->employee_id;
                        $EmployeeLeaves->branch_id = $Employee->branch_id;
                    }
                    $DOJ_DAY = (int) date('d', strtotime($Employee->date_of_joining));
                    $DOJ_MON = (int) date('m', strtotime($Employee->date_of_joining));
                    
                    $TODAY_DAY = (int) date('d', strtotime($TODAY));
                    $TODAY_MON = (int) date('m', strtotime($TODAY));
                    // if DOJ first date join (01/mm/YYYY) credit of Sick Leave next date (02/mm/YYYY)
                    if($DOJ_DAY==1 && $TODAY_DAY==2 && $EmployeeLeaves->sick_leave==0) {
                        if($TODAY_MON==$DOJ_MON) {
                            $CREDIT_FLAG .= 'FIRST DAY JOIN MONTH: 1'.PHP_EOL;
                            $EmployeeLeaves->sick_leave             = 1;
                            $cron_changes++;
                        }
                    } else if($DOJ_DAY<=15 && $EmployeeLeaves->sick_leave==0) {
                        // semi month before join first credit of Sick Leave
                        if($TODAY_MON==$DOJ_MON && $TODAY_DAY>$DOJ_DAY ) {
                            $CREDIT_FLAG .= 'SEMI MONTH: 1'.PHP_EOL;
                            $EmployeeLeaves->sick_leave             = 1;
                            $cron_changes++;
                        }
                    } else  if($FIRST_DAY_THIS_MONTH==$TODAY && $monthDiffs<=6) {
                        // every month first date till PROBATION PERIOD
                        $CREDIT_FLAG .= 'EVERY FIRST DAY OF MONTH: +1'.PHP_EOL;
                        $EmployeeLeaves->sick_leave++;
                        $cron_changes++;
                    }
                    // update end PROBATOIN flag in employee
                    if($CREDIT_FLAG) {
                        $LeaveCreditTransactions = new LeaveCreditTransactions;
                        $LeaveCreditTransactions->employee_id = $Employee->employee_id;
                        $LeaveCreditTransactions->branch_id = $Employee->branch_id;
                        $LeaveCreditTransactions->trn_credit_on = Carbon::now();
                        $LeaveCreditTransactions->trn_credit_days = 1;
                        $LeaveCreditTransactions->trn_remark = $LOG.PHP_EOL.'sick_leave: '.$EmployeeLeaves->sick_leave.PHP_EOL;
                        $LeaveCreditTransactions->trn_leave_type_id = 2; // Sick leave
                        $LeaveCreditTransactions->year_id = $calanderYear->year_id;
                        $LeaveCreditTransactions->save();
                        $LOG .= __LINE__ . ': leave_id='.$EmployeeLeaves->leave_id.PHP_EOL;
                        if($EmployeeLeaves->leave_id) {
                            $EmployeeLeaves->update();
                        } else {
                            $EmployeeLeaves->save();
                        }
                        $LOG .= 'Emp Code: ' . $Employee->emp_code.PHP_EOL;
                        $LOG .= 'DOJ: ' . dateConvertDBtoForm($Employee->date_of_joining).PHP_EOL;
                        $LOG .= 'DOJ_DAY: '.$DOJ_DAY.PHP_EOL;
                        $LOG .= 'monthDiffs: '.$monthDiffs.PHP_EOL;
                        $LOG .= 'PROBATOIN_END_DATE: '.$PROBATOIN_END_DATE.PHP_EOL;
                        $LOG .= $CREDIT_FLAG . PHP_EOL;
                    }
                } // end for loop $EmployeeList probatoon duration
                
            } else {
                $LOG .= 'DATE NO MATCH'.PHP_EOL;
            }
            $LeaveCreditSetting = new \App\Model\LeaveCreditSetting;
            $LeaveCreditSetting->cron_log = $LOG;
            $LeaveCreditSetting->cron_executed_on = $TODAY;
            $LeaveCreditSetting->cron_changes = $cron_changes;
            Log::info('Ends LeaveCredit ' . __FUNCTION__.'()');
            Log::info($LOG);
            if($TODAY != $cron_executed_on) {
                $LeaveCreditSetting->save();
            }
            DB::commit();
        } catch (\Exception $e) {
            if(session('logged_session_data.employee_id')) {
                echo ('<pre>LOG='.$LOG.'</pre>');
                $tt = debugBackLog(0, 30);
                echo $tt;
                dd('An error occurred: ' . $e->getMessage());
            }
            Log::info('An error occurred: ' . $e->getMessage());
            DB::rollback();
        } // ends try catch
        
        return $LOG;
    } // ends handle

    public function leavesAndPremissionExpired() {
        // this mehtod is update the leave, RH and Permission apllication set expired status if admin, hr or head they have no action done (approved or rejected)
        // apllication set expired status if salary payroll data is freezed then only can set expired!
        $LOG = '';
        info(__FUNCTION__.' starts');
        $totalLeave=0;
        $totalRH=0;
        $totalPermission=0;
        // 1st leave appplications
        $LeaveApplicationMonths = DB::table('leave_application')->selectRaw('leave_application_id, application_from_date')
            ->where('status', LeaveStatus::$PENDING)
            ->get();
        foreach ($LeaveApplicationMonths as $key => $LeaveApplication) {
            $LeaveApplication = LeaveApplicationCase::find($LeaveApplication->leave_application_id);
            $LOG .= 'Leave From Date = '. $LeaveApplication->application_from_date . PHP_EOL;
            $leave_date = $LeaveApplication->application_from_date;
            $sdate = new \DateTime( $leave_date );
            $edate = new \DateTime( $leave_date );
            
            $leave_day = $sdate->format('d');
            $leave_month_last_day = $sdate->format('t');
            $LEAVE_LOG = '';
            $sdate = $sdate->modify('previous month');
            if($leave_day>=SalaryRepository::PAYROLL_START_DATE) {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->modify('next month');
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
                $LEAVE_LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
            } else {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
                $LEAVE_LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
            }
            $checkFreeze = PayrollUpload::where('salary_key', $salary_month_key)->where('salary_freeze', 1)->count();
            $previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
            $current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
            $LOG .= 'leave_day='. $leave_day.', leave_month_last_day='.$leave_month_last_day.PHP_EOL;
            $LEAVE_LOG .= 'leave_day='. $leave_day.', leave_month_last_day='.$leave_month_last_day.PHP_EOL;
            $LEAVE_LOG .= ($checkFreeze ? 'Set Expired! (leave_application_id='.$LeaveApplication->leave_application_id.')' : 'Still Valid Application') . PHP_EOL;
            if($checkFreeze) {
                $LeaveApplication->remarks = 'Leave status updated by expired status by system.';
                $LeaveApplication->status = LeaveStatus::$CANCEL;
                $LeaveApplication->update();
                $totalLeave++;
                info('LEAVE_LOG='.$LEAVE_LOG);
            }
        }

        // 2nd RH appplications
        $RhApplicationMonths = DB::table('restricted_holiday_application')->selectRaw('rh_application_id, holiday_date')
            ->where('status', LeaveStatus::$PENDING)
            ->get();
        foreach ($RhApplicationMonths as $key => $RhApplication) {
            $RhApplication = RhApplicationCase::find($RhApplication->rh_application_id);
            $LOG .= 'RH Leave Date = '. $RhApplication->holiday_date . PHP_EOL;
            $leave_date = $RhApplication->holiday_date;
            $sdate = new \DateTime( $leave_date );
            $edate = new \DateTime( $leave_date );
            $RH_LOG = '';
            $leave_day = $sdate->format('d');
            $leave_month_last_day = $sdate->format('t');
            $sdate = $sdate->modify('previous month');
            if($leave_day>=SalaryRepository::PAYROLL_START_DATE) {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->modify('next month');
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
                $RH_LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
            } else {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
                $RH_LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
            }
            $checkFreeze = PayrollUpload::where('salary_key', $salary_month_key)->where('salary_freeze', 1)->count();
            $previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
            $current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
            $RH_LOG .= 'leave_day='. $leave_day.', leave_month_last_day='.$leave_month_last_day.PHP_EOL;
            $RH_LOG .= ($checkFreeze ? 'Set Expired! (rh_application_id='.$RhApplication->rh_application_id.')' : 'Still Valid Application') . PHP_EOL;
            if($checkFreeze) {
                $RhApplication->remarks = 'RH Leave status updated by expired status by system.';
                $RhApplication->status = LeaveStatus::$CANCEL;
                $RhApplication->update();
                $totalRH++;
                info('RH_LOG='.$RH_LOG);
            }
        }

        // 3rd Permission appplications
        $LeavePermissionMonths = DB::table('leave_permission')->selectRaw('leave_permission_id, leave_permission_date')
            ->where('status', LeaveStatus::$PENDING)
            ->get();
        foreach ($LeavePermissionMonths as $key => $LeavePermission) {
            $LeavePermission = LeavePermissionCase::find($LeavePermission->leave_permission_id);
            $LOG .= 'Permission Date = '. $LeavePermission->leave_permission_date . PHP_EOL;
            $leave_date = $LeavePermission->leave_permission_date;
            $sdate = new \DateTime( $leave_date );
            $edate = new \DateTime( $leave_date );
            $PERMISSION_LOG = '';
            $leave_day = $sdate->format('d');
            $leave_month_last_day = $sdate->format('t');
            $sdate = $sdate->modify('previous month');
            if($leave_day>=SalaryRepository::PAYROLL_START_DATE) {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->modify('next month');
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
                $PERMISSION_LOG .= 'next salary_month_key='. $salary_month_key.PHP_EOL;
            } else {
                $salary_month_key = new \DateTime( $leave_date );
                $salary_month_key = $salary_month_key->format('Y-m-01');
                $LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
                $PERMISSION_LOG .= 'current salary_month_key='. $salary_month_key.PHP_EOL;
            }
            $checkFreeze = PayrollUpload::where('salary_key', $salary_month_key)->where('salary_freeze', 1)->count();
            $previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
            $current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
            $PERMISSION_LOG .= 'leave_day='. $leave_day.', leave_month_last_day='.$leave_month_last_day.PHP_EOL;
            $PERMISSION_LOG .= ($checkFreeze ? 'Set Expired! (leave_permission_id='.$LeavePermission->leave_permission_id.')' : 'Still Valid Application') . PHP_EOL;
            if($checkFreeze) {
                $LeavePermission->head_remarks = 'Permission status updated by expired status by system.';
                $LeavePermission->status = LeaveStatus::$CANCEL;
                $LeavePermission->update();
                $totalPermission++;
                info('PERMISSION_LOG='.$PERMISSION_LOG);
            }
        }

        if($totalLeave || $totalRH || $totalPermission) {
            $FINAL_LOG = 'Processed status for:'. PHP_EOL;
            $FINAL_LOG .= 'totalLeave='. $totalLeave.PHP_EOL;
            $FINAL_LOG .= 'totalRH='. $totalRH.PHP_EOL;
            $FINAL_LOG .= 'totalPermission='. $totalPermission.PHP_EOL;
            $FINAL_LOG .= PHP_EOL;
            info('FINAL_LOG = ' . $FINAL_LOG);
        }

        info(__FUNCTION__.' ends');
    }
} // ends LeaveCredit
