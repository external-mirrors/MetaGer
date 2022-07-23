<?php

namespace App\Http\Middleware;

use App;
use App\Models\Verification\HumanVerification as ModelsHumanVerification;
use Cache;
use Closure;
use Cookie;
use Log;
use URL;
use App\QueryTimer;
use App\SearchSettings;

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

        if (!$should_skip && !config("metager.metager.botprotection.enabled") || app('App\Models\Key')->getStatus()) {
            $should_skip = true;
        }

        if ($should_skip) {
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return $next($request);
        }

        // The specific user
        $user = null;
        $update = true;

        /** @var ModelsHumanVerification */
        $user = App::make(ModelsHumanVerification::class);
        $search_settings = \app()->make(SearchSettings::class);

        /**
         * Directly lock any user when there are many not whitelisted accounts on this IP
         * Only applies when the user itself is not whitelisted.
         * Also applies RefererLock from above
         */
        $user->checkGroupLock();


        # If the user is locked we will force a Captcha validation
        if ($user->isLocked()) {
            $user->saveUser();
            \App\Http\Controllers\HumanVerification::logCaptcha($request);
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            $this->logCaptcha($request); // TODO remove
            return redirect()->route('captcha_show', ["url" => URL::full()]); // TODO uncomment
        }

        $user->addQuery();

        \App\PrometheusExporter::HumanVerificationSuccessfull();
        \app()->make(QueryTimer::class)->observeEnd(self::class);
        return $next($request);
    }

    // TODO remove function
    private function logCaptcha(\Illuminate\Http\Request $request)
    {
        $log = [
            now()->format("Y-m-d H:i:s"),
            $request->input("eingabe"),
            "js=" . \app()->make(SearchSettings::class)->javascript_enabled,
        ];
        $file_path = \storage_path("logs/metager/captcha.csv");
        $fh = fopen($file_path, "a");
        try {
            \fputcsv($fh, $log);
        } finally {
            fclose($fh);
        }
        // Temporary Log to test new functionality. Will be removed again soon
    }
}
