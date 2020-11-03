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
    public function handle($request, Closure $next)
    {   
        if(isset($request->key)){
            $pKey = new Key($request->key);
        }
        
        if(Cookie::get('key')){
            $cKey = new Key($request->key);
        }

        if($pKey->getStatus() || $cKey->getStatus())
        return $next($request);
    }
}
