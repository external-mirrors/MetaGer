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
        if ($host === "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion" || $request->is(['metrics', 'health-check/*'])) {
            return $next($request);
        }

        // Redirect from v2 onion to v3 onion
        if ($host === "b7cxf4dkdsko6ah2.onion") {
            return redirect("http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion");
        }

        $allowed_hostnames = [
            "127.0.0.1",
            "localhost",
        ];
        $required_hostname = "metager.de";
        if (\stripos($locale, "de") === 0) {
            $allowed_hostnames[] = "metager.de";
        } elseif (\stripos($locale, "en") === 0) {
            $allowed_hostnames[] = "metager.org";
            $required_hostname = "metager.org";
        } elseif (\stripos($locale, "es") === 0) {
            $allowed_hostnames[] = "metager.es";
            $required_hostname = "metager.es";
        }

        $url = url()->full();
        if ($host !== $required_hostname && !\in_array($host, $allowed_hostnames) && preg_match("/^(https?:\/\/[^\/]+)(.*)/", $url, $matches)) {
            $new_host = \str_replace($host, $required_hostname, $matches[1]);
            $new_url = $new_host . $matches[2];
            return redirect($new_url);
        }

        // If you switch languages between our domains (metager.de/metager.es/metager.org) a language parameter will be added 
        // allthough the language already is default for that domain (de-DE for metager.de, en-US for metager.org, es-ES for metager.es)
        $matched_host_default_locales = [
            "metager.de"    => "de-DE",
            "metager.org"   => "en-US",
            "metager.es"    =>  "es-ES",
        ];

        if (\array_key_exists($host, $matched_host_default_locales) && request()->segment(1) === $matched_host_default_locales[$host]) {
            $new_url = LaravelLocalization::getLocalizedUrl(null, request()->getRequestUri());
            return redirect($new_url);
        }

        return $next($request);
    }
}
