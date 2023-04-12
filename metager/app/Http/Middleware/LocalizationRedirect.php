<?php

namespace App\Http\Middleware;

use Closure;
use LaravelLocalization;
use URL;

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
        $host = $request->getHost();

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
            "metager3.de"
        ];

        if (app()->environment("local")) {
            $allowed_hostnames[] = "nginx";
            // Allow ngrok aswell under local env
            if (\preg_match("/\.ngrok-free\.app$/", $host)) {
                $allowed_hostnames[] = $host;
            }
        }

        $required_hostname = "metager.de";
        if (\stripos($locale, "de") === 0) {
            $allowed_hostnames[] = "metager.de";
        } elseif (\stripos($locale, "en") === 0) {
            $allowed_hostnames[] = "metager.org";
            $required_hostname = "metager.org";
        }

        // Allow the MetaGer review apps aswell
        if (\preg_match("/\.review\.metager\.de$/", $host)) {
            $allowed_hostnames[] = $host;
        }

        $url = URL::full();
        if ($host !== $required_hostname && !\in_array($host, $allowed_hostnames) && preg_match("/^(https?:\/\/[^\/]+)(.*)/", $url, $matches)) {
            $new_host = \str_replace($host, $required_hostname, $matches[1]);
            $new_url = $new_host . $matches[2];
            return redirect($new_url);
        }

        // If you switch languages between our domains (metager.de/metager.es/metager.org) a language parameter will be added 
        // allthough the language already is default for that domain (de-DE for metager.de, en-US for metager.org, es-ES for metager.es)
        $matched_host_default_locales = [
            "metager.de" => "de-DE",
            "metager.org" => "en-US",
            "metager.es" => "es-ES",
        ];

        if (\array_key_exists($host, $matched_host_default_locales) && request()->segment(1) === $matched_host_default_locales[$host]) {
            // Check if the user would automatically be redirected back from the base domain
            // If so we leave the locale in the path
            // Auto redirect only happens for regions in the same language as the domain
            $base_language = $matched_host_default_locales[$host];
            $base_language = \preg_replace("/^([a-zA-Z]+).*/", "$1", $base_language);

            $supported_locales_and_regionals = LaravelLocalization::getSupportedLocales();
            $supported_locales_and_regionals = \array_map(function ($locale) {
                return $locale["regional"];
            }, $supported_locales_and_regionals);
            $supported_locales_and_regionals = \array_flip($supported_locales_and_regionals);
            $supported_locales_and_regionals = array_filter($supported_locales_and_regionals, function ($key) use ($base_language) {
                if (stripos($key, $base_language) === 0) {
                    return true;
                }
                return false;
            }, \ARRAY_FILTER_USE_KEY);
            $supported_locales = \array_keys($supported_locales_and_regionals);
            $auto_locale = request()->getPreferredLanguage($supported_locales);
            if (!array_key_exists($auto_locale, $supported_locales_and_regionals) || $matched_host_default_locales[$host] === $supported_locales_and_regionals[$auto_locale]) {
                $new_url = LaravelLocalization::getLocalizedUrl(null, request()->getRequestUri());
                return redirect($new_url);
            }
        }

        return $next($request);
    }
}