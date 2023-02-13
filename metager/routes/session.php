<?php
use App\Http\Controllers\AdminInterface;
use App\Http\Controllers\HumanVerification;

# In this File we collect all routes which require a session or other cookies to be active
Route::get('login', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "login"])->name('keycloak.login');
Route::get('logout', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "logout"])->name('keycloak.logout');
Route::get('callback', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "callback"])->name('keycloak.callback');

$auth_middleware = [];
/**
 * Disable Authentication in local environments
 * but keep it enabled for development/production
 */
if (in_array(App::environment(), ["development", "production"])) {
    $auth_middleware[] = "keycloak-web";
}

Route::group(['middleware' => $auth_middleware, 'prefix' => 'admin'], function () {
    Route::get('fpm-status', [AdminInterface::class, "getFPMStatus"])->name("fpm-status");
    Route::get('count', 'AdminInterface@count');
    Route::get('count/count-data', [AdminInterface::class, 'getCountData']);
    Route::get('timings', 'MetaGerSearch@searchTimings');
    Route::get('engine/stats.json', 'AdminInterface@engineStats');
    Route::get('check', 'AdminInterface@check');
    Route::get(
        'ip',
        function (Request $request) {
            dd($request->ip(), $_SERVER["AGENT"]);
        }
    );
    Route::get('bot', 'HumanVerification@botOverview')->name("admin_bot");
    Route::post('bot', 'HumanVerification@botOverviewChange');
    Route::get('bv', [HumanVerification::class, 'bv']);
    Route::group(
        ['prefix' => 'spam'],
        function () {
            Route::get('/', 'AdminSpamController@index');
            Route::post('/', 'AdminSpamController@ban');
            Route::get('jsonQueries', 'AdminSpamController@jsonQueries');
            Route::post('queryregexp', 'AdminSpamController@queryregexp');
            Route::post('deleteRegexp', 'AdminSpamController@deleteRegexp');
        }
    );
    Route::get('stress', 'Stresstest@index');
    Route::get('stress/verify', 'Stresstest@index')->middleware('browserverification', 'humanverification');
    Route::get('adgoal', 'AdgoalTestController@index')->name("adgoal-index");
    Route::post('adgoal', 'AdgoalTestController@post')->name("adgoal-generate");
    Route::post('adgoal/generate-urls', 'AdgoalTestController@generateUrls')->name("adgoal-urls");

    Route::group(
        ['prefix' => 'affiliates'],
        function () {
            Route::get('/', 'AdgoalController@adminIndex');
            Route::get('/json/blacklist', 'AdgoalController@blacklistJson');
            Route::put('/json/blacklist', 'AdgoalController@addblacklistJson');
            Route::delete('/json/blacklist', 'AdgoalController@deleteblacklistJson');
            Route::get('/json/whitelist', 'AdgoalController@whitelistJson');
            Route::put('/json/whitelist', 'AdgoalController@addwhitelistJson');
            Route::delete('/json/whitelist', 'AdgoalController@deletewhitelistJson');
            Route::get('/json/hosts', 'AdgoalController@hostsJson');
            Route::get('/json/hosts/clicks', 'AdgoalController@hostClicksJson');
        }
    );
});