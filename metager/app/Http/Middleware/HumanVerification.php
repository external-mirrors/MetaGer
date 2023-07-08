<?php

namespace App\Http\Middleware;

use App;
use App\Models\Authorization\Authorization;
use App\Models\Verification\AgentVerification;
use App\Models\Verification\HumanVerification as ModelsHumanVerification;
use App\QueryTimer;
use App\SearchSettings;
use Cache;
use Closure;
use LaravelLocalization;
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
        \app()->make(QueryTimer::class)->observeStart(self::class);

        $should_skip = false;

        if ($request->filled("loadMore") && Cache::has($request->input("loadMore"))) {
            $should_skip = true;
        }

        // Check for a valid Skip Token
        if (!$should_skip && $request->filled("token")) {
            $prefix = \App\Http\Controllers\HumanVerification::TOKEN_PREFIX;
            $token = $prefix . $request->input("token");

            if (Cache::has($token)) {
                $value = Cache::get($token);

                if (!empty($value) && intval($value) > 0) {
                    Cache::put($token, ($value - 1), now()->addHour());
                    $should_skip = true;
                } else {
                    // Token is not valid. Remove it
                    Cache::forget($token);
                    \app()->make(QueryTimer::class)->observeEnd(self::class);
                    return redirect()->to(url()->current() . '?' . http_build_query($request->except(["token"])));
                }
            } else {
                $should_skip = true;
            }
        }

        if (!$should_skip && !config("metager.metager.botprotection.enabled") || app(Authorization::class)->canDoAuthenticatedSearch()) {
            $should_skip = true;
        }

        if ($should_skip) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        }

        // The specific user
        $user = null;

        /** @var ModelsHumanVerification */
        $user = App::make(ModelsHumanVerification::class);

        if ($request->has("admin_bot")) {
            echo redirect(route("admin_bot", ["key" => $user->key]));
            return;
        }

        // Verify that we requested all necessary UA headers to base validation on that
        foreach ($user->getVerificators() as $verificator) {
            if ($verificator instanceof AgentVerification) {
                if (!$request->hasHeader("Sec-CH-UA-Full-Version-List") && !$request->has("ua")) {
                    $url = route("resultpage", array_merge($request->all(), ["ua" => 1]));
                    return redirect($url, 302, ["Accept-CH" => "Sec-CH-UA-Full-Version-List, Sec-CH-UA-Model, Sec-CH-UA-Platform-Version"]);
                }
            }
        }

        /**
         * Directly lock any user when there are many not whitelisted accounts on this IP
         * Only applies when the user itself is not whitelisted.
         * Also applies RefererLock from above
         */
        $user->checkGroupLock();


        # If the user is locked we will force a Captcha validation
        if ($user->isLocked()) {
            $user->saveUser();
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            $this->logCaptcha($request, $user);
            echo redirect()->route('captcha_show', ["url" => URL::full(), "key" => $user->key]); // TODO uncomment
            return;
        }

        $user->addQuery();

        \App\PrometheusExporter::HumanVerificationSuccessfull();
        \app()->make(QueryTimer::class)->observeEnd(self::class);
        return $next($request);
    }

    private function logCaptcha(\Illuminate\Http\Request $request, ModelsHumanVerification $user)
    {
        $log = [
            now()->format("Y-m-d H:i:s"),
            $request->input("eingabe"),
            "js=" . \app()->make(SearchSettings::class)->javascript_enabled,
        ];
        $locked_verificators = array();
        foreach ($user->getVerificators() as $verificator) {
            if ($verificator->isLocked()) {
                $locked_verificator = $verificator::class;
                $locked_verificator = substr($locked_verificator, \strrpos($locked_verificator, "\\") + 1);
                $locked_verificators[] = $locked_verificator;
            }
        }
        if (!empty($locked_verificators)) {
            $log[] = "verificators=" . implode(",", $locked_verificators);
        }
        $search_settings = \app()->make(SearchSettings::class);
        $log[] = "bv_key=" . $search_settings->bv_key;
        $file_path = \storage_path("logs/metager/captcha_show.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
    }
}