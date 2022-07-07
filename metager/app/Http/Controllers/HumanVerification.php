<?php

namespace App\Http\Controllers;

use App\Models\HumanVerification as ModelsHumanVerification;
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
    const TOKEN_PREFIX = "humanverificationtoken.";

    public static function captchaShow(Request $request)
    {
        $redirect_url = $request->get("url", url('/'));
        $protocol = "http://";
        if ($request->secure()) {
            $protocol = "https://";
        }

        if (stripos($redirect_url, $protocol . $request->getHttpHost()) !== 0) {
            $redirect_url = url("/");
        }

        $human_verification = \app()->make(ModelsHumanVerification::class);

        if (!$human_verification->isLocked()) {
            return redirect($redirect_url);
        }

        $captcha = Captcha::create("default", true);
        \App\PrometheusExporter::CaptchaShown();
        return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
            ->with('uid', $human_verification->uid)
            ->with('id', $human_verification->id)
            ->with('url', $redirect_url)
            ->with('correct', $captcha["key"])
            ->with('image', $captcha["img"]);
    }

    public static function captchaSolve(Request $request)
    {
        \App\PrometheusExporter::CaptchaAnswered();
        $redirect_url = $request->post("url", url('/'));
        $protocol = "http://";
        if ($request->secure()) {
            $protocol = "https://";
        }

        if (stripos($redirect_url, $protocol . $request->getHttpHost()) !== 0) {
            $redirect_url = url("/");
        }
        $human_verification = \app()->make(ModelsHumanVerification::class);

        $lockedKey = $request->post("c", "");

        $rules = ['captcha' => 'required|captcha_api:' . $lockedKey  . ',math'];
        $validator = validator()->make(request()->all(), $rules);

        if (empty($lockedKey) || $validator->fails()) {
            return redirect(route('captcha_show', ["url" => $redirect_url, "e" => ""]));
        } else {
            \App\PrometheusExporter::CaptchaCorrect();
            # Generate a token that makes the user skip Humanverification
            # There are some special cases where a user that entered a correct Captcha
            # might see a captcha again on his next request
            $token = md5(microtime(true));
            Cache::put(self::TOKEN_PREFIX . $token, 5, 3600);
            $url_parts = parse_url($redirect_url);
            // If URL doesn't have a query string.
            if (isset($url_parts['query'])) { // Avoid 'Undefined index: query'
                parse_str($url_parts['query'], $params);
            } else {
                $params = array();
            }

            $params['token'] = $token;     // Overwrite if exists

            // Note that this will url_encode all values
            $url_parts['query'] = http_build_query($params);

            // If not
            $url = $url_parts['scheme'] . '://' . $url_parts['host'] . (!empty($url_parts["port"]) ? ":" . $url_parts["port"] : "") . (!empty($url_parts["path"]) ? $url_parts["path"] : "") . '?' . (!empty($url_parts["query"]) ? $url_parts["query"] : "");

            # If we can unlock the Account of this user we will redirect him to the result page
            # The Captcha was correct. We can remove the key from the user
            # Additionally we will whitelist him so he is not counted towards botnetwork
            $human_verification->verifyUser();
            $human_verification->unlockUser();

            # ToDo Remove this again
            # Gathering some data to debug problems with user getting caught in captchas 
            $file_path_agents = \storage_path("logs/metager/captcha_solve/" . $human_verification->id . ".agents");
            $file_path_userlist = \storage_path("logs/metager/captcha_solve/" . $human_verification->id . ".userlist");

            if (!\file_exists(\dirname($file_path_agents))) {
                mkdir(\dirname($file_path_agents), 0777);
            }
            $log_line = now()->format("Y-m-d_H:i:s") . " " . $_SERVER["AGENT"] . \PHP_EOL;
            \file_put_contents($file_path_agents, $log_line, \FILE_APPEND);
            \file_put_contents($file_path_userlist, json_encode($human_verification->getUserList(), \JSON_PRETTY_PRINT));

            return redirect($url);
        }
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

        $human_verification = \app()->make(ModelsHumanVerification::class);
        if ($request->input("mm") === $human_verification->uid) {
            $human_verification->verifyUser();
        }

        return response(hex2bin('89504e470d0a1a0a0000000d494844520000000100000001010300000025db56ca00000003504c5445000000a77a3dda0000000174524e530040e6d8660000000a4944415408d76360000000020001e221bc330000000049454e44ae426082'), 200)
            ->header('Content-Type', 'image/png');
    }

    public static function removeGet(Request $request, $mm, $password, $url)
    {
        $url = base64_decode(str_replace("<<SLASH>>", "/", $url));
        # If the user is correct and the password is we will delete any entry in the database
        $requiredPass = md5($mm . Carbon::NOW()->day . $url . config("metager.metager.proxy.password"));

        $human_verification = \app()->make(ModelsHumanVerification::class);
        if ($mm === $human_verification->uid && $requiredPass == $password) {
            $human_verification->verifyUser();
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

    public function botOverview(Request $request)
    {
        $human_verification = \app()->make(ModelsHumanVerification::class);

        return view('humanverification.botOverview')
            ->with('title', "Bot Overview")
            ->with('ip', $request->ip())
            ->with('userList', $human_verification->getUserList())
            ->with('user', $human_verification->getUser());
    }

    public function botOverviewChange(Request $request)
    {
        $human_verification = \app()->make(ModelsHumanVerification::class);

        if ($request->filled("locked")) {
            if (\boolval($request->input("locked"))) {
                $human_verification->lockUser();
            } else {
                $human_verification->unlockUser();
            }
        } elseif ($request->filled("whitelist")) {
            if (\boolval($request->input("whitelist"))) {
                $human_verification->verifyUser();
            } else {
                $human_verification->unverifyUser();
            }
        } elseif ($request->filled("unusedResultPages")) {
            $human_verification->setUnusedResultPage(intval($request->input('unusedResultPages')));
        }

        return redirect('admin/bot');
    }

    public function browserVerification(Request $request)
    {
        $key = $request->input("id", "");

        // Verify that key is a md5 checksum
        if (preg_match("/^[a-f0-9]{32}$/", $key)) {
            Redis::connection(config('cache.stores.redis.connection'))->rpush($key, true);
            Redis::connection(config('cache.stores.redis.connection'))->expire($key, 30);
        }
        return response(view('layouts.resultpage.verificationCss'), 200)->header("Content-Type", "text/css");
    }
}
