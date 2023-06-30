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
            App::setFallbackLocale("en");
        } else {
            App::setLocale("de-DE");
            App::setFallbackLocale("de");
        }

        $locale_path = LaravelLocalization::setLocale();
        $locale = LaravelLocalization::getCurrentLocale();

        // Our locale includes the requested region however our translated strings are not differentiating regions
        // We need to define a fallback locale for each regional locale to just use the language part stripping the region
        if (\preg_match("/^([a-zA-Z]{2,5})-[a-zA-Z]{2,5}$/", $locale, $matches)) {
            // Check if translations exist
            $path = lang_path($matches[1]);
            if (file_exists($path)) {
                App::setFallbackLocale($matches[1]);
            } else {
                App::setFallbackLocale("en");
            }
        }

        return $locale_path;
    }

    /**
     * Extracts the language part from our current locale
     * 
     * @return string language (i.e. de,en,es,...)
     */
    public static function getLanguage()
    {
        $current_locale = LaravelLocalization::getCurrentLocale();
        if (\preg_match("/^([a-zA-Z]+)/", $current_locale, $matches)) {
            $current_locale = $matches[1];
        }
        return $current_locale;
    }

    /**
     * Extracts the region part from our current locale
     * 
     * @return string region (i.e. de,us,...)
     */
    public static function getRegion()
    {
        $current_region = LaravelLocalization::getCurrentLocale();
        if (\preg_match("/([a-zA-Z]+)$/", $current_region, $matches)) {
            $current_region = $matches[1];
        }
        return $current_region;
    }

    /**
     * Returns the supported Locales grouped by language and sorted by native name within the group
     */
    public static function getLanguageSelectorLocales()
    {
        $locales = [];

        foreach (LaravelLocalization::getSupportedLocales() as $locale => $locale_details) {
            if (\preg_match("/^([a-zA-Z]+)-/", $locale, $matches)) {
                $locales[$matches[1]][$locale] = $locale_details["native"];
            }
        }

        // Sort languages
        \ksort($locales);

        // Sort locales in the languages
        foreach ($locales as $language => &$tmp_locales) {
            ksort($tmp_locales);
        }

        return $locales;
    }
}