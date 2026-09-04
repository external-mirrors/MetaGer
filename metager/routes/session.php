<?php
use App\Http\Controllers\AdminInterface;
use App\Http\Controllers\AssocController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\LogsApiController;
use App\Http\Controllers\MembershipController;
use App\Http\Middleware\AdminAuthenticate;
use App\Mail\LogsLoginCode;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

# In this File we collect all routes which require a session or other cookies to be active
Route::get('login', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "login"])->name('keycloak.login');
Route::get('logout', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "logout"])->name('keycloak.logout');
Route::get('callback', [Vizir\KeycloakWebGuard\Controllers\AuthController::class, "callback"])->name('keycloak.callback');

/**
 * Authentication is disabled in local environments but kept for
 * development/production — decided inside AdminAuthenticate, per request,
 * not here. Routes are cached in production and the test job runs against a
 * warm cache built from a copy of the production .env (see CLAUDE.md), so an
 * App::environment() check at this point in the file would be baked into the
 * cache at build time and stop varying afterwards — every /admin/* route
 * would keep whatever middleware was decided then, regardless of the
 * environment actually serving the request later.
 */
Route::group(['middleware' => [StartSession::class, AdminAuthenticate::class], 'prefix' => 'admin'], function () {
    Route::match(["get", "post"], "logs", [LogsApiController::class, "admin"])->name("logs:admin");
    Route::get("membership", [MembershipController::class, "adminIndex"])->name("membership_admin_overview");
    Route::get("membership/test", [MembershipController::class, "test"]);
    Route::get("membership/reduction", [MembershipController::class, "adminMembershipReduction"])->name("membership_admin_reduction");
    Route::post("membership/reduction/deny", [MembershipController::class, "adminMembershipReductionDeny"])->name("membership_admin_reduction_deny");
    Route::post("membership/reduction/accept", [MembershipController::class, "adminMembershipReductionAccept"])->name("membership_admin_reduction_accept");
    Route::post("membership/accept", [MembershipController::class, "adminAccept"])->name("membership_admin_accept");
    Route::post("membership/deny", [MembershipController::class, "adminDeny"])->name("membership_admin_deny");
    Route::get("assoc/members", [AssocController::class, "members"])->name("assoc_admin_members");
    Route::get("assoc/members/{type}/{id}", [AssocController::class, "member"])->name("assoc_admin_member");
    Route::get("assoc/households", [AssocController::class, "households"])->name("assoc_admin_households");
    Route::get("assoc/households/{id}", [AssocController::class, "household"])->name("assoc_admin_household");
    Route::get("assoc/bank-statements", [BankStatementController::class, "index"])->name("assoc_admin_bank_statements");
    Route::get("assoc/bank-statements/{id}", [BankStatementController::class, "show"])->name("assoc_admin_bank_statement");
    Route::post("assoc/bank-statements/{id}/match", [BankStatementController::class, "match"])->name("assoc_admin_bank_statement_match");
    Route::post("assoc/bank-statements/rematch", [BankStatementController::class, "rematch"])->name("assoc_admin_bank_statements_rematch");
    Route::get("logs/mail", [LogsApiController::class, "mail_logincode"]);
    Route::get('fpm-status', [AdminInterface::class, "getFPMStatus"])->name("fpm-status");
    Route::get('count', 'AdminInterface@count');
    Route::get('count/count-data', [AdminInterface::class, 'getCountData']);
    Route::get('timings', 'MetaGerSearch@searchTimings');
    Route::get('engine/stats.json', 'AdminInterface@engineStats');
    Route::get(
        'ip',
        function (Request $request) {
            dd($request->ip(), $_SERVER["AGENT"], $request->headers);
        }
    );
});