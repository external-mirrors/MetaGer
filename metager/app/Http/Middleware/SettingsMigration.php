<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SettingsMigration
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
        // We migrate settings from cookie path /meta/ to root
        // because the new keymanager needs to have access to the metager settings to generate accurate setting links
        foreach (\Cookie::get() as $key => $value) {
            \Cookie::queue(\Cookie::forget($key, "/meta/"));
            \Cookie::queue(\Cookie::forever($key, $value, "/"));
        }
        return $next($request);
    }
}