<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;

class LogFailedAuthenticationAttempt
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Failed  $event
     * @return void
     */
    public function handle(Failed $event)
    {
        // Authentication failed Let's log the user

        $fail2banEnabled = config("metager.metager.fail2ban.enabled");
        if(empty($fail2banEnabled) || !$fail2banEnabled || !config("metager.metager.fail2ban.url") || !config("metager.metager.fail2ban.user") || !config("metager.metager.fail2ban.password")){
            return;
        }

        // Submit fetch job to worker
        $mission = [
                "resulthash" => "captcha",
                "url" => config("metager.metager.fail2ban.url") . "/mgadmin/",
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "username" => config("metager.metager.fail2ban.user"),
                "password" => config("metager.metager.fail2ban.password"),
                "headers" => [
                    "ip" => Request::ip()
                ],
                "cacheDuration" => 0,
                "name" => "Captcha",
            ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }
}
