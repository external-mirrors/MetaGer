<?php

use App\Http\Middleware\AllowLocalOnly;
use App\Http\Middleware\ResolveLocale;
use App\Http\Middleware\HttpCache;
use App\Http\Middleware\Statistics;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;
use Predis\PredisException;
use Prometheus\Exception\StorageException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

return Application::configure(basePath: dirname(__DIR__))
    /**
     * `web:` is deliberately absent.
     *
     * `RouteServiceProvider::mapWebRoutes()` already loads `routes/web.php`,
     * inside a group whose prefix is `Localization::setLocale()` - the locale
     * segment this request actually carries. Naming the file here as well
     * registers every one of those routes a *second* time, unprefixed, and
     * `AppRouteServiceProvider` boots last, so the unprefixed copy wins the
     * name lookup: `route('suggest')` on `/en-US` returned `/suggest`, while
     * `route('settings')` - the same Blade file, the next line down, but
     * declared in `routes/cookie.php`, which is loaded only once - correctly
     * returned `/en-US/meta/settings`.
     *
     * That is not a cosmetic difference. An unprefixed URL is re-detected from
     * scratch by `LocalizationRedirect`, which for a user whose browser
     * language and chosen language disagree answers a cross-origin `302` to
     * the other domain. `fetch()` cannot follow it under our own
     * `connect-src 'self'`, so the start page's suggest endpoint threw, and
     * with it the form submit that waited on it.
     *
     * Leftover from the Laravel 11 skeleton migration: `withRouting(web:)` is
     * the modern way to register web routes, but it cannot express a
     * per-request prefix, which is exactly what the provider is here for.
     */
    ->withRouting(
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            TrustProxies::class,
            // After TrustProxies, because the locale decision still reads the
            // host and the host is only trustworthy once the proxy headers
            // have been. Before route matching, because it strips the locale
            // segment the static route table no longer contains.
            ResolveLocale::class,
        ]);
        $middleware->trustProxies(at: [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '144.76.113.134',
            '144.76.88.77',
            '167.233.15.225',
        ]);
        // PreventRequestForgery is Laravel 13's name for what used to be
        // VerifyCsrfToken. The removals below must name the *new* class:
        // VerifyCsrfToken survives only as a deprecated alias, and removal
        // matches on the exact class name, so naming the alias silently leaves
        // the real middleware in the stack. Web routes run without StartSession,
        // so it then fatals on $request->session() — a 500 on every page.
        $middleware->remove([
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);
        $middleware->removeFromGroup('web', [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->appendToGroup("web", [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \App\Http\Middleware\LocalizationRedirect::class,
        ]);
        $middleware->appendToGroup("api", [
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        $middleware->appendToGroup("enableCookies", [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \App\Http\Middleware\LocalizationRedirect::class,
        ]);
        $middleware->appendToGroup("session", [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LocalizationRedirect::class,
        ]);
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'auth.events' => \App\Http\Middleware\EventAuthorization::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'allow-local-only' => AllowLocalOnly::class,
            'httpcache' => HttpCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);

        /**
         * Redis backs almost everything a search does — the fetch queue, the
         * wait for results, the load-more cache — so an outage here isn't a
         * case for a partial degrade, it's "the site can't do its job right
         * now." Two production incidents (GlitchTip METAGER-I/L,
         * METAGER-K/H/E) reached call sites that had never been written to
         * expect either exception type and 500ed. This turns both into the
         * same, deliberate answer: 503 with a short Retry-After, which
         * resources/views/errors/503.blade.php pairs with a zero-JS
         * meta-refresh — Redis outages of this kind are typically over
         * within seconds, so telling the browser to just try again shortly
         * is more useful to a user than a dead-end error page.
         */
        $exceptions->renderable(function (PredisException|StorageException $e) {
            throw new ServiceUnavailableHttpException(5, $e->getMessage(), $e);
        });
    })->create();