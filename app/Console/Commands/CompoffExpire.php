<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Model\CompOff;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use App\Model\EmployeeLeaves;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\LeaveCreditTransactions;
use App\Repositories\SalaryRepository;

class CompoffExpire extends Command
{

    protected $signature = 'compoff:expire {--today=false}';


    protected $description = 'Employee comp off expire updates background jobs';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        Log::info('Starts LeaveCCompoffExpireredit ' . __FUNCTION__);
        set_time_limit(0);
        $LOG = '';
        $TODAY = $this->option('today');
        if(!$TODAY || $TODAY=='false' || $TODAY===false) {
            $TODAY = date('Y-m-d');
        }
        
        $calanderYear = calanderYear::currentYear();
        if(!$calanderYear || !$calanderYear->year_id) {
            $info = 'calanderYear master record not found, please first run calanderYear after few minutes only can run this credit process.';
            info($info);
            dd($info);
            return false;
        }
        
        DB::beginTransaction();
        try {
            $LOG .= 'Executed Data: '.$TODAY.PHP_EOL;
            $CompOffAll = CompOff::where('expire_date', '<=', $TODAY)->groupBy('employee_id')->get();
            $LOG .= 'Expired CompOff : '.count($CompOffAll).PHP_EOL;
            foreach ($CompOffAll as $key => $CompOffEach) {
                $LOG .= 'Code : '.$CompOffEach->employee->finger_id .PHP_EOL;
                $CompOffEachAll = CompOff::where('expire_date', '<=', $TODAY)->where('employee_id', $CompOffEach->employee_id)->get();
                foreach ($CompOffEachAll as $key => $CompOff) {
                    $CompOff->status = 2;
                    $CompOff->update();
                    $LOG .= 'Date : '.$CompOff->working_date.' expire updated'.PHP_EOL;
                }
            }
        } catch (\Exception $e) {
            if(session('logged_session_data.employee_id')) {
                echo ('<pre>LOG='.$LOG.'</pre>');
                $tt = debugBackLog(0, 30);
                echo $tt;
                info('$LOG='.$LOG.', $tt='.$tt);
            }
            Log::error(__FILE__.':'.__LINE__.' An error occurred: ' . $e->getMessage());
            DB::rollback();
        } // ends try catch
        info('$LOG');
        info($LOG);
        DB::commit();
        return $LOG;
    } // ends handle
} // ends CompoffExpire
