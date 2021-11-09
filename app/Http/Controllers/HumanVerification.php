<?php

namespace App\Http\Controllers;

use Captcha;
use Carbon;
use Cookie;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Input;

class HumanVerification extends Controller
{
    const PREFIX = "humanverification";
    const EXPIRELONG = 60 * 60 * 24 * 14;
    const EXPIRESHORT = 60 * 60 * 72;

    public static function captcha(Request $request, Hasher $hasher, $id, $uid, $url = null)
    {
        if ($url != null) {
            $url = base64_decode(str_replace("<<SLASH>>", "/", $url));
        } else {
            $url = $request->input('url', url("/"));
        }

        $protocol = "http://";

        if ($request->secure()) {
            $protocol = "https://";
        }

        if (stripos($url, $protocol . $request->getHttpHost()) !== 0) {
            $url = url("/");
        }

        $userlist = Cache::get(HumanVerification::PREFIX . "." . $id, []);
        $user = null;

        if (sizeof($userlist) === 0 || empty($userlist[$uid])) {
            return redirect('/');
        } else {
            $user = $userlist[$uid];
        }

        if ($request->getMethod() == 'POST') {
            \App\PrometheusExporter::CaptchaAnswered();
            $lockedKey = $request->input("c", "");

            $rules = ['captcha' => 'required|captcha_api:' . $lockedKey  . ',math'];
            $validator = validator()->make(request()->all(), $rules);

            if (empty($lockedKey) || $validator->fails()) {
                $captcha = Captcha::create("default", true);
                \App\PrometheusExporter::CaptchaShown();
                return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
                    ->with('uid', $user["uid"])
                    ->with('id', $id)
                    ->with('url', $url)
                    ->with('correct', $captcha["key"])
                    ->with('image', $captcha["img"])
                    ->with('errorMessage', 'Fehler: Falsche Eingabe!');
            } else {
                \App\PrometheusExporter::CaptchaCorrect();
                # If we can unlock the Account of this user we will redirect him to the result page
                if ($user !== null && $user["locked"]) {
                    # The Captcha was correct. We can remove the key from the user
                    # Additionally we will whitelist him so he is not counted towards botnetwork
                    $user["locked"] = false;
                    $user["whitelist"] = true;
                    HumanVerification::saveUser($user);
                    return redirect($url);
                } else {
                    return redirect('/');
                }
            }
        }

        $captcha = Captcha::create("default", true);
        \App\PrometheusExporter::CaptchaShown();
        return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
            ->with('uid', $user["uid"])
            ->with('id', $id)
            ->with('url', $url)
            ->with('correct', $captcha["key"])
            ->with('image', $captcha["img"]);
    }

