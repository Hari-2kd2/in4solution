<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Model\Employee;
use App\Model\LeaveType;
use App\Model\calanderYear;
use App\Model\EmployeeLeaves;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Model\LeaveCreditTransactions;
use App\Repositories\SalaryRepository;

class LeaveCancel extends Command
{

    protected $signature = 'leaves:cancel {--today=false}';


    protected $description = 'Employee leave cancel background jobs';


    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        set_time_limit(0);
        $LOG = '';
        $TODAY = $this->option('today');
        if(!$TODAY) {
            $TODAY = date('Y-m-d');
        }

        $calanderYear = calanderYear::currentYear();
        if(!$calanderYear || !$calanderYear->year_id) {
            // $calanderYear = calanderYear::currentYear(date('Y') - 1);
            info(__FILE__.' calanderYear master record not found, please first run calanderYear after few minutes only can run this credit process.');
            dd('calanderYear master record not found, please first run calanderYear after few minutes only can run this credit process.');
            return false;
        }
        DB::beginTransaction();
        try {
            $LOG .= 'Executed Data: '.$TODAY.PHP_EOL;

        } catch (\Exception $e) {

            DB::rollback();
        } // ends try catch
        DB::commit();
        return $LOG;
    } // ends handle
} // ends LeaveCredit
