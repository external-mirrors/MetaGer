<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AllowLocalOnly
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
        // Only allow access to metrics from within our network
        $ip = $request->ip();
        $allowedNetworks = [
            "10.",
            "172.",
            "192.",
            "127.0.0.1",
        ];

        $allowed = false;
        foreach ($allowedNetworks as $part) {
            if (stripos($ip, $part) === 0) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            abort(401);
        }
        return $next($request);
    }
}
