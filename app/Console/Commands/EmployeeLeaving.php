<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EmployeeLeaving extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:resigned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change employee status based on date of leaving as (Resigned)';

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
        DB::table('employee')->where('date_of_leaving', '>=', date('Y-m-d'))->insert(['permanent_status' => 3, 'status' => 2]);
    }
}
