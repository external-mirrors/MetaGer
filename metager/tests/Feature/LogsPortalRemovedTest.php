<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The Logs API customer portal is gone, and InvoiceNinja with it.
 *
 * /logs was a self-service account for researchers buying access to the
 * search query log: an e-mailed login code, an NDA, a subscription billed
 * through InvoiceNinja, and API keys for a `/logs/api` download. It never had
 * customers. Its billing crons were unscheduled in 938063400 because
 * `logs:create-invoice` failed every run, and the overview page could not even
 * render without an InvoiceNinja token, which no environment configured. What
 * finally removed it was a major bump of invoiceninja/sdk-php (MR !2587) for a
 * dependency only this portal used.
 *
 * Its tables (logs_user, logs_abo, logs_nda, logs_order, logs_access_key) are
 * left in the database: dropping them is a separate decision about the data
 * in them, not about the code.
 *
 * The search query log itself is a different feature that shares the name:
 * `logs:gather` drains it from Redis into `logs_partitioned`, `logs:truncate`
 * ages it out, and the admin count page reads it. That half stays.
 */
class LogsPortalRemovedTest extends TestCase
{
    public function testThePortalPagesAreGone(): void
    {
        foreach (["/logs", "/logs/login", "/logs/api", "/logs/abo", "/logs/nda", "/admin/logs", "/admin/logs/mail"] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    /**
     * A blade calling route() on a name nothing registers is a 500 at render
     * time, not a 404 — so the names have to be gone too.
     */
    public function testNoPortalRouteNameIsRegistered(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())
            ->map(fn($route) => $route->getName() ?? "")
            ->filter(fn($name) => str_starts_with($name, "logs:"));

        $this->assertEmpty($names->all(), "Logs portal routes are back: " . $names->implode(", "));
    }

    /**
     * On the files rather than through class_exists(), for the reason given in
     * AdvertisingRemovedTest: a stale classmap can still name a deleted class.
     */
    public function testThePortalCodeIsGone(): void
    {
        foreach ([
            "app/Http/Controllers/LogsApiController.php",
            "app/Http/Middleware/LogsAuthentication.php",
            "app/Models/Logs",
            "app/Models/Authorization/LogsAuthGuard.php",
            "app/Models/Authorization/LogsUser.php",
            "app/Providers/LogsUserProvider.php",
            "app/Mail/LogsLoginCode.php",
            "app/Console/Commands/LogsCreateInvoice.php",
            "app/Console/Commands/LogsCreateOrder.php",
            "routes/logs.php",
            "resources/views/logs",
            "config/metager/invoiceninja.php",
        ] as $path) {
            $this->assertFileDoesNotExist(base_path($path), "$path is back.");
        }
    }

    public function testInvoiceNinjaIsNoLongerADependency(): void
    {
        $composer = json_decode(file_get_contents(base_path("composer.json")), true);

        $this->assertArrayNotHasKey("invoiceninja/sdk-php", $composer["require"]);
        $this->assertArrayNotHasKey("logs", config("auth.guards"));
        $this->assertArrayNotHasKey("logs", config("auth.providers"));
    }

    public function testTheBillingCommandsAreGone(): void
    {
        $registered = array_keys(Artisan::all());

        $this->assertNotContains("logs:create-order", $registered);
        $this->assertNotContains("logs:create-invoice", $registered);
    }

    public function testTheQueryLogCommandsAreStillScheduled(): void
    {
        $scheduled = collect(app(Schedule::class)->events())
            ->map(fn($event) => $event->command ?? "")
            ->implode("\n");

        $this->assertStringContainsString("logs:gather", $scheduled);
        $this->assertStringContainsString("logs:truncate", $scheduled);
    }
}
