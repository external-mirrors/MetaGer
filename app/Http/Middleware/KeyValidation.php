<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use App\Models\Key;

class KeyValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, Key $key)
    {   
        if(isset($key) && $key->getStatus()) {
            return $next($request);
        } elseif(isset($key) && !$key->getStatus()) {
            if($request->filled('key')){
                return redirect($request->except('key'));
            } else {
                Cookie::queue('key', '', 0, '/', null, false, false);
                return redirect($request);
            }
        } else {
            return redirect($request);
        }
    }
}
