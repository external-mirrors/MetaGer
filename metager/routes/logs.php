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
Route::get("api", [LogsApiController::class, "logsApi"])->name("logs:api");

Route::middleware(LogsAuthentication::class)->group(function () {
    Route::get("/", [LogsApiController::class, "overview"])->name("logs:overview");
    Route::post("update-invoice-data", [LogsApiController::class, "updateInvoiceData"])->name("logs:update_invoice_data");
    Route::get("abo", [LogsApiController::class, "showAbo"])->name("logs:abo");
    Route::get("nda", [LogsApiController::class, "nda"])->name("logs:nda");
    Route::post("abo", [LogsApiController::class, "createAbo"]);
    Route::post("access-key", [LogsApiController::class, "createAccessKey"])->name("logs:access-key");
    Route::post("access-key-delete", [LogsApiController::class, "deleteAccessKey"])->name("logs:access-key-delete");
});