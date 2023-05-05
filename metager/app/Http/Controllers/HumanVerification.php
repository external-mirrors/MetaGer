<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Verification\CookieVerification;
use App\Models\Verification\HumanVerification as ModelsHumanVerification;
use App\Models\Verification\Captcha;
use Carbon;
use Cookie;
use Crypt;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Input;
use Laravel\SerializableClosure\Signers\Hmac;
use LaravelLocalization;

class HumanVerification extends Controller
{
    const PREFIX = "humanverification";
    const EXPIRELONG = 60 * 60 * 24 * 14;
    const EXPIRESHORT = 60 * 60 * 72;
    const TOKEN_PREFIX = "humanverificationtoken.";
    const BV_DATA_EXPIRATION_MINUTES = 5;

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

        if (!$request->filled("key")) {
            abort(404);
        }

        $human_verification = ModelsHumanVerification::createFromKey($request->input("key"));

        if ($human_verification === null || !$human_verification->isLocked()) {
            return redirect($redirect_url);
        }

        $captcha = new Captcha(
            app('Illuminate\Filesystem\Filesystem'),
            app('Illuminate\Contracts\Config\Repository'),
            app('Intervention\Image\ImageManager'),
            app('Illuminate\Session\Store'),
            app('Illuminate\Hashing\BcryptHasher'),
            app('Illuminate\Support\Str')
        );
        $captcha_key = $captcha->create('default', true);

        // Extract the correct solution to this captcha for generating the Audio Captcha
        $text = implode(" ", $captcha->getText());

        // Make sure each capture can only be tried once
        $captcha_id = Crypt::encryptString(md5(microtime(true) . $text));

        $tts_url = TTSController::CreateTTSUrl($text, Localization::getLanguage());

        \App\PrometheusExporter::CaptchaShown();
        return view('humanverification.captcha')->with('title', 'Bestätigung notwendig')
            ->with("id", $captcha_id)
            ->with('url', $redirect_url)
            ->with("key", $request->input("key"))
            ->with('correct', $captcha_key["key"])
            ->with('image', $captcha_key["img"])
            ->with('tts_url', $tts_url)
            ->with('css', [mix('css/verify/index.css')]);
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

        $lockedKey = $request->post("c", "");

        $rules = ['captcha' => 'required|captcha_api:' . $lockedKey . ',math'];
        $validator = validator()->make(request()->all(), $rules);

        // There will be an entry in Cache for this key if this same captcha was already tried
        $captcha_id = $request->input("id", "");
        if (!empty($captcha_id)) {
            try {
                $captcha_id = Crypt::decryptString($captcha_id);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                $captcha_id = "";
            }
            // If this is not a md5
            if (strlen($captcha_id) !== 32 || !ctype_xdigit($captcha_id)) {
                $captcha_id = "";
            }
        }

