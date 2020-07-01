<?php

namespace App\Http\Middleware;

use Closure;
use LaravelLocalization;

class LocalizationRedirect
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
        $locale = LaravelLocalization::getCurrentLocale();
        $host = $request->getHttpHost();


        // We only redirect to the TLDs in the production version and exclude our onion domain
        if(env("APP_ENV", "") !== "production" || $host === "b7cxf4dkdsko6ah2.onion" || $request->is('metrics')){
            return $next($request);
        }

        $url = url()->full();
        $url = preg_replace("/^http:\/\//", "https://", $url);
        if($host !== "metager.de" && $locale == "de"){
            $url = str_replace($host, "metager.de", $url);
            $url = preg_replace("/^(https:\/\/[^\/]+)\/de/", "$1", $url);
            return redirect($url);
        }

        if($host !== "metager.es" && $locale == "es"){
            $url = str_replace($host, "metager.es", $url);
            $url = preg_replace("/^(https:\/\/[^\/]+)\/es/", "$1", $url);
            return redirect($url);
        }

        if($host !== "metager.org" && $locale == "en"){
            $url = str_replace($host, "metager.org", $url);
            $url = preg_replace("/^(https:\/\/[^\/]+)\/en/", "$1", $url);
            return redirect($url);
        }

        return $next($request);
    }
}
