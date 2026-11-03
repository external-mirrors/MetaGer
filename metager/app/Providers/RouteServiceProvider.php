<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

/**
 * The route table is a constant.
 *
 * It used to be built per request: each group below carried
 * `'prefix' => Localization::setLocale()`, so the URIs the router knew about
 * depended on the locale segment of the URL being answered. That made
 * `route:cache` impossible — there is no one table to cache — and it made
 * `route()` produce different URLs for routes declared in different files,
 * because only the files loaded inside a prefixed group ever got the prefix.
 *
 * The locale is now stripped from the request by `ResolveLocale` before route
 * matching and put back by `AppServiceProvider`'s `URL::formatPathUsing` hook.
 * Nothing here needs to know it exists.
 *
 * `Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes` went with
 * it: it worked around the per-request table by writing one cache file per
 * locale and picking one at boot. With a single table there is nothing to pick.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        // First, so nothing in the web routes can shadow them.
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapSessionRoutes();

        $this->mapEnableCookieRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Server-to-server JSON APIs (routes/api.php).
     *
     * Deliberately outside the `web` group: nothing there applies to a caller
     * that authenticates with a bearer token and sends no cookies, and
     * LocalizationRedirect would relocate a request that forgot its Accept
     * header to another domain. Each route names its own authorization.
     *
     * Not the framework's `api` group either: its throttle keys on
     * `$request->user()`, which here would resolve the default guard for a
     * caller that is not a user. The one endpoint that needs a limit sets its
     * own (BlocklistCheckController).
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }

    /**
     * Define the "session" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapEnableCookieRoutes()
    {
        Route::group([
            'middleware' => 'enableCookies',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/cookie.php');
        });
    }

    /**
     * Define the "session" routes for the application.
     *
     * These routes can all set cookies.
     *
     * @return void
     */
    protected function mapSessionRoutes()
    {
        Route::group([
            'middleware' => 'session',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/session.php');
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}