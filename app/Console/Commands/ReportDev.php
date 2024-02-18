<?php

namespace App\Console\Commands;

use App\Http\Controllers\Attendance\GenerateReportController;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;

class ReportDev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:report {fdate} {tdate}';
    protected $name = "dev-report";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run this to create attendacne report with two params. format: yyyy-mm-dd';

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
        print_r('Please wait');
        echo "\n";
        // return true;

        $fdate = $this->argument('fdate');
        $tdate = $this->argument('tdate');

        $datePeriod = CarbonPeriod::create(dateConvertFormtoDB($fdate), dateConvertFormtoDB($tdate));

        foreach ($datePeriod as $date) {
            $date = $date->format('Y-m-d');
            $controller = new GenerateReportController();
            $controller->generateAttendanceReport($date);
        }
        /*
    Write your database logic we bellow:
    Item::create(['name'=>'hello new']);
     */
    }
}