        if (empty($captcha_id) || Cache::has($captcha_id) || empty($lockedKey) || $validator->fails() || !$request->has("key") || !Cache::has($request->input("key"))) {
            $params = [
                "url" => $redirect_url,
                "e" => "",
                "key" => $request->input("key", "")
            ];
            if ($request->has("dnaa")) {
                $params["dnaa"] = true;
            }
            Cache::put($captcha_id, true, now()->addMinutes(10));
            return redirect(route('captcha_show', $params));
        } else {
            // Check if the user wants to store a cookie
            if ($request->has("dnaa")) {
                CookieVerification::createCookie();
            }

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

            $query = isset($params["eingabe"]) ? $params["eingabe"] : "";
            $time = 0;
            if ($request->filled("begin")) {
                $time = $request->input("begin");
                $time = \filter_var($time, \FILTER_VALIDATE_FLOAT);
                if ($time === false) {
                    $time = 0;
                } else {
                    $time = microtime(true) - $time;
                }
            }
            self::logCaptchaSolve($query, $time, $request->has("dnaa"));


            $params['token'] = $token; // Overwrite if exists

            // Note that this will url_encode all values
            $url_parts['query'] = http_build_query($params);

            // If not
            $url = $url_parts['scheme'] . '://' . $url_parts['host'] . (!empty($url_parts["port"]) ? ":" . $url_parts["port"] : "") . (!empty($url_parts["path"]) ? $url_parts["path"] : "") . '?' . (!empty($url_parts["query"]) ? $url_parts["query"] : "");

            # If we can unlock the Account of this user we will redirect him to the result page
            # The Captcha was correct. We can remove the key from the user
            # Additionally we will whitelist him so he is not counted towards botnetwork
            $human_verification = ModelsHumanVerification::createFromKey($request->input("key"));
            if ($human_verification !== null) {
                $human_verification->unlockUser();
                $human_verification->verifyUser();
            }

            Cache::put($captcha_id, true, now()->addMinutes(10));

            return redirect($url);
        }
    }

    private static function logCaptchaSolve(string $query, float $time, bool $dnaa = false)
    {
        $log = [
            now()->format("Y-m-d H:i:s"),
            $query,
            "time=" . $time,
            "dnaa=" . var_export($dnaa, true)
        ];
        $file_path = \storage_path("logs/metager/captcha_solve.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
    }

    public static function remove(Request $request)
    {
        if (!$request->has('hv') || !Cache::has($request->input("hv"))) {
            abort(404, "Keine Katze gefunden.");
        }

        $hv_data = Cache::get($request->input("hv"));

        if ($hv_data !== null && is_array($hv_data)) {
            foreach ($hv_data as $hv_entry) {
                $verificator = $hv_entry["class"]::impersonate($hv_entry["id"], $hv_entry["uid"]);
                $verificator->verifyUser();
            }
            Cache::forget($request->input("hv"));
        }

        return response(hex2bin('89504e470d0a1a0a0000000d494844520000000100000001010300000025db56ca00000003504c5445000000a77a3dda0000000174524e530040e6d8660000000a4944415408d76360000000020001e221bc330000000049454e44ae426082'), 200)
            ->header('Content-Type', 'image/png');
    }

    public static function removeGet(Request $request, $hv, $password, $url)
    {
        $url = \pack("H*", $url);
        # If the user is correct and the password is we will delete any entry in the database
        $requiredPass = md5(Carbon::NOW()->day . $url . config("metager.metager.proxy.password"));

        if ($requiredPass == $password && !empty($hv)) {
            $hv_data = Cache::get($hv);
            if ($hv_data !== null && is_array($hv_data)) {
                foreach ($hv_data as $hv_entry) {
                    $verificator = $hv_entry["class"]::impersonate($hv_entry["id"], $hv_entry["uid"]);
                    $verificator->verifyUser();
                }
                Cache::forget($hv);
            }
        }

        return redirect($url);
    }

    public function botOverview(Request $request)
    {
        if (!$request->has("key") || !Cache::has($request->input("key"))) {
            $url = route("resultpage", ["admin_bot" => "true"]);
            return redirect($url);
        }

        $human_verification = ModelsHumanVerification::createFromKey($request->input("key"));

        if ($human_verification === null) {
            $url = route("resultpage", ["admin_bot" => "true"]);
            return redirect($url);
        }

        return view('humanverification.botOverview')
            ->with('title', "Bot Overview")
            ->with('ip', $request->ip())
            ->with("key", $request->input("key"))
            ->with('verificators', $human_verification->getVerificators())
            ->with('css', [mix('css/admin/bot/index.css')])
            ->with('js', [mix('js/admin/bot.js')]);
    }

    public function bv(Request $request)
    {
        $bv_key = $request->input("bv_key", "");
        $bv_data = null;
        if (!empty($bv_key)) {
            if (Cache::has($bv_key)) {
                $bv_data = Cache::get($bv_key);
            } else {
                return redirect(url("/admin/bv"));
            }
        }

        return view('humanverification.bv')
            ->with('title', 'BV Data')
            ->with('bv_key', $bv_key)
            ->with("bv_data", $bv_data);
    }

    public function botOverviewChange(Request $request)
    {
        $verificator_class = $request->input("verificator");
        $human_verification = ModelsHumanVerification::createFromKey($request->input("key"));

        $verificator = null;
        foreach ($human_verification->getVerificators() as $verificator_tmp) {
            if ($verificator_tmp::class !== $verificator_class) {
                continue;
            } else {
                $verificator = $verificator_tmp;
            }
        }

        if ($request->filled("locked")) {
            if (\boolval($request->input("locked"))) {
                $verificator->lockUser();
            } else {
                $verificator->unlockUser();
            }
        } elseif ($request->filled("whitelist")) {
            if (\boolval($request->input("whitelist"))) {
                $verificator->verifyUser();
            } else {
                $verificator->unverifyUser();
            }
        } elseif ($request->filled("unusedResultPages")) {
            $verificator->setUnusedResultPage(intval($request->input('unusedResultPages')));
        }

        return redirect(route("admin_bot", ["key" => $request->input("key")]));
    }

    public function verificationCssFile(Request $request)
    {
        $key = $request->input("id", "");
        // Verify that key is a md5 checksum
        if (preg_match("/^[a-f0-9]{32}$/", $key)) {
            Cache::lock($key . "_lock", 10)->block(5, function () use ($key) {
                $bvData = Cache::get($key);
                if ($bvData === null) {
                    abort(404);
                }
                if (!\array_key_exists("css", $bvData)) {
                    $bvData["css"] = array();
                }
                $bvData["css"]["loaded"] = now();
                Cache::put($key, $bvData, now()->addMinutes(self::BV_DATA_EXPIRATION_MINUTES));
            });
        }
        return response(view('layouts.resultpage.verificationCss'), 200)->header("Content-Type", "text/css")->header("Cache-Control", "no-store");
    }

    public function verificationJsFile(Request $request)
    {
        $key = $request->input("id", "");

        // Verify that key is a md5 checksum
        if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
            abort(404);
        }

        // Acquire lock
        Cache::lock($key . "_lock", 10)->block(5, function () use ($key, $request) {
            $bvData = Cache::get($key);
            if ($bvData === null) {
                abort(404);
            }
            if (!\array_key_exists("js", $bvData)) {
                $bvData["js"] = array();
            }
            $bvData["js"]["loaded"] = now();

            if (!\array_key_exists("csp", $bvData)) {
                $bvData["csp"] = array();
            }
            if ($request->has("sp")) {
                $bvData["csp"]["honor"] = false;
            } else {
                $bvData["csp"]["honor"] = true;
            }

            if ($request->has("wd")) {
                $bvData["webdriver"] = true;
            } else {
                $bvData["webdriver"] = false;
            }

            Cache::put($key, $bvData, now()->addMinutes(self::BV_DATA_EXPIRATION_MINUTES));
        });
        return response()->file(\public_path("img/1px.png"), ["Content-Type" => "image/png", "Cache-Control" => "no-store"]);
    }

    public function verificationCSP(Request $request, string $mgv)
    {
        // Verify that key is a md5 checksum
        if (!preg_match("/^[a-f0-9]{32}$/", $mgv)) {
            abort(404);
        }

        Cache::lock($mgv . "_lock", 10)->block(5, function () use ($mgv, $request) {
            $bvData = Cache::get($mgv);
            if ($bvData === null) {
                abort(404);
            }

            $report = $request->getContent();
            $report = \json_decode($report);
            if (empty($report) || !\property_exists($report, "csp-report")) {
                return;
            } else {
                $report = $report->{"csp-report"};
            }

            if (!\array_key_exists("csp", $bvData)) {
                $bvData["csp"] = array();
            }
            // Check if this is our wanted CSP error
            $js_url = url("/js/index.js");
            if (\property_exists($report, "source-file") && stripos($report->{"source-file"}, $js_url) === 0) {
                $bvData["csp"]["reporting_enabled"] = true;
            } else {
                $bvData["csp"]["loaded"] = now();
            }

            // Update CSP Error Count
            if (!\array_key_exists("error_count", $bvData["csp"])) {
                $bvData["csp"]["error_count"] = 1;
            } else {
                $bvData["csp"]["error_count"]++;
            }

            // Update Array of Column- and Line-Numbers of the errors
            if (\property_exists($report, "line-number")) {
                if (!\array_key_exists("line-numbers", $bvData["csp"])) {
                    $bvData["csp"]["line-numbers"] = array();
                }
                $bvData["csp"]["line-numbers"][] = $report->{"line-number"};
                sort($bvData["csp"]["line-numbers"]);
            }

            if (\property_exists($report, "column-number")) {
                if (!\array_key_exists("column-numbers", $bvData["csp"])) {
                    $bvData["csp"]["column-numbers"] = array();
                }
                $bvData["csp"]["column-numbers"][] = $report->{"column-number"};
                sort($bvData["csp"]["column-numbers"]);
            }

            Cache::put($mgv, $bvData, now()->addMinutes(self::BV_DATA_EXPIRATION_MINUTES));
        });
        return response("", 200);
    }
}