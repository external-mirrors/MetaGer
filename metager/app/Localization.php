<?php

namespace App;

use App;
use Config;
use Cookie;
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
        $locale = "en-US";
        $language = "en";
        if ($host === "metager.de") {
            $locale = "de-DE";
            $language = "de";
        }
        $fallback_locale = $language;

        $legacy_path_locales = [
            "uk" => "en-GB",
            "ie" => "en-IE",
            "es" => "es-ES",
            "at" => "de-AT"
        ];

        $path_locale = request()->segment(1);

        $guessed_locale = self::GET_PREFERRED_LOCALE($locale);
        $default_locale = $locale;
        if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", $path_locale) || in_array($path_locale, LaravelLocalization::getSupportedLanguagesKeys())) {
            $locale = $path_locale;
        } else {
            if (array_key_exists($path_locale, $legacy_path_locales)) {
                $locale = $legacy_path_locales[$path_locale];
            } else {
                $path_locale = "";
            }
            // We will guess a locale only for metager.org or if the guessed locale is a german language
            // There is a lot of traffic on metager.de with a en_US agent and I don't know yet if that's
            // a misconfigured useragent or indeed the correct language setting
            if (request()->getHost() !== "metager.de" || strpos($guessed_locale, "de") === 0) {
                $locale = $guessed_locale;
            }
        }

        if (request()->getHost() !== "metager.de" || strpos($guessed_locale, "de") === 0) {
            $default_locale = $guessed_locale;
        }

        // Update default Locale so it can be stripped from the path
        config(["app.locale" => $locale, "app.default_locale" => $default_locale, "laravellocalization.localesMapping" => [$default_locale => ""]]);
        App::setLocale($locale);

        App::setFallbackLocale($fallback_locale);
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
            "en_UK" => "en_GB",
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