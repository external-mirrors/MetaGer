<?php

use App\Http\Controllers\HumanVerification;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

Route::post('img/cat.png', 'HumanVerification@remove');
Route::get('verify/metager', [HumanVerification::class, 'captchaShow'])->name('captcha_show')->middleware(["throttle:humanverification"]);
Route::post('verify/metager', [HumanVerification::class, 'captchaSolve'])->name('captcha_solve')->middleware(["throttle:humanverification"]);;
Route::get('r/metager/{mm}/{pw}/{url}', ['as' => 'humanverification', 'uses' => 'HumanVerification@removeGet']);
Route::post('img/dog.jpg', [HumanVerification::class, 'whitelist']);
Route::get('index.css', [HumanVerification::class, 'verificationCssFile']);
Route::get('index.js', [HumanVerification::class, 'verificationJsFile']);
Route::get('metager.png', [HumanVerification::class, 'verificationImage'])->name("bv_verificationimage");
