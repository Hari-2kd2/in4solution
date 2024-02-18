<?php

namespace App\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Http\Controllers\Attendance\GenerateReportController;

class ReportCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report {--today=true}';
    protected $name = "report";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this to create attendacne report';

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
    public function handle()
    {
        $time_start = microtime(true);
        info('Please wait report generation in progress started on '. Carbon::now());

        $TODAY = $this->option('today'); // this is option to run specific date in command params
        if(!$TODAY || $TODAY=='false' || $TODAY===false) {
            $TODAY = date('Y-m-d',strtotime("-1 days")); // mean generate report to yesterday date
        }

        $controller = new GenerateReportController();
        $controller->generateAttendanceReport($TODAY);
        
        $time_end = microtime(true);
        $execution_time_in_seconds = ($time_end - $time_start) . ' Seconds';
        info('Execution_time_in_seconds: ' . $execution_time_in_seconds . ' Ends at: '. Carbon::now());

    }
}