    public static function logCaptcha(Request $request)
    {
        $fail2banEnabled = config("metager.metager.fail2ban.enabled");
        if (empty($fail2banEnabled) || !$fail2banEnabled || !config("metager.metager.fail2ban.url") || !config("metager.metager.fail2ban.user") || !config("metager.metager.fail2ban.password")) {
            return;
        }

        // Submit fetch job to worker
        $mission = [
            "resulthash" => "captcha",
            "url" => config("metager.metager.fail2ban.url") . "/captcha/",
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

    public static function remove(Request $request)
    {
        if (!$request->has('mm')) {
            abort(404, "Keine Katze gefunden.");
        }

        if (HumanVerification::checkId($request, $request->input('mm'))) {
            HumanVerification::removeUser($request, $request->input('mm'));
        }
        return response(hex2bin('89504e470d0a1a0a0000000d494844520000000100000001010300000025db56ca00000003504c5445000000a77a3dda0000000174524e530040e6d8660000000a4944415408d76360000000020001e221bc330000000049454e44ae426082'), 200)
            ->header('Content-Type', 'image/png');
    }

    public static function removeGet(Request $request, $mm, $password, $url)
    {
        $url = base64_decode(str_replace("<<SLASH>>", "/", $url));
        # If the user is correct and the password is we will delete any entry in the database
        $requiredPass = md5($mm . Carbon::NOW()->day . $url . config("metager.metager.proxy.password"));

        if (HumanVerification::checkId($request, $mm) && $requiredPass === $password) {
            HumanVerification::removeUser($request, $mm);
        }
        return redirect($url);
    }

    private static function saveUser($user)
    {
        $userList = Cache::get(HumanVerification::PREFIX . "." . $user["id"], []);

        if ($user["whitelist"]) {
            $user["expiration"] = now()->addWeeks(2);
        } else {
            $user["expiration"] = now()->addHours(72);
        }
        $userList[$user["uid"]] = $user;
        Cache::put(HumanVerification::PREFIX . "." . $user["id"], $userList, now()->addWeeks(2));
    }

    private static function deleteUser($user)
    {
        $userList = Cache::get(HumanVerification::PREFIX . "." . $user["id"], []);
        $newUserList = [];
        $changed = false;

        foreach ($userList as $uid => $userTmp) {
            if ($userTmp["uid"] !== $user["uid"]) {
                $newUserList[$userTmp["uid"]] = $userTmp;
            } else {
                $changed = true;
            }
        }

        if ($changed) {
            if (sizeof($newUserList) > 0) {
                Cache::put(HumanVerification::PREFIX . "." . $user["id"], $newUserList, now()->addWeeks(2));
            } else {
                Cache::forget(HumanVerification::PREFIX . "." . $user["id"], $newUserList);
            }
        }
    }

    private static function removeUser($request, $uid)
    {
        $ip = $request->ip();
        $id = "";
        if (HumanVerification::couldBeSpammer($ip)) {
            $id = hash("sha1", "999.999.999.999");
        } else {
            $id = hash("sha1", $ip);
        }

        $userlist = Cache::get(HumanVerification::PREFIX . "." . $id, []);
        $user = null;

        if (sizeof($userlist) === 0 || empty($userlist[$uid])) {
            return;
        } else {
            $user = $userlist[$uid];
        }

        $sum = 0;
        foreach ($userlist as $uidTmp => $userTmp) {
            if (!empty($userTmp) && gettype($userTmp["whitelist"]) === "boolean" && !$userTmp["whitelist"]) {
                $sum += intval($userTmp["unusedResultPages"]);
            }
        }
        # Check if we have to whitelist the user or if we can simply delete the data
        if ($user["unusedResultPages"] < $sum && !$user["whitelist"]) {
            # Whitelist
            $user["whitelist"] = true;
        }

        if ($user["whitelist"]) {
            $user["unusedResultPages"] = 0;
            HumanVerification::saveUser($user);
        } else {
            HumanVerification::deleteUser($user);
        }
    }

    private static function checkId($request, $id)
    {
        $uid = "";
        $ip = $request->ip();
        if (HumanVerification::couldBeSpammer($ip)) {
            $uid = hash("sha1", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }

        if ($uid === $id) {
            return true;
        } else {
            return false;
        }
    }

    public static function couldBeSpammer($ip)
    {
        # Check for recent Spams
        $eingabe = \Request::input('eingabe');
        $spams = Redis::lrange("spam", 0, -1);
        foreach ($spams as $index => $spam) {
            if (\preg_match($spam, $eingabe)) {
                return "999.999.999.999" . $index;
            }
        }

        return null;
    }

    public function botOverview(Request $request)
    {
        $id = "";
        $uid = "";
        $ip = $request->ip();
        if (\App\Http\Controllers\HumanVerification::couldBeSpammer($ip)) {
            $id = hash("sha1", "999.999.999.999");
            $uid = hash("sha1", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $id = hash("sha1", $ip);
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }

        $userList = Cache::get(HumanVerification::PREFIX . "." . $id);
        $user = $userList[$uid];

        return view('humanverification.botOverview')
            ->with('title', "Bot Overview")
            ->with('ip', $ip)
            ->with('userList', $userList)
            ->with('user', $user);
    }

    public function botOverviewChange(Request $request)
    {
        $id = "";
        $uid = "";
        $ip = $request->ip();
        if (\App\Http\Controllers\HumanVerification::couldBeSpammer($ip)) {
            $id = hash("sha1", "999.999.999.999");
            $uid = hash("sha1", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $id = hash("sha1", $ip);
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }

        $userList = Cache::get(HumanVerification::PREFIX . "." . $id);
        $user = $userList[$uid];

        if ($request->filled("locked")) {
            $user["locked"] = boolval($request->input('locked'));
        } elseif ($request->filled("whitelist")) {
            $user["whitelist"] = boolval($request->input('whitelist'));
        } elseif ($request->filled("unusedResultPages")) {
            $user["unusedResultPages"] = intval($request->input('unusedResultPages'));
        }

        HumanVerification::saveUser($user);
        return redirect('admin/bot');
    }

    public function browserVerification(Request $request)
    {
        $key = $request->input("id", "");

        // Verify that key is a md5 checksum
        if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
            abort(404);
        }

        Redis::connection(config('cache.stores.redis.connection'))->rpush($key, true);
        Redis::connection(config('cache.stores.redis.connection'))->expire($key, 30);

        return response(view('layouts.resultpage.verificationCss'), 200)->header("Content-Type", "text/css");
    }

    public static function block(Request $request)
    {
        $prefix = "humanverification";

        $ip = $request->ip();
        $id = "";
        $uid = "";
        if (\App\Http\Controllers\HumanVerification::couldBeSpammer($ip)) {
            $id = hash("sha1", "999.999.999.999");
            $uid = hash("sha1", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $id = hash("sha1", $ip);
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }

        /**
         * If the user sends a Password or a key
         * We will not verificate the user.
         * If someone that uses a bot finds this out we
         * might have to change it at some point.
         */
        if ($request->filled('password') || $request->filled('key') || Cookie::get('key') !== null || $request->filled('appversion') || !config('metager.metager.botprotection.enabled')) {
            $update = false;
            return $next($request);
        }

        # Get all Users of this IP
        $users = Cache::get($prefix . "." . $id, []);

        $user = [];
        $changed = false;
        if (empty($users[$uid])) {
            $user = [
                'uid' => $uid,
                'id' => $id,
                'unusedResultPages' => 0,
                'whitelist' => false,
                'locked' => true,
                "lockedKey" => "",
                "expiration" => now()->addWeeks(2),
            ];
            $changed = true;
        } else {
            $user = $users[$uid];
            if (!$user["locked"]) {
                $user["locked"] = true;
                $changed = true;
            }
        }

        if ($user["whitelist"]) {
            $user["expiration"] = now()->addWeeks(2);
        } else {
            $user["expiration"] = now()->addHours(72);
        }
        if ($changed) {
            $userList = Cache::get($prefix . "." . $user["id"], []);
            $userList[$user["uid"]] = $user;
            Cache::put($prefix . "." . $user["id"], $userList, 2 * 7 * 24 * 60 * 60);
        }
        return [$id, $uid];
    }
}
