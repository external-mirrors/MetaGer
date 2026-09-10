<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Schedule::command("heartbeat")->everyMinute();
Schedule::command("requests:gather")->everyFifteenMinutes();
Schedule::command("logs:gather")->everyMinute();
// The other half of a search that no longer waits for the keyserver: the
// charge is written to Redis while the user is served and paid from here.
// See App\Console\Commands\SettleKeyDischarges.
//
// In the background because this one talks to a network service and the rest
// of this file shares a process with the scheduler's own liveness probe;
// without overlapping because a keyserver that answers slowly must not leave
// two of these racing over the same queue.
Schedule::command("keys:settle-discharges")->everyMinute()->runInBackground()->withoutOverlapping();
Schedule::command("logs:truncate")->daily()->onOneServer();
Schedule::call(function () {
    DB::table('monthlyrequests')->truncate();
    DB::disconnect('mysql');
})->monthlyOn(1, '00:00');

// Membership Commands
Schedule::command('membership:paypal-payments')->hourly()->onOneServer();
Schedule::command('membership:payment-reminder')->cron("0 6-23 * * *")->onOneServer();
Schedule::command('membership:notify-admin')->dailyAt("06:00")->onOneServer();
Schedule::command('membership:notify-unfinished')->hourly()->onOneServer();

// Logs Commands
Schedule::command('logs:create-order')->onOneServer()->dailyAt("06:00");
Schedule::command('logs:create-invoice')->onOneServer()->dailyAt("07:00");