<?php

use App\Http\Controllers\HumanVerification;

Route::post('img/cat.png', 'HumanVerification@remove');
Route::get('img/logo.png', [HumanVerification::class, 'verificationJsFile']);
Route::get('verify/metager', [HumanVerification::class, 'captchaShow'])->name('captcha_show')->middleware(["throttle:humanverification"]);
Route::post('verify/metager', [HumanVerification::class, 'captchaSolve'])->name('captcha_solve')->middleware(["throttle:humanverification"]);;
Route::get('r/metager/{hv}/{pw}/{url}', ['as' => 'humanverification', 'uses' => 'HumanVerification@removeGet']);
Route::post('img/dog.jpg', [HumanVerification::class, 'whitelist']);
Route::get('index.css', [HumanVerification::class, 'verificationCssFile']);
Route::post('{mgv}/csp-report', [HumanVerification::class, 'verificationCSP'])->name("csp_verification");
