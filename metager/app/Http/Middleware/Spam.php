<?php

namespace App\Http\Middleware;

use App\Models\HumanVerification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class Spam
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
        if (app('App\Models\Key')->getStatus()) {
            return $next($request);
        }
        # Check for recent Spams
        $eingabe = $request->input('eingabe');
        $spams = Redis::lrange("spam", 0, -1);

        $spam = false;

        foreach ($spams as $spam) {
            if (\preg_match("/" . $spam . "/", $eingabe)) {
                $spam = true;
                break;
            }
        }

        if ($spam === true) {
            $human_verification = \app()->make(HumanVerification::class);
            $human_verification->lockUser();
        }

        return $next($request);
    }
}
