<?php
use App\Http\Controllers\LogsApiController;
use App\Http\Middleware\LogsAuthentication;
use App\Mail\LogsLoginCode;

/**
 * Group of Routes to provide access
 * to MetaGer Logs
 */

Route::get("login", [LogsApiController::class, 'login'])->name("logs:login");
Route::post("login", [LogsApiController::class, "login_post"])->middleware("throttle:logs_login")->name("logs:login:post");

Route::middleware(LogsAuthentication::class)->group(function () {
    Route::get("/", [LogsApiController::class, "overview"])->name("logs:overview");
    Route::post("update-invoice-data", [LogsApiController::class, "updateInvoiceData"])->name("logs:update_invoice_data");
    Route::get("abo", [LogsApiController::class, "createAbo"])->name("logs:abo");
    Route::get("nda", [LogsApiController::class, "nda"])->name("logs:nda");
});