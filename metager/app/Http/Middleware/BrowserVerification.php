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

        if ($request->filled("mgv")) {
            $key = $request->input('mgv', "");
            // Verify that key is a md5 checksum
            if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                abort(404);
            }
            if ($this->waitForBV($key)) {
                \app()->make(SearchSettings::class)->header_printed = false;
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                return $next($request);
            } else {
                self::logBrowserverification($request);
                return redirect(url("/"));
            }
        }

        $key = md5($request->ip() . microtime(true));
        Cache::put($key, [
            "start" => now()
        ], now()->addMinutes(30));

        echo (view('layouts.resultpage.verificationHeader')->with('key', $key)->render());
        flush();

        if ($this->supportsInlineVerification() && $this->waitForBV($key)) {
            echo (view('layouts.resultpage.resources')->render());
            flush();
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            \app()->make(SearchSettings::class)->header_printed = true;
            return $next($request);
        }

        $params = $request->all();
        $params["mgv"] = $key;
        $url = route($route, $params);

        echo (view('layouts.resultpage.unverifiedResultPage')
            ->with('url', $url)
            ->render());
        flush();
        \app()->make(QueryTimer::class)->observeEnd(self::class);
    }

    private function waitForBV($key)
    {
        $bvData = null;
        $wait_time_inline_verificytion_ms = 2000;
        $wait_time_js_ms = null;
        do {
            $bvData = Cache::get($key);
            // This condition is true when at least the css file was loaded
            if ($bvData !== null && sizeof($bvData) > 1) {
                if (!\array_key_exists("js_loaded", $bvData) && \array_key_exists("css_loaded", $bvData)) {
                    // CSS File was loaded but Javascript wasn't
                    if ($wait_time_js_ms === null) {
                        // Calculate a more acurate wait to since we do know how long it took the browser to load the css file 
                        // we can estimate a more reasonable wait time to check if js is enabled
                        $load_time_css_ms = $bvData["start"]->diffInMilliseconds($bvData["css_loaded"]);
                        $wait_time_js_ms = $load_time_css_ms * 3;
                        $wait_time_inline_verificytion_ms = max($wait_time_js_ms + 500, $wait_time_inline_verificytion_ms);
                    }
                    if (now()->diffInMilliseconds($bvData["start"]) <= $wait_time_js_ms) {
                        usleep(10 * 1000);
                        continue;
                    }
                }

                $search_settings = \app()->make(SearchSettings::class);
                if (\array_key_exists("js_loaded", $bvData)) {
                    $search_settings->bv_key = $key;
                    $search_settings->javascript_enabled = true;
                    if (\array_key_exists("js_picasso", $bvData)) {
                        $search_settings->javascript_picasso = $bvData["js_picasso"];
                    }
                }
                return true;
            }
            usleep(10 * 1000);
        } while ($bvData === null || now()->diffInMilliseconds($bvData["start"]) < $wait_time_inline_verificytion_ms);
        return false;
    }

    private function supportsInlineVerification()
    {
        $agent = new Agent();
        $agent->setUserAgent($_SERVER["AGENT"]);

        $browser = $agent->browser();
        $version = $agent->version($browser);

        // IE and Opera doesn't work at all
        if ($browser === "IE") {
            return false;
        }

        // Edge Browser up to and including version 16 doesn't support it
        if ($browser === "Edge" && \version_compare($version, 17) === -1) {
            return false;
        }

        // Safari Browser up to and including version 7 doesn't support it
        if ($browser === "Safari" && \version_compare($version, 8) === -1) {
            return false;
        }

        return true;
    }

    public static function logBrowserverification(Request $request)
    {
        $log = [
            now()->format("Y-m-d H:i:s"),
            $request->input("eingabe"),
            "js=" . \app()->make(SearchSettings::class)->javascript_enabled,
            "picasso=" . \app()->make(SearchSettings::class)->javascript_picasso,

        ];
        $file_path = \storage_path("logs/metager/bv_fail.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
    }
}
