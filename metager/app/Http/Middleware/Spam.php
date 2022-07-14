<?php

namespace App\Http\Middleware;

use App\Models\HumanVerification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;

class Spam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (app('App\Models\Key')->getStatus()) {
            return $next($request);
        }
        # Check for recent Spams
        $eingabe = $request->input('eingabe');
        $spams = Redis::lrange("spam", 0, -1);

        $spam = false;

        foreach ($spams as $spam) {
            if (\preg_match("/" . $spam . "/", $eingabe)) {
                $spam = true;
                break;
            }
        }

        if ($spam === true) {
            $browser = new Agent();

            $browser->setUserAgent($_SERVER["AGENT"]);
            if ($browser->browser() === "Chrome" && $browser->version($browser->browser()) === "91.0.4472.77") {
                $this->logFail2Ban($request->ip());
            }
            // ToDo Remove Log
            $file_path = \storage_path("logs/metager/spam.csv");
            $fh = fopen($file_path, "a");
            try {

                $data = [
                    now()->format("Y-m-d H:i:s"),
                    $request->input("eingabe", ""),
                ];
                foreach ($request->header() as $key => $value) {
                    $data[] = $key . ":" . json_encode($value);
                }
                \fputcsv($fh, $data);
            } finally {
                fclose($fh);
            }

            /*
            $human_verification = \app()->make(HumanVerification::class);
            $human_verification->lockUser();
            $human_verification->setUnusedResultPage(50);
            $human_verification->setWhiteListed(false);
            */
        }

        return $next($request);
    }

    private function logFail2Ban($ip)
    {
        $fail2banEnabled = config("metager.metager.fail2ban.enabled");
        if (empty($fail2banEnabled) || !$fail2banEnabled || !config("metager.metager.fail2ban.url") || !config("metager.metager.fail2ban.user") || !config("metager.metager.fail2ban.password")) {
            return;
        }

        // Submit fetch job to worker
        $mission = [
            "resulthash" => "browserverification.ban",
            "url" => config("metager.metager.fail2ban.url") . "/spam/",
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => config("metager.metager.fail2ban.user"),
            "password" => config("metager.metager.fail2ban.password"),
            "headers" => [
                "ip" => $ip()
            ],
            "cacheDuration" => 0,
            "name" => "Captcha",
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }
}
