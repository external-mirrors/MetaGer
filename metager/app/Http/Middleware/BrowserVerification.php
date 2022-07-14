<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\QueryTimer;
use Cache;
use App\Models\HumanVerification;
use App\SearchSettings;

class BrowserVerification
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $route = "resultpage")
    {
        \app()->make(QueryTimer::class)->observeStart(self::class);

        $this->verification_key = \hash("sha512", $request->ip() . $_SERVER["AGENT"]);

        $bvEnabled = config("metager.metager.browserverification_enabled");
        if (empty($bvEnabled) || !$bvEnabled) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        } else {
            $whitelist = config("metager.metager.browserverification_whitelist");
            $agent = new Agent();
            foreach ($whitelist as $browser) {
                if ($agent->match($browser)) {
                    \app()->make(QueryTimer::class)->observeEnd(self::class);
                    return $next($request);
                }
            }
        }

        if (($request->input("out", "") === "api" || $request->input("out", "") === "atom10") && app('App\Models\Key')->getStatus()) {
            header('Content-type: application/xml; charset=utf-8');
        } elseif (($request->input("out", "") === "api" || $request->input("out", "") === "atom10") && !app('App\Models\Key')->getStatus()) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            abort(403);
        } else {
            header('Content-type: text/html; charset=utf-8');
        }
        header('X-Accel-Buffering: no');

        //use parameter for middleware to skip this when using associator
        if (($request->filled("loadMore") && Cache::has($request->input("loadMore"))) || app('App\Models\Key')->getStatus()) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        }

        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('output_handler', '');
        ob_end_clean();

        $mgv = $request->input('mgv', "");
        if (!empty($mgv)) {
            // Verify that key is a md5 checksum
            if (!preg_match("/^[a-f0-9]{32}$/", $mgv)) {
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                abort(404);
            }
            $bvData = null;
            $start_time = now();
            $wait_time_seconds = 5;
            do {
                $bvData = Cache::get($mgv);
                if ($bvData !== null) {
                    if ((array_key_exists("css_loaded", $bvData) && $bvData["css_loaded"] === true) &&
                        (array_key_exists("js_loaded", $bvData) && $bvData["js_loaded"] === true) &&
                        (array_key_exists("css_image_loaded", $bvData) && $bvData["css_image_loaded"] === true)
                    ) {
                        break;
                    } else {
                        $wait_time_seconds = 2;
                    }
                }
                \usleep(50 * 1000);
            } while (now()->diffInSeconds($start_time) < $wait_time_seconds);
            if ($bvData !== null) {
                // ToDo Remove Log
                $file_path = \storage_path("logs/metager/bv.csv");
                $fh = fopen($file_path, "a");
                try {
                    \fputcsv($fh, [now()->format("Y-m-d H:i:s"), $_SERVER["AGENT"], $request->input("eingabe", "")]);
                } finally {
                    fclose($fh);
                }
                $search_settings = \app()->make(SearchSettings::class);
                $search_settings->jskey = $mgv;
                $search_settings->header_printed = false;
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                return $next($request);
            } else {
                # We are serving that request but after solving a captcha
                self::logBrowserverification($request);
                \app()->make(HumanVerification::class)->lockUser();
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                return $next($request);
            }
        }

        $key = md5($request->ip() . microtime(true));

        echo (view('layouts.resultpage.verificationHeader')->with('key', $key)->render());
        flush();

        $bvData = null;
        $start_time = now();
        $wait_time_seconds = 2;
        do {
            $bvData = Cache::get($key);
            if ($bvData !== null) {
                echo (view('layouts.resultpage.resources')->render());
                flush();
                $search_settings = \app()->make(SearchSettings::class);
                $search_settings->jskey = $key;
                $search_settings->header_printed = true;
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                return $next($request);
            }
        } while (now()->diffInSeconds($start_time) < $wait_time_seconds);

        $params = $request->all();
        $params["mgv"] = $key;
        $url = route($route, $params);

        echo (view('layouts.resultpage.unverifiedResultPage')
            ->with('url', $url)
            ->render());
        flush();
        \app()->make(QueryTimer::class)->observeEnd(self::class);
    }

    public static function logBrowserverification(Request $request)
    {
        $fail2banEnabled = config("metager.metager.fail2ban.enabled");
        if (empty($fail2banEnabled) || !$fail2banEnabled || !config("metager.metager.fail2ban.url") || !config("metager.metager.fail2ban.user") || !config("metager.metager.fail2ban.password")) {
            return;
        }

        // Submit fetch job to worker
        $mission = [
            "resulthash" => "captcha",
            "url" => config("metager.metager.fail2ban.url") . "/browserverification/",
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => config("metager.metager.fail2ban.user"),
            "password" => config("metager.metager.fail2ban.password"),
            "headers" => [
                "ip" => $request->ip()
            ],
            "cacheDuration" => 0,
            "name" => "Captcha",
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }
}
