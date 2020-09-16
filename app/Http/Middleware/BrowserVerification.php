<?php

namespace App\Http\Middleware;

use Closure;
use GrahamCampbell\Throttle\Facades\Throttle;
use Illuminate\Support\Facades\Redis;

class BrowserVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $bvEnabled = config("metager.metager.browserverification_enabled");
        if (empty($bvEnabled) || !$bvEnabled) {
            return $next($request);
        }

        $mgv = $request->input('mgv', "");
        if (!empty($mgv)) {
            // Verify that key is a md5 checksum
            if (!preg_match("/^[a-f0-9]{32}$/", $mgv)) {
                abort(404);
            }
            $result = boolval(Redis::connection("cache")->blpop($mgv, 5));
            if ($result === true) {
                return $next($request);
            } else {
                return redirect("/");
            }
        }

        // Check if throttled
        $accept = Throttle::check($request, 8, 1);
        if (!$accept) {
            Throttle::hit($request, 8, 1);
            abort(429);
        }
        header('Content-type: text/html; charset=utf-8');
        header('X-Accel-Buffering: no');
        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('output_handler', '');

        ob_end_clean();

        $key = md5($request->ip() . microtime(true));

        echo (view('layouts.resultpage.verificationHeader')->with('key', $key)->render());
        #flush(); // TODO Readd

        $answer = boolval(Redis::connection("cache")->blpop($key, 2));

        if ($answer === true) {
            return $next($request);
        }

        $params = $request->all();
        $params["mgv"] = $key;
        $url = route("resultpage", $params);

        echo (view('layouts.resultpage.unverifiedResultPage')
                ->with('url', $url)
                ->render());

    }
}
