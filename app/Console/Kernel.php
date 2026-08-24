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
        // Scheduled automated credit notes processor
        $schedule->command('credit:process-notes')->dailyAt('01:00');
        
        // Auto-expire and cancel pending orders where seller has not responded within 72 hours (3 days)
        $schedule->command('orders:auto-expire-pending')->hourly();
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
