<?php

namespace App\Providers;

use Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider as ServiceProvider;

/**
 * The localization package, minus its route-caching commands.
 *
 * The package replaces `route:cache`, `route:clear` and `route:list` with
 * per-locale versions. They exist to work around a route table that differs per
 * locale — `route:cache` writes one 284KB cache file *per supported locale*, and
 * `LoadsTranslatedCachedRoutes` picks the right one at boot.
 *
 * Our route table no longer differs per locale: `ResolveLocale` takes the
 * segment off the request before matching and `URL::formatPathUsing` puts it
 * back on generated URLs, so there is exactly one table. Twenty-one byte-identical
 * cache files is the harmless half of keeping the overrides. The other half is
 * that `route:list` has been unusable for as long as they have been installed —
 * it fails with `The "locale" argument does not exist`, because the package's
 * subclass predates Laravel's rewrite of that command.
 *
 * Everything else the package does — the locale table, `getCurrentLocale()`,
 * `getSupportedLocales()` — is still in use and still comes from here.
 */
class LaravelLocalizationServiceProvider extends ServiceProvider
{
    protected function registerCommands()
    {
        // Deliberately empty; Laravel's own route commands are the right ones.
    }
}
