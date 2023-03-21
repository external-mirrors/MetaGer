<?php

namespace App\Http\Middleware;

use App\Http\Controllers\HumanVerification;
use App\Models\Authorization\Authorization;
use App\QueryTimer;
use App\SearchSettings;
use Cache;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;

class BrowserVerification
{
    const LOG_KEY = "bv_logs";

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

        if ($request->filled("out")) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            if (app(Authorization::class)->canDoAuthenticatedSearch()) {
                return $next($request);
            } else {
                self::logBrowserverification($request);
                abort(403);
            }
        }

        //use parameter for middleware to skip this when using associator
        if (
            $request->filled("loadMore") || app(Authorization::class)->canDoAuthenticatedSearch() ||
            ($request->filled("key") && $request->input('key') === config("metager.metager.keys.uni_mainz"))
        ) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        }

        $mgv = $request->input("mgv", "");
        if (preg_match("/^[a-z0-9]{32}$/", $mgv) !== 1) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            self::logBrowserverification($request);
            abort(401);
        }

        if (Cache::has($mgv)) {

            $bvData = Cache::get($mgv);
            if ($bvData === null) {
                // Key does not exist if this is called in a frame abort. if not redirect to the page without mgv parameter
                // Check if request header "Sec-Fetch-Dest" is set
                $framed = false;
                if ($request->header("Sec-Fetch-Dest") === "iframe") {
                    $framed = true;
                } elseif ($request->input("iframe", "0") === "1") {
                    $framed = true;
                }
                if ($framed) {
                    self::logBrowserverification($request);
                    $params = $request->except(["mgv", "iframe"]);
                    return response(view("errors/410", ["refresh" => route($route, $params)]));
                } else {
                    $params = $request->all();
                    unset($params["mgv"]);
                    return redirect(route($route, $params));
                }
            }

            // The css key has to be present in order to continue
            if (!array_key_exists("css", $bvData)) {
                if (sizeof($bvData["tries"]) < 5) {
                    $time_since_last_try = now()->diffInMilliseconds($bvData["tries"][sizeof($bvData["tries"]) - 1]);
                    // Redirect the user to make him refresh (up to 5 times)
                    Cache::lock($mgv . "_lock", 10)->block(5, function () use ($mgv) {
                        $bvData = Cache::get($mgv);
                        if ($bvData === null) {
                            $bvData = [];
                        }
                        $bvData["tries"][] = now();
                        Cache::put($mgv, $bvData, now()->addMinutes(HumanVerification::BV_DATA_EXPIRATION_MINUTES));
                    });
                    if ($time_since_last_try < 100) {
                        // Make sure there are at least 100ms between each try
                        usleep((100 - $time_since_last_try) * 1000);
                    }
                    $params = $request->all();
                    $params["mgv"] = $mgv;
                    $url = route($route, $params);
                    \app()->make(QueryTimer::class)->observeEnd(self::class);
                    return redirect($url);
                } else {
                    \app()->make(QueryTimer::class)->observeEnd(self::class);
                    self::logBrowserverification($request);
                    abort(429);
                }
            }

            // CSS/JS Data is loaded we need to wait for CSP
            $csp_result = $this->waitForCSP($bvData, $mgv);

            // Save state in Search settings
            $search_settings = \app()->make(SearchSettings::class);
            $search_settings->bv_key = $mgv;

            // Check if Javascript was loaded
            // No after waiting load a fresh copy of bvData
            $bvData = Cache::get($mgv);
            if (array_key_exists("js", $bvData)) {
                $search_settings->javascript_enabled = true;
            }
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        } else {
            // The verification key
            Cache::put($mgv, [
                "start" => now(),
                "tries" => [
                    now()
                ]
            ], now()->addSeconds(30));
            $report_to = route("csp_verification", ["mgv" => $mgv]);
            $params = $request->all();
            $params["mgv"] = $mgv;
            $js_url = route($route, $params);
            $params["iframe"] = "1";
            $frame_url = route($route, $params);
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return response(
                view('layouts.resultpage.framedResultPage', ["frame_url" => $frame_url, "js_url" => $js_url, "mgv" => $mgv]),
                200,
                [
                    "Cache-Control" => "no-cache, no-store, must-revalidate",
                    "Pragma" => "no-cache",
                    "Expires" => "0",
                    "Content-Security-Policy" => "default-src 'self'; script-src 'self' 'nonce-$mgv'; script-src-elem 'self' 'nonce-$mgv'; script-src-attr 'self'; style-src 'self' 'nonce-$mgv'; style-src-elem 'self' 'nonce-$mgv'; style-src-attr 'self' 'nonce-$mgv'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self' https://scripts.zdv.uni-mainz.de; form-action 'self' www.paypal.com; report-uri " . $report_to . "; report_to " . $report_to
                ]
            );
        }
    }

    private function waitForCSP(&$bvData, $key)
    {
        $max_wait_time_ms = 5000;

        $wait_time_ms = 250;
        $wait_start = $bvData["start"];

        $csp_loaded = null;

        do {
            $bvData = Cache::get($key);

            if ($bvData === null) {
                return null;
            }

            if ($csp_loaded !== true) {
                if (\array_key_exists("csp", $bvData) && \array_key_exists("loaded", $bvData["csp"])) {
                    if (now()->diffInMilliseconds($bvData["csp"]["loaded"]) > $wait_time_ms) {
                        $csp_loaded = true;
                    } else {
                        $csp_loaded = false;
                    }
                } else {
                    // If css and javascript is both loaded we will wait a few more moments
                    /** @var \Carbon\Carbon $stop_waiting_for_csp */
                    $stop_waiting_for_csp = $bvData["css"]["loaded"]->addMilliseconds($wait_time_ms);
                    if (\array_key_exists("js", $bvData) && \array_key_exists("loaded", $bvData["js"])) {
                        /** @var \Carbon\Carbon $stop_waiting_for_csp_js */
                        $stop_waiting_for_csp_js = $bvData["css"]["loaded"]->addMilliseconds($wait_time_ms);
                        if ($stop_waiting_for_csp_js->isAfter($stop_waiting_for_csp)) {
                            $stop_waiting_for_csp = $stop_waiting_for_csp_js;
                        }
                    }
                    if (now()->isAfter($stop_waiting_for_csp)) {
                        $csp_loaded = true;
                    }
                }
            }

            if ($csp_loaded) {
                return true;
            }
            // Calculate Sleep Time
            // Sleeptime gradually increases with the current wait time
            // Min 10ms and max 1s
            $sleep_time_milliseconds = round(now()->diffInMilliseconds($wait_start) / 10);
            $sleep_time_milliseconds = max(10, $sleep_time_milliseconds);
            $sleep_time_milliseconds = min(1000, $sleep_time_milliseconds);
            usleep($sleep_time_milliseconds * 1000);
        } while (now()->diffInMilliseconds($wait_start) < $max_wait_time_ms);
        return false;
    }

    public static function logBrowserverification(Request $request)
    {

        $log = [
            now()->format("Y-m-d H:i:s"),
            $request->input("eingabe"),
            "js=" . \app()->make(SearchSettings::class)->javascript_enabled,
        ];

        Redis::rpush(self::LOG_KEY, $log);
    }

    public static function FLUSH_LOGS()
    {
        $max_entries = 250;
        $file_path = \storage_path("logs/metager/bv_fail.csv");
        $fh = fopen($file_path, "a");

        $logs = [];
        try {
            while (true) {
                $entry = Redis::lpop(self::LOG_KEY);
                if (!empty($entry)) {
                    $logs[] = $entry;
                }
                if (sizeof($logs) >= $max_entries || empty($entry)) {
                    if (sizeof($logs) > 0) {
                        foreach ($logs as $log) {
                            \fputcsv($fh, $log);
                            $logs = [];
                        }
                    }
                    if (empty($entry)) {
                        break;
                    }
                }
            }
        } finally {
            fclose($fh);
        }
    }
}