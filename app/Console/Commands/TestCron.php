<?php

namespace App\Console\Commands;

use App\Model\RhApplication;
use App\Model\LeaveApplication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';
    protected $name      = "test-cron";

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
    public function handle()
    {

        Log::info("Cron is working fine! " . __FILE__);
        return true;
        try {
            DB::beginTransaction();
            Log::info("Cron is working fine!");
            $date = date('Y-m-d');
            $rhList = RhApplication::where('holiday_date', $date)
                ->where('status', 2)
                ->select('holiday_date', 'employee_id')
                ->get();

            $leaveData = [];
            foreach ($rhList as $value) {
                $leavedate = date('Y-m-d', strtotime($value->holiday_date . '+ 1 days'));
                $leaveData[] = LeaveApplication::where('employee_id', $value->employee_id)
                    ->whereRaw('application_from_date <= "' . $leavedate . '" AND application_to_date >= "' . $leavedate . '" ')
                    ->where('status', 2)
                    ->first();
            }

            if (!empty($leaveData)) {
                foreach ($leaveData as  $leave) {
                    $cancelRhApplication = RhApplication::where('employee_id', $leave->employee_id)
                        ->where('holiday_date', $date)
                        ->where('status', 2)
                        ->first();

                    if ($cancelRhApplication) {
                        $cancelRhApplication->status = 4;
                        $cancelRhApplication->update();
                        Log::info('RhApplication Cancelled for EmployeeId:' . $leave->employee_id);
                    } else {
                        Log::info('No matching RhApplication found for EmployeeId:' . $leave->employee_id);
                    }
                }
            } else {
                Log::info('No leave data available.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('An error occurred: ' . $e->getMessage());
        }
    }
}
