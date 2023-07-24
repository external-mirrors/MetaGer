<?php

namespace App\Http\Middleware;

use App\Localization;
use Carbon;
use Closure;
use Cookie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LaravelLocalization;

class ExternalImagesearch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        // If this is no imagesearch return
        if ($request->input("focus", "web") !== "bilder") {
            return $next($request);
        }
        $external_provider = null;
        $store_cookie = false;
        // Check if either a Cookie is defined or external search was enabled manually
        try {
            $setting_post = $request->post("bilder_setting_external", null);
            $expiration_post = $request->post("expiration", null);
            $signature_post = $request->post("signature", null);
            $save_post = filter_var($request->post("save-external-engine", "0"), FILTER_VALIDATE_BOOL);
            if (in_array($setting_post, ["google", "bing"]) && !empty($expiration_post) && !empty($signature_post)) {
                $expiration_post = Carbon::createFromFormat("Y-m-d H:i:s", $expiration_post);
                $hash_calculated = hash_hmac("sha256", $expiration_post->format("Y-m-d H:i:s"), config("app.key"));
                if (hash_equals($hash_calculated, $signature_post) && $expiration_post->isAfter(now())) {
                    $external_provider = $setting_post;
                    if ($save_post) {
                        $store_cookie = true;
                    }
                }
            }
        } catch (Exception $e) {
            Log::debug($e->__toString());
        }

        if (empty($external_provider) && Cookie::has("bilder_setting_external")) {
            $setting_cookie = Cookie::get("bilder_setting_external");
            if (in_array($setting_cookie, ["google", "bing"])) {
                $external_provider = $setting_cookie;
            }
        }

        switch ($external_provider) {
            case null:
                return $next($request);
            case "google":
                $redirect = $this->createGoogleRedirect($request);
                break;
            case "bing":
                $redirect = $this->createBingRedirect($request);
                break;
        }

        if ($store_cookie) {
            $redirect->cookie(Cookie::forever("bilder_setting_external", $external_provider, "/", null, app()->environment("local") ? true : false));
        }
        return $redirect;
    }

    /**
     * Creates a redirect to Googles imagesearch
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function createGoogleRedirect(Request $request)
    {
        $base_url = "https://www.google.de/search";

        $params = [
            "q" => $request->input("eingabe"),
            "tbm" => "isch",
            "hl" => Localization::getLanguage(),
            "lr" => "lang_" . Localization::getLanguage(),
            "cr" => "country" . Localization::getRegion()
        ];
        $base_url .= "?" . http_build_query($params);

        return redirect($base_url);
    }

    /**
     * Creates a redirect to Bings imagesearch
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    private function createBingRedirect(Request $request)
    {
        $base_url = "https://www.bing.com/images/search";

        $params = [
            "q" => $request->input("eingabe"),
            "first" => "1",
            "setlang" => Localization::getLanguage(),
            "mkt" => LaravelLocalization::getCurrentLocale(),
        ];
        $base_url .= "?" . http_build_query($params);

        return redirect($base_url);
    }
}