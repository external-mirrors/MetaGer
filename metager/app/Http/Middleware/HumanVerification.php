<?php

namespace App\Http\Middleware;

use App;
use App\Models\HumanVerification as ModelsHumanVerification;
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
                    Cache::decrement($token);
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


        # A lot of automated requests are from websites that redirect users to our result page.
        # We will detect those requests and put a captcha
        $refererLock = $user->refererLock();


        /**
         * Directly lock any user when there are many not whitelisted accounts on this IP
         * Only applies when the user itself is not whitelisted.
         * Also applies RefererLock from above
         */
        if ((!$user->alone && $user->request_count_all_users >= 50 && !$user->isWhiteListed() && $user->not_whitelisted_accounts > $user->whitelisted_accounts) || $refererLock) {
            $user->lockUser();
        }

        # If the user is locked we will force a Captcha validation
        if ($user->isLocked()) {
            \App\Http\Controllers\HumanVerification::logCaptcha($request);
            \app()->make(QueryTimer::class)->observeEnd(self::class);
            return redirect()->route('captcha_show', ["url" => URL::full()]);
        }

        $user->addQuery();

        \App\PrometheusExporter::HumanVerificationSuccessfull();
        \app()->make(QueryTimer::class)->observeEnd(self::class);
        return $next($request);
    }
}
