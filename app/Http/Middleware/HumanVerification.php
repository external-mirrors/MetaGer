<?php

namespace App\Http\Middleware;

use Cache;
use Captcha;
use Closure;
use Cookie;
use Illuminate\Http\Response;
use URL;

class HumanVerification
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
        // The specific user
        $user = null;
        $update = true;
        $prefix = "humanverification";
        try {
            $ip = $request->ip();
            $id = "";
            $uid = "";
            if (\App\Http\Controllers\HumanVerification::couldBeSpammer($ip)) {
                $id = hash("sha512", "999.999.999.999");
                $uid = hash("sha512", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
            } else {
                $id = hash("sha512", $ip);
                $uid = hash("sha512", $ip . $_SERVER["AGENT"] . "uid");
            }
            unset($_SERVER["AGENT"]);

            /**
             * If the user sends a Password or a key
             * We will not verificate the user.
             * If someone that uses a bot finds this out we
             * might have to change it at some point.
             */
            if ($request->filled('password') || $request->filled('key') || Cookie::get('key') !== null || $request->filled('appversion') || !env('BOT_PROTECTION', false)) {
                $update = false;
                return $next($request);
            }

            # Get all Users of this IP
            $users = Cache::get($prefix . "." . $id, []);
            $users = $this->removeOldUsers($prefix, $users);

            $user = [];
            if (empty($users[$uid])) {
                $user = [
                    'uid' => $uid,
                    'id' => $id,
                    'unusedResultPages' => 0,
                    'whitelist' => false,
                    'locked' => false,
                    "lockedKey" => "",
                    "expiration" => now()->addWeeks(2),
                ];
            } else {
                $user = $users[$uid];
            }
            # Lock out everyone in a Bot network
            # Find out how many requests this IP has made
            $sum = 0;
            // Defines if this is the only user using that IP Adress
            $alone = true;
            foreach ($users as $uid => $userTmp) {
                if (!$userTmp["whitelist"]) {
                    $sum += $userTmp["unusedResultPages"];
                    if ($userTmp["uid"] != $uid) {
                        $alone = false;
                    }
                }
            }

            # A lot of automated requests are from websites that redirect users to our result page.
            # We will detect those requests and put a captcha
            $referer = URL::previous();
            # Just the URL-Parameter
            $refererLock = false;
            if (stripos($referer, "?") !== false) {
                $referer = substr($referer, stripos($referer, "?") + 1);
                $referer = urldecode($referer);
                if (preg_match("/http[s]{0,1}:\/\/metager\.de\/meta\/meta.ger3\?.*?eingabe=([\w\d]+\.){1,2}[\w\d]+/si", $referer) === 1) {
                    $refererLock = true;
                }

            }

            if ((!$alone && $sum >= 50 && !$user["whitelist"]) || $refererLock) {
                $user["locked"] = true;
            }

            # If the user is locked we will force a Captcha validation
            if ($user["locked"]) {
                $captcha = Captcha::create("default", true);
                $user["lockedKey"] = $captcha["key"];
                \App\PrometheusExporter::CaptchaShown();
                return
                new Response(
                    view('humanverification.captcha')
                        ->with('title', "Bestätigung erforderlich")
                        ->with('uid', $uid)
                        ->with('id', $id)
                        ->with('url', url()->full())
                        ->with('image', $captcha["img"])
                );
            }

            $user["unusedResultPages"]++;

            if ($alone || $user["whitelist"]) {
                # This IP doesn't need verification yet
                # The user currently isn't locked

                # We have different security gates:
                #   50 and then every 25 => Captcha validated Result Pages
                # If the user shows activity on our result page the counter will be deleted
                if ($user["unusedResultPages"] === 50 || ($user["unusedResultPages"] > 50 && $user["unusedResultPages"] % 25 === 0)) {
                    $user["locked"] = true;
                }

            }
        } finally {
            if ($update) {
                if ($user["whitelist"]) {
                    $user["expiration"] = now()->addWeeks(2);
                } else {
                    $user["expiration"] = now()->addHours(72);
                }
                $this->setUser($prefix, $user);
            }
        }

        $request->request->add(['verification_id' => $user["uid"], 'verification_count' => $user["unusedResultPages"]]);
        return $next($request);

    }

    public function setUser($prefix, $user)
    {
        // Lock must be acquired within 2 seconds
        $userList = Cache::get($prefix . "." . $user["id"], []);
        $userList[$user["uid"]] = $user;
        Cache::put($prefix . "." . $user["id"], $userList, 2 * 7 * 24 * 60 * 60);
    }

    public function removeOldUsers($prefix, $userList)
    {
        $newUserlist = [];
        $now = now();

        $id = "";
        $changed = false;
        foreach ($userList as $uid => $user) {
            $id = $user["id"];
            if ($now < $user["expiration"]) {
                $newUserlist[$user["uid"]] = $user;
            } else {
                $changed = true;
            }
        }

        if ($changed) {
            Cache::put($prefix . "." . $user["id"], $newUserlist, 2 * 7 * 24 * 60 * 60);
        }

        return $newUserlist;
    }
}
