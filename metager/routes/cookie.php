<?php
use App\Http\Controllers\SettingsController;


Route::get('meta/key', function () {
    return redirect(LaravelLocalization::getLocalizedUrl(null, "/keys/key/enter"));
})->name('keyindex');

Route::group(
    [
        'prefix' => 'meta/settings',
    ],
    function () {
        Route::get('/', 'SettingsController@index')->name('settings');
        Route::post('de', 'SettingsController@disableSearchEngine')->name('disableEngine');
        Route::post('ee', 'SettingsController@enableSearchEngine')->name('enableEngine');
        Route::post('ex', [SettingsController::class, 'enableExternalSearchProvider'])->name('enableExternalProvider');
        Route::post('ef', 'SettingsController@enableFilter')->name('enableFilter');
        Route::post('es', 'SettingsController@enableSetting')->name('enableSetting');
        Route::post('ds', 'SettingsController@deleteSettings')->name('deleteSettings');
        Route::post('nb', 'SettingsController@newBlacklist')->name('newBlacklist');
        Route::post('db', 'SettingsController@deleteBlacklist')->name('deleteBlacklist');
        Route::post('cb', 'SettingsController@clearBlacklist')->name('clearBlacklist');

        # Route to show and delete all settings
        Route::get('all-settings', 'SettingsController@allSettingsIndex')->name('showAllSettings');
        Route::post('all-settings/removeOne', 'SettingsController@removeOneSetting')->name('removeOneSetting');
        Route::post('all-settings/removeAll', 'SettingsController@removeAllSettings')->name('removeAllSettings');
        Route::get('load-settings', 'SettingsController@loadSettings')->name('loadSettings');
    }
);