<?php

namespace App\Http\Middleware;

use Closure;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\QueryTimer;
use Cache;
use App\SearchSettings;
use Response;

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
            $bv_result = $this->waitForBV($key, 6500);
            if ($bv_result) {
                \app()->make(SearchSettings::class)->header_printed = false;
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                return $next($request);
            } elseif ($bv_result === null) {
                $params = request()->except("mgv");
                $url = route("resultpage", $params);
                self::logBrowserverification($request);
                return redirect($url);
            } else {
                self::logBrowserverification($request);
                return redirect(url("/"));
            }
        }

        $key = md5($request->ip() . microtime(true));
        Cache::put($key, [
            "start" => now()
        ], now()->addMinutes(30));

        $report_to = route("csp_verification", ["mgv" => $key]);
        return response()->stream(function () use ($next, $request, $route, $key) {
            echo (view('layouts.resultpage.verificationHeader')->with('key', $key)->render());
            flush();

            if ($this->supportsInlineVerification() && $this->waitForBV($key)) {
                echo (view('layouts.resultpage.resources')->render());
                flush();
                \app()->make(QueryTimer::class)->observeEnd(self::class);
                \app()->make(SearchSettings::class)->header_printed = true;
                $next($request);
                return;
            }

            $params = $request->all();
            $params["mgv"] = $key;
            $url = route($route, $params);

            echo (view('layouts.resultpage.unverifiedResultPage')
                ->with('url', $url)
                ->render());
            flush();
            \app()->make(QueryTimer::class)->observeEnd(self::class);
        }, 200, ["Content-Security-Policy" => "default-src 'self'; script-src 'self'; script-src-elem 'self'; script-src-attr 'self'; style-src 'self'; style-src-elem 'self'; style-src-attr 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self' https://scripts.zdv.uni-mainz.de; form-action 'self' www.paypal.com; report-uri " . $report_to . "; report_to " . $report_to]);
    }

    private function waitForBV($key, $wait_time_inline_verificytion_ms = 2000)
    {
        $bvData = null;
        $wait_time_ms = 250;
        $wait_start = now();

        $js_loaded = false;
        $csp_loaded = null;

        $search_settings = \app()->make(SearchSettings::class);
        $search_settings->bv_key = $key;

        do {
            usleep(10 * 1000);

            $bvData = Cache::get($key);

            if ($bvData === null) {
                return null;
            }

            if (!$js_loaded && \array_key_exists("js", $bvData) && \array_key_exists("loaded", $bvData["js"])) {
                $js_loaded = true;
                $search_settings = \app()->make(SearchSettings::class);
                $search_settings->javascript_enabled = true;
            }

            if (\array_key_exists("csp", $bvData) && \array_key_exists("loaded", $bvData["csp"])) {
                if (now()->diffInMilliseconds($bvData["csp"]["loaded"]) > $wait_time_ms) {
                    $csp_loaded = true;
                } else {
                    $csp_loaded = false;
                }
            }

            if (
                \array_key_exists("css", $bvData) && \array_key_exists("loaded", $bvData["css"]) &&
                now()->diffInMilliseconds($bvData["css"]["loaded"]) > $wait_time_ms && ($csp_loaded === null ||
                    $csp_loaded === true
                )
            ) {
                // Css is loaded and all other checks should've been, too
                if (\array_key_exists("csp", $bvData) && array_key_exists("honor", $bvData["csp"]) && $bvData["csp"]["honor"] === false) {
                    $this->logCSP();
                }
                return true;
            }
        } while ($bvData === null || now()->diffInMilliseconds($wait_start) < $wait_time_inline_verificytion_ms);
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
        ];
        $file_path = \storage_path("logs/metager/bv_fail.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
    }

    public static function logCSP()
    {
        $request = request();
        $log = [
            now()->format("Y-m-d H:i:s"),
            $request->input("eingabe"),
            "ua=" . $_SERVER["AGENT"],
        ];
        $file_path = \storage_path("logs/metager/csp_fail.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
    }
}
