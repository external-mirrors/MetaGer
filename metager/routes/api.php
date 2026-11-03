<?php

use App\Http\Controllers\BlocklistCheckController;
use App\Http\Controllers\BlocklistController;
use App\Http\Middleware\CrmAuthorization;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Server-to-server APIs
|--------------------------------------------------------------------------
|
| Loaded by RouteServiceProvider::mapApiRoutes, outside the `web` group — see
| the comment there. No closures: routes are cached in production.
|
*/

/**
 * The blocklist, for suma-crm. docs/blocklist-api.md is the contract.
 *
 * `{id}` is not constrained to digits on purpose: a constraint would let
 * `/api/blocklist/abc` miss the route entirely and answer with the framework's
 * 404 instead of the contract's, and without passing the token check first.
 */
Route::prefix("api/blocklist")->middleware(CrmAuthorization::class)->group(function () {
    Route::get("/", [BlocklistController::class, "index"])->name("blocklist.index");
    Route::post("/", [BlocklistController::class, "store"])->name("blocklist.store");
    Route::post("test", [BlocklistController::class, "test"])->name("blocklist.test");
    Route::post("check", [BlocklistCheckController::class, "check"])->name("blocklist.check");
    Route::get("{id}", [BlocklistController::class, "show"])->name("blocklist.show");
    Route::patch("{id}", [BlocklistController::class, "update"])->name("blocklist.update");
    Route::delete("{id}", [BlocklistController::class, "destroy"])->name("blocklist.destroy");
});
