<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;

class RemoveKey
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
        // Check if a wrong Key Cookie is set and if so remove it
        if(Cookie::has("key") && !app('App\Models\Key')->getStatus()){
            return redirect(route("removeCookie", ["ir" => url()->full()]));
        }
        return $next($request);
    }
}
