<?php

use App\Http\Middleware\AllowLocalOnly;
use App\Http\Middleware\ExternalImagesearch;
use App\Http\Middleware\HttpCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '144.76.113.134',
            '144.76.88.77',
            '167.233.15.225',
        ]);
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
        $middleware->remove([
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->removeFromGroup('web', [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->appendToGroup("web", [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \App\Http\Middleware\LocalizationRedirect::class,
        ]);
        $middleware->appendToGroup("humanverification_routes", [
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
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'humanverification' => \App\Http\Middleware\HumanVerification::class,
            'useragentmaster' => \App\Http\Middleware\UserAgentMaster::class,
            'browserverification' => \App\Http\Middleware\BrowserVerification::class,
            'spam' => \App\Http\Middleware\Spam::class,
            'allow-local-only' => AllowLocalOnly::class,
            'httpcache' => HttpCache::class,
            'externalimagesearch' => ExternalImagesearch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();