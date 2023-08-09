<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('heartbeat')->everyMinute();
        $schedule->command('requests:gather')->everyFifteenMinutes();
        $schedule->command('requests:useragents')->everyFiveMinutes();
        $schedule->command('logs:gather')->everyMinute();
        $schedule->command('logs:truncate')->daily()->onOneServer();
        $schedule->command('spam:load')->everyMinute();
        $schedule->command('load:affiliate-blacklist')->everyMinute();
        $schedule->command('affilliates:store')->everyMinute()
            ->onOneServer();
        $schedule->call(function () {
            DB::table('monthlyrequests')->truncate();
            DB::disconnect('mysql');
        })->monthlyOn(1, '00:00');
        $schedule->command('queue:work --queue=donations --stop-when-empty');
        $schedule->command('queue:work --queue=general --stop-when-empty');
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}