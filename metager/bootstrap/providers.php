<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    Mcamara\LaravelLocalization\LaravelLocalizationServiceProvider::class,
    App\Providers\MetaGerProvider::class,
    App\Providers\AuthorizationServiceProvider::class,
    Jenssegers\Agent\AgentServiceProvider::class,
    Mews\Captcha\CaptchaServiceProvider::class,
    App\Providers\SearchSettingsProvider::class,
];