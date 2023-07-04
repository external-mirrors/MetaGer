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
        // Ignore healthchecks
        if (request()->is(['metrics', 'health-check/*'])) {
            return;
        }
        /**
         * metager.org is our english Domain
         * We will change the Locale to en
         */
        $host = request()->getHost();
        $locale = "de-DE";
        $language = "de";
        if ($host === "metager.org") {
            $locale = "en-US";
            $language = "en";
        }

        $path_locale = request()->segment(1);

        if (!preg_match("/^[a-z]{2}-[A-Z]{2}$/", $path_locale) || !in_array($path_locale, LaravelLocalization::getSupportedLanguagesKeys())) {
            // There is no locale set in the path: Guess a good locale
            $locale = self::GET_PREFERRED_LOCALE($locale);
            $path_locale = ""; // There will be no prefix for the routes
            // Update default Locale so it can be stripped from the path
            config(["app.locale" => $locale, "laravellocalization.localesMapping" => [$locale => "de-DE"]]);
        } else {
            $locale = $path_locale;
        }
        App::setLocale($locale);

        // Our locale includes the requested region however our translated strings are not differentiating regions
        // We need to define a fallback locale for each regional locale to just use the language part stripping the region
        if (\preg_match("/^([a-zA-Z]{2,5})-[a-zA-Z]{2,5}$/", $locale, $matches)) {
            // Check if translations exist
            $path = lang_path($matches[1]);
            if (file_exists($path)) {
                App::setFallbackLocale($matches[1]);
            } else {
                App::setFallbackLocale($language);
            }
        }
        LaravelLocalization::setLocale($locale);

        return $path_locale;
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

    /**
     * Returns an array of available Locales in the format xx_XX
     *
     * @param string $default Default Locale if no matches were found
     *
     * @return string
     */
    public static function GET_PREFERRED_LOCALE($default = null)
    {
        $default = str_replace("-", "_", $default);
        $regional_locales = [];
        $available_locales = LaravelLocalization::getSupportedLocales();
        foreach ($available_locales as $locale => $locale_data) {
            $regional_locales[] = $locale_data["regional"];
        }

        // Add some two letter country codes to the list
        $two_letter_locales = [
            "de" => "de_DE",
            "en" => "en_US",
            "es" => "es_ES",
            "en_GB" => "en_UK",
        ];
        $regional_locales = array_merge($regional_locales, array_keys($two_letter_locales));

        // Make sure default locale is at array index 0 of available locales
        if ($default !== null) {
            if (in_array($default, $regional_locales)) {
                $regional_locales = array_diff($regional_locales, [$default]);
            }
            array_unshift($regional_locales, $default);
        }

        $preferred_locale = request()->getPreferredLanguage($regional_locales);

        if (in_array($preferred_locale, array_keys($two_letter_locales))) {
            $preferred_locale = $two_letter_locales[$preferred_locale];
        }

        return str_replace("_", "-", $preferred_locale);
    }
}