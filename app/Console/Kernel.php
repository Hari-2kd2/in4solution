<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;
use App\Console\Commands\ReportCron;
use App\Console\Commands\LeaveCredit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        try {

            // Every year calender year reference record auto generate
            // $schedule->command('calander:cron')->yearly()->withoutOverlapping()->runInBackground();
            
            // Daily attendance auto generate
            // $schedule->command('report')->daily()->withoutOverlapping()->runInBackground();
            // $schedule->command('report')->daily()->withoutOverlapping()->runInBackground();
            
            // leave credits policy updates
            // $schedule->command('leaves:credit')->daily()->withoutOverlapping()->runInBackground();
            
            // leave comp off expire updates
            // $schedule->command('compoff:expire')->daily()->withoutOverlapping()->runInBackground();

            info(date('Y-m-d H:i:s') . ' Cron Execution Time');
            $schedule->command('report')->everyFifteenMinutes()->withoutOverlapping()->runInBackground();

        } catch (\Throwable $th) {
            info($th->getMessage());
        }    
        
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
