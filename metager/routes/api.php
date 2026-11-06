<?php

use App\Http\Controllers\BlocklistChangedController;
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
 * The blocklist service's change notice: sync MetaGer's list now.
 */
Route::post("api/blocklist-changed", BlocklistChangedController::class)->name("blocklist.changed");
