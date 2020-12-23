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

        if(($request->input("out", "") === "api" || $request->input("out", "") === "atom10") && app('App\Models\Key')->getStatus()) {
            header('Content-type: application/xml; charset=utf-8');
        } elseif(($request->input("out", "") === "api" || $request->input("out", "") === "atom10") && !app('App\Models\Key')->getStatus()) {
            abort(403);
        } else {
            header('Content-type: text/html; charset=utf-8');
        }
        header('X-Accel-Buffering: no');

        if (($request->filled("loadMore") && Cache::has($request->input("loadMore"))) || app('App\Models\Key')->getStatus()) {
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
                abort(404);
            }
            $result = Redis::connection("cache")->blpop($mgv, 5);
            if ($result !== null) {
                $request->request->add(["headerPrinted" => false, "jskey" => $mgv]);
                return $next($request);
            } else {
                # We are serving that request but log it for fail2ban
                self::logBrowserverification($request);
                return $next($request);
            }
        }

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

    public static function logBrowserverification() {
        $fail2banEnabled = config("metager.metager.fail2ban_enabled");
        if(empty($fail2banEnabled) || !$fail2banEnabled || !env("fail2banurl", false) || !env("fail2banuser") || !env("fail2banpassword")){
            return;
        }

        // Submit fetch job to worker
        $mission = [
                "resulthash" => "captcha",
                "url" => env("fail2banurl") . "/browserverification/",
                "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                "username" => env("fail2banuser"),
                "password" => env("fail2banpassword"),
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
