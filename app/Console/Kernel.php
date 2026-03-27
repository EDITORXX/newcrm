<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync Google Sheets every minute (command checks intervals internally)
        $schedule->command('google-sheets:sync')->everyMinute();
        
        // Move overdue calls to pending
        $schedule->command('telecaller:move-overdue-to-pending')->everyMinute();
        
        // Send 10-minute reminder notifications
        $schedule->command('telecaller:send-reminder-notifications')->everyMinute();
        
        // Send follow-up reminder notifications every 15 minutes
        $schedule->command('notifications:followup-reminders')->everyFifteenMinutes();

        // Process ASM fresh lead CNP automation every 5 minutes
        $schedule->command('asm-cnp:process')->everyFiveMinutes();
        
        // Reset daily limits at midnight
        $schedule->job(new \App\Jobs\ResetDailyLimitsJob)->dailyAt('00:00');
        
        // Auto-assign unassigned leads after reset (at 00:05)
        $schedule->job(new \App\Jobs\AutoAssignUnassignedLeadsJob)->dailyAt('00:05');
        
        // Generate recurring tasks daily at 1 AM
        $schedule->command('tasks:generate-recurring')->dailyAt('01:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
