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
Schedule::command("requests:useragents")->everyFiveMinutes();
Schedule::command("logs:gather")->everyMinute();
Schedule::command("logs:truncate")->daily()->onOneServer();
Schedule::command("spam:load")->everyMinute();
Schedule::command("load:affiliate-blacklist")->everyMinute();
Schedule::command("affilliates:store")->everyMinute()->onOneServer();
Schedule::call(function () {
    DB::table('monthlyrequests')->truncate();
    DB::disconnect('mysql');
})->monthlyOn(1, '00:00');
Schedule::command('queue:work --queue=donations --stop-when-empty');
Schedule::command('queue:work --queue=general --stop-when-empty');

// Membership Commands
Schedule::command('membership:paypal-payments')->hourly()->onOneServer();
Schedule::command('membership:payment-reminder')->cron("51 6-23 * * *")->onOneServer();
Schedule::command('membership:notify-admin')->dailyAt("06:00")->onOneServer();
Schedule::command('membership:membership:notify-unfinished')->hourly()->onOneServer();

// Logs Commands
Schedule::command('logs:create-order')->onOneServer()->dailyAt("06:00");
Schedule::command('logs:create-invoice')->onOneServer()->dailyAt("07:00");