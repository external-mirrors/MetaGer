<?php

namespace App\Http\Controllers;

use Captcha;
use Carbon;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            $url = $request->input('url');
        }

        $userlist = Cache::get(HumanVerification::PREFIX . "." . $id, []);
        $user = null;

        if (sizeof($userlist) === 0 || empty($userlist[$uid])) {
            return redirect('/');
        } else {
            $user = $userlist[$uid];
        }

        if ($request->getMethod() == 'POST') {

            $lockedKey = $user["lockedKey"];
            $key = $request->input('captcha');
            $key = strtolower($key);

            if (!$hasher->check($key, $lockedKey)) {
                $captcha = Captcha::create("default", true);
                $user["lockedKey"] = $captcha["key"];
                HumanVerification::saveUser($user);

                return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
                    ->with('uid', $user["uid"])
                    ->with('id', $id)
                    ->with('url', $url)
                    ->with('image', $captcha["img"])
                    ->with('errorMessage', 'Fehler: Falsche Eingabe!');
            } else {
                # If we can unlock the Account of this user we will redirect him to the result page
                if ($user !== null && $user["locked"]) {
                    # The Captcha was correct. We can remove the key from the user
                    # Additionally we will whitelist him so he is not counted towards botnetwork
                    $user["locked"] = false;
                    $user["lockedKey"] = "";
                    $user["whitelist"] = true;
                    HumanVerification::saveUser($user);
                    return redirect($url);
                } else {
                    return redirect('/');
                }
            }
        }
        $captcha = Captcha::create("default", true);
        $user["lockedKey"] = $captcha["key"];
        HumanVerification::saveUser($user);

        return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
            ->with('uid', $user["uid"])
            ->with('id', $id)
            ->with('url', $url)
            ->with('image', $captcha["img"]);

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
        $requiredPass = md5($mm . Carbon::NOW()->day . $url . env("PROXY_PASSWORD"));
        if (HumanVerification::checkId($request, $mm) && $requiredPass === $password) {
            HumanVerification::removeUser($request, $mm);
        }
        return redirect($url);
    }

    private static function saveUser($user)
    {
        $userList = Cache::get(HumanVerification::PREFIX . "." . $user["id"], []);
        $userList[$user["uid"]] = $user;
        if ($user["whitelist"]) {
            $user["expiration"] = now()->addWeeks(2);
        } else {
            $user["expiration"] = now()->addHours(72);
        }
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
            Cache::put(HumanVerification::PREFIX . "." . $user["id"], $userList, now()->addWeeks(2));
        }
    }

    private static function removeUser($request, $uid)
    {
        $ip = $request->ip();
        $id = "";
        if (HumanVerification::couldBeSpammer($ip)) {
            $id = hash("sha512", "999.999.999.999");
        } else {
            $id = hash("sha512", $ip);
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
            if (!empty($userTmp) && !empty($userTmp["whitelist"]) && !$userTmp["whitelist"]) {
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
            $uid = hash("sha512", "999.999.999.999" . $ip . $_SERVER["AGENT"] . "uid");
        } else {
            $uid = hash("sha512", $ip . $_SERVER["AGENT"] . "uid");
        }

        if ($uid === $id) {
            return true;
        } else {
            return false;
        }
    }

    public static function couldBeSpammer($ip)
    {
        $possibleSpammer = false;

        # Check for recent Spams
        $eingabe = \Request::input('eingabe');
        if (\preg_match("/^susimail\s+-site:[^\s]+\s-site:/si", $eingabe)) {
            return true;
        } else if (\preg_match("/^\s*site:\"linkedin\.com[^\"]*\"\s+/si", $eingabe)) {
            return true;
        }

        return $possibleSpammer;

    }
}
