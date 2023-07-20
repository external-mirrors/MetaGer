<?php

namespace App\Providers;

use App\Localization;
use App\Models\Verification\HumanVerification;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    use \Mcamara\LaravelLocalization\Traits\LoadsTranslatedCachedRoutes;
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
        $this->mapWebRoutes();

        $this->mapSessionRoutes();

        $this->mapEnableCookieRoutes();

        $this->mapHumanVerificationRoutes();
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
            'prefix' => Localization::setLocale(),
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
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
            'prefix' => Localization::setLocale(),
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
            'prefix' => Localization::setLocale(),
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/session.php');
        });
    }

    /**
     * Define the "humanverification" routes for the application.
     *
     * These routes can all set cookies.
     *
     * @return void
     */
    protected function mapHumanVerificationRoutes()
    {
        Route::group([
            'middleware' => 'humanverification_routes',
            'prefix' => Localization::setLocale(),
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/humanverification.php');
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

        RateLimiter::for('humanverification', function (Request $request) {
            return Limit::perMinutes(5, 30)->by($request->input("key"));
        });
    }
}