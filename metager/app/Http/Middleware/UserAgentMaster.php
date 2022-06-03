<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use App\QueryTimer;
use Jenssegers\Agent\Agent;

class UserAgentMaster
{
    /**
     * This Middleware takes the User-Agent of the user and saves it into a Database
     * It will also take a random User Agent of a matching device and replace the current User-Agent with it.
     */
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \app()->make(QueryTimer::class)->observeStart(self::class);
        /**
         * Categorize the User-Agents by
         * 1. Platform (i.e. Ubuntu)
         * 2. Browser (i.e. Firefox)
         * 3. Device (desktop|tablet|mobile)
         */
        $agent = new Agent();
        $device = "desktop";
        if ($agent->isTablet()) {
            $device = "tablet";
        } else if ($agent->isPhone()) {
            $device = "mobile";
        }

        $newAgent = null;

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            // Push an entry to a list in Redis
            // App\Console\Commands\SaveUseragents.php is called regulary to save the list into a sqlite database
            Redis::rpush('useragents', json_encode(["platform" => $agent->platform(), "browser" => $agent->browser(), "device" => $device, "useragent" => $_SERVER['HTTP_USER_AGENT']]));
            Redis::expire('useragents', 301);

            // Try to retrieve a random User-Agent of the same category from the sqlite database
            $newAgent = \App\UserAgent::where("platform", $agent->platform())
                ->where("browser", $agent->browser())
                ->where("device", $device)
                ->inRandomOrder()
                ->limit(1)
                ->get();
        } else {
            // Probably no Useragent provided lets use a random one
            // Try to retrieve a random User-Agent of the same category from the sqlite database
            $newAgent = \App\UserAgent::where("platform", $agent->platform())
                ->where("device", $device)
                ->inRandomOrder()
                ->limit(1)
                ->get();
        }


        if (sizeof($newAgent) >= 1) {
            // If there is an Entry in the database use it
            $newAgent = $newAgent[0]->useragent;
        } elseif (!empty($_SERVER['HTTP_USER_AGENT'])) {
            // Else anonymize the version of the current User-Agent
            $agentPieces = explode(" ", $_SERVER['HTTP_USER_AGENT']);
            for ($i = 0; $i < count($agentPieces); $i++) {
                $agentPieces[$i] = preg_replace("/(\d+\.\d+)/s", "0.0", $agentPieces[$i]);
                $agentPieces[$i] = preg_replace("/([^\/]*)\/\w+/s", "$1/0.0", $agentPieces[$i]);
            }
            $newAgent = implode(" ", $agentPieces);
        } else {
            $newAgent = '';
        }
        // Replace the User-Agent
        $_SERVER['HTTP_USER_AGENT'] = $newAgent;

        \app()->make(QueryTimer::class)->observeEnd(self::class);
        return $next($request);
    }
}
