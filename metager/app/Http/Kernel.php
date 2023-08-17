<?php

namespace App\Http;

use App\Http\Middleware\AllowLocalOnly;
use App\Http\Middleware\ExternalImagesearch;
use App\Http\Middleware\HttpCache;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
        /**
         * The application's global HTTP middleware stack.
         *
         * These middleware are run during every request to your application.
         *
         * @var array
         */
        protected $middleware = [
                \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
                \Illuminate\Http\Middleware\HandleCors::class,
                \App\Http\Middleware\TrustProxies::class,
                \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
                \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
                \App\Http\Middleware\TrimStrings::class,
                \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ];

        /**
         * The application's route middleware groups.
         *
         * @var array
         */
        protected $middlewareGroups = [
                'web' => [
                        \Illuminate\Routing\Middleware\SubstituteBindings::class,
                        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                        \App\Http\Middleware\LocalizationRedirect::class,
                ],

                'humanverification_routes' => [
                        \Illuminate\Routing\Middleware\SubstituteBindings::class,
                        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                        \App\Http\Middleware\LocalizationRedirect::class,
                ],

                'api' => [
                        \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
                        \Illuminate\Routing\Middleware\SubstituteBindings::class,
                ],

                'enableCookies' => [
                        \Illuminate\Routing\Middleware\SubstituteBindings::class,
                        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                        \App\Http\Middleware\LocalizationRedirect::class,
                ],

                'session' => [
                        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                        \Illuminate\Session\Middleware\StartSession::class,
                        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                        \Illuminate\Routing\Middleware\SubstituteBindings::class,
                        \App\Http\Middleware\LocalizationRedirect::class,
                ],
        ];

        /**
         * The application's middleware aliases.
         *
         * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
         *
         * @var array<string, class-string|string>
         */
        protected $middlewareAliases = [
                'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
                'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
                'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
                'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
                'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
                'can' => \Illuminate\Auth\Middleware\Authorize::class,
                'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
                'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
                'referer.check' => \App\Http\Middleware\RefererCheck::class,
                'humanverification' => \App\Http\Middleware\HumanVerification::class,
                'useragentmaster' => \App\Http\Middleware\UserAgentMaster::class,
                'browserverification' => \App\Http\Middleware\BrowserVerification::class,
                'spam' => \App\Http\Middleware\Spam::class,
                'keyvalidation' => \App\Http\Middleware\KeyValidation::class,
                'allow-local-only' => AllowLocalOnly::class,
                'httpcache' => HttpCache::class,
                'externalimagesearch' => ExternalImagesearch::class,
        ];
}