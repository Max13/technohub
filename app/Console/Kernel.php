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
        // $schedule->command('inspire')->hourly();
        $schedule->command('hotspot:clear hs-students') // Todo: Set name in config file
                 ->daily()
                 ->runInBackground()
                 ->withoutOverlapping();

        $schedule->command('ebics:import', [
                    '2026-01-01',
                    today()->format('Y-m-d'),
                 ])
                 ->dailyAt('05:00')
                 ->weekdays()
                 ->runInBackground()
                 ->withoutOverlapping();

        $schedule->command('ypareo:sync:all')
                 ->dailyAt('08:00')
                 ->weekdays()
                 ->runInBackground()
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
