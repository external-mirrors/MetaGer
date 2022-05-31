<?php

use App\Http\Controllers\HumanVerification;
use Illuminate\Support\Facades\Redis;

Route::post('img/cat.png', 'HumanVerification@remove');
Route::get('verify/metager', [HumanVerification::class, 'captchaShow'])->name('captcha_show')->middleware(["throttle:humanverification"]);
Route::post('verify/metager', [HumanVerification::class, 'captchaSolve'])->name('captcha_solve')->middleware(["throttle:humanverification"]);;
Route::get('r/metager/{mm}/{pw}/{url}', ['as' => 'humanverification', 'uses' => 'HumanVerification@removeGet']);
Route::post('img/dog.jpg', [HumanVerification::class, 'whitelist']);
Route::get('index.css', [HumanVerification::class, 'browserVerification']);
Route::get('index.js', function (Request $request) {
    $key = $request->input("id", "");

    // Verify that key is a md5 checksum
    if (!preg_match("/^[a-f0-9]{32}$/", $key)) {
        abort(404);
    }

    Redis::connection(config('cache.stores.redis.connection'))->rpush("js" . $key, true);
    Redis::connection(config('cache.stores.redis.connection'))->expire($key, 30);

    return response("", 200)->header("Content-Type", "application/javascript");
});
