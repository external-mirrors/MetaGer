<?php

return [

    // Uncomment the languages that your site supports - or add new ones.
    // These are sorted by the native name, which is the order you might show them in a language selector.
    // Regional languages are sorted by their base language, so "British English" sorts as "English, British"
    'supportedLocales' => [
        'default' => ['name' => 'Default Locale', 'script' => 'Latn', 'native' => 'Default', 'regional' => ''],
        'de-CH' => ['name' => 'German (Switzerland)', 'script' => 'Latn', 'native' => 'Deutsch (Schweiz)', 'regional' => 'de_CH'],
        'de-DE' => ['name' => 'German (Germany)', 'script' => 'Latn', 'native' => 'Deutsch (Deutschland)', 'regional' => 'de_DE'],
        'de-AT' => ['name' => 'German (Austria)', 'script' => 'Latn', 'native' => 'Deutsch (Österreich)', 'regional' => 'de_AT'],
        'da-DK' => ['name' => 'Danish (Denmark)', 'script' => 'Latn', 'native' => 'Dansk (Danmark)', 'regional' => 'da_DK'],
        'en-GB' => ['name' => 'English (Great Britain)', 'script' => 'Latn', 'native' => 'English (Great Britain)', 'regional' => 'en_GB'],
        'en-US' => ['name' => 'English (US)', 'script' => 'Latn', 'native' => 'English (USA)', 'regional' => 'en_US'],
        'en-IE' => ['name' => 'English (Ireland)', 'script' => 'Latn', 'native' => 'English (Ireland)', 'regional' => 'en_IE'],
        'nl-NL' => ['name' => 'Dutch (Netherlands)', 'script' => 'Latn', 'native' => 'Nederlands (Nederland)', 'regional' => 'nl_NL'],
        'es-ES' => ['name' => 'Spanish (Spain)', 'script' => 'Latn', 'native' => 'Español (España)', 'regional' => 'es_ES'],
        'es-MX' => ['name' => 'Spanish (Mexico)', 'script' => 'Latn', 'native' => 'Español (México)', 'regional' => 'es_MX'],
        'pl-PL' => ['name' => 'Po (Portugal)', 'script' => 'Latn', 'native' => 'Polski (Polska)', 'regional' => 'pl_PL'],
        'fi-FI' => ['name' => 'Finnish (Finland)', 'script' => 'Latn', 'native' => 'Suomalainen (Suomi)', 'regional' => 'fi_FI'],
        'sv-SE' => ['name' => 'Swedish (Sweden)', 'script' => 'Latn', 'native' => 'Svenska (Sverige)', 'regional' => 'sv_SE'],
        'it-IT' => ['name' => 'Italian (Italy)', 'script' => 'Latn', 'native' => 'Italiano (Italia)', 'regional' => 'it_IT'],
        'fr-FR' => ['name' => 'French (France)', 'script' => 'Latn', 'native' => 'Français (France)', 'regional' => 'fr_FR'],
        'fr-CA' => ['name' => 'French (Canada)', 'script' => 'Latn', 'native' => 'Français (Canada)', 'regional' => 'fr_CA'],
    ],

    // Requires middleware `LaravelSessionRedirect.php`.
    //
    // Automatically determine locale from browser (https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language)
    // on first call if it's not defined in the URL. Redirect user to computed localized url.
    // For example, if users browser language is `de`, and `de` is active in the array `supportedLocales`,
    // the `/about` would be redirected to `/de/about`.
    //
    // The locale will be stored in session and only be computed from browser
    // again if the session expires.
    //
    // If false, system will take app.php locale attribute
    'useAcceptLanguageHeader' => true,

    // If `hideDefaultLocaleInURL` is true, then a url without locale
    // is identical with the same url with default locale.
    // For example, if `en` is default locale, then `/en/about` and `/about`
    // would be identical.
    //
    // If in addition the middleware `LaravelLocalizationRedirectFilter` is active, then
    // every url with default locale is redirected to url without locale.
    // For example, `/en/about` would be redirected to `/about`.
    // It is recommended to use `hideDefaultLocaleInURL` only in
    // combination with the middleware `LaravelLocalizationRedirectFilter`
    // to avoid duplicate content (SEO).
    //
    // If `useAcceptLanguageHeader` is true, then the first time
    // the locale will be determined from browser and redirect to that language.
    // After that, `hideDefaultLocaleInURL` behaves as usual.
    'hideDefaultLocaleInURL' => true,

    // If you want to display the locales in particular order in the language selector you should write the order here.
    //CAUTION: Please consider using the appropriate locale code otherwise it will not work
    //Example: 'localesOrder' => ['es','en'],
    'localesOrder' => [],

    //  If you want to use custom lang url segments like 'at' instead of 'de-AT', you can use the mapping to tallow the LanguageNegotiator to assign the descired locales based on HTTP Accept Language Header. For example you want ot use 'at', so map HTTP Accept Language Header 'de-AT' to 'at' (['de-AT' => 'at']).
    'localesMapping' => [
    ],

    // Locale suffix for LC_TIME and LC_MONETARY
    // Defaults to most common ".UTF-8". Set to blank on Windows systems, change to ".utf8" on CentOS and similar.
    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    // URLs which should not be processed, e.g. '/nova', '/nova/*', '/nova-api/*' or specific application URLs
    // Defaults to []
    'urlsIgnored' => ['/skipped'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],
];