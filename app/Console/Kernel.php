<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ADD THIS LINE TO SCHEDULE YOUR AUTOMATED CRON JOB EVERY DAY:
        $schedule->command('credit:process-notes')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // This auto-registers your manually created ProcessSellerCreditNote.php file
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
