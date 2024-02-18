<?php

namespace App\Console\Commands;

use App\Model\calanderYear;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\View\EmployeeAttendaceController;

class CalanderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calander:cron';
    protected $name      = "calander-cron";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Cron';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        // thsi project 2023-01-01 calander year is Jan to Dec, we can change here whatever we need to start calander year
        $newYear = date('Y');
        $yearDayNumber = date('z');
        $checkCalanderDate = ($newYear-1) . '-01-01';
        $currentCalanderYear = calanderYear::where('year_start', $checkCalanderDate)->first();
        // close the current running year and start the next calander year
        if($yearDayNumber==0 && $currentCalanderYear && $currentCalanderYear->year_status==0) {
            DB::beginTransaction();
            $currentCalanderYear->year_status = 1;
            $currentCalanderYear->update();

            $calanderYear = new calanderYear;
            $calanderYear->year_name            = 'JAN '.$newYear.' - DEC '.$newYear;
            $calanderYear->year_start           = date('Y-m-d');
            $calanderYear->year_end             = $newYear . '-12-01'; // if calander year change month we should calculate here
            $calanderYear->year_request_before  = $newYear . '-12-29'; // if calander year change month we should calculate here
            $calanderYear->year_status          = 0;
            $calanderYear->save();
            
            DB::commit();
            info($calanderYear->year_id.' New calander created successfully.');
        }
    }
}
