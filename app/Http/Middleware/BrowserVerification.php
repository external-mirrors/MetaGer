<?php

namespace App\Http\Middleware;

use Closure;
use GrahamCampbell\Throttle\Facades\Throttle;
use Illuminate\Support\Facades\Redis;
use \App\Http\Controllers\HumanVerification;

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
        flush();

        $answer = boolval(Redis::connection("cache")->blpop($key, 5));

        if ($answer === true) {
            return $next($request);
        } else {
            $accept = Throttle::attempt($request, 8, 1);
            if (!$accept) {
                abort(429);
            }

            # Lockout
            $ids = HumanVerification::block($request);
        }

        return redirect()->route('captcha', ["id" => $ids[0], "uid" => $ids[1], "url" => url()->full()]);

    }
}
