<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Cache;

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
        if ($request->filled("loadMore") && Cache::has($request->input("loadMore"))) {
            return $next($request);
        }

        ini_set('zlib.output_compression', 'Off');
        ini_set('output_buffering', 'Off');
        ini_set('output_handler', '');
        ob_end_clean();

        $bvEnabled = config("metager.metager.browserverification_enabled");
        if (empty($bvEnabled) || !$bvEnabled) {
            return $next($request);
        } else {
            $whitelist = config("metager.metager.browserverification_whitelist");
            $agent = new Agent();
            foreach ($whitelist as $browser) {
                if ($agent->match($browser)) {
                    return $next($request);
                }
            }
        }

        $mgv = $request->input('mgv', "");
        if (!empty($mgv)) {
            // Verify that key is a md5 checksum
            if (!preg_match("/^[a-f0-9]{32}$/", $mgv)) {
                abort(404);
            }
            $result = Redis::connection("cache")->blpop($mgv, 5);
            if ($result !== null) {
                $request->request->add(["headerPrinted" => false, "jskey" => $mgv]);
                return $next($request);
            } else {
                return redirect("/");
            }
        }

        header('Content-type: text/html; charset=utf-8');
        header('X-Accel-Buffering: no');

        $key = md5($request->ip() . microtime(true));

        echo(view('layouts.resultpage.verificationHeader')->with('key', $key)->render());
        flush();

        $answer = Redis::connection("cache")->blpop($key, 2);
        if ($answer !== null) {
            echo(view('layouts.resultpage.resources')->render());
            flush();
            $request->request->add(["headerPrinted" => true, "jskey" => $key]);
            return $next($request);
        }

        $params = $request->all();
        $params["mgv"] = $key;
        $url = route("resultpage", $params);

        echo(view('layouts.resultpage.unverifiedResultPage')
                ->with('url', $url)
                ->render());
    }
}
