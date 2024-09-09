<?php
use App\Http\Controllers\AdvertisingController;

/**
 * Group of routes to provide the advertiser portal
 */

Route::group(["prefix" => "advertising"], function () {
    Route::get("/", [AdvertisingController::class, "overview"])->name("advertising_index");
});