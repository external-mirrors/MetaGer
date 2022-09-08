<?php

namespace App;

use App;
use LaravelLocalization;

/**
 * Applies our custom localization rules including localized domain names
 * 
 */
class Localization
{
    public static function setLocale(string $locale = null)
    {
        /**
         * metager.org is our english Domain
         * We will change the Locale to en
         */
        $host = request()->header("X_Forwarded_Host", "");
        if (empty($host)) {
            $host = request()->header("Host", "");
        }

        if (stripos($host, "metager.org") !== false) {
            App::setLocale("en-US");
        } else if (stripos($host, "metager.es") !== false) {
            App::setLocale("es-ES");
        } else {
            App::setLocale("de-DE");
        }

        $locale_path = LaravelLocalization::setLocale();
        $locale = LaravelLocalization::getCurrentLocale();

        // Our locale includes the requested region however our translated strings are not differentiating regions
        // We need to define a fallback locale for each regional locale to just use the language part stripping the region
        if (\preg_match("/^([a-zA-Z]{2,5})-[a-zA-Z]{2,5}$/", $locale, $matches)) {
            App::setLocale($matches[1]);
            $locale = config("app.locale");
        }

        return $locale_path;
    }
}
