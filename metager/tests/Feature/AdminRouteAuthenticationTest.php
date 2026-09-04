<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminAuthenticate;
use Tests\TestCase;

/**
 * Two invariants that both had to hold for /admin/* to be reachable in
 * tests, and only one of which was actually broken.
 *
 * Every admin route must carry AdminAuthenticate unconditionally — that part
 * was already true before this test existed and stays true after, since the
 * decision of whether to actually require auth lives inside the middleware,
 * not in whether it's attached.
 *
 * The real bug was the other invariant: `php artisan optimize` runs
 * `config:cache`, which bakes `config('app.env')` — and therefore
 * `App::environment()` — into bootstrap/cache/config.php as a literal at
 * build time. LoadConfiguration reads that literal back on every later
 * request without calling env() again, so phpunit.xml's `<env
 * name="APP_ENV" value="testing"/>` is silently ignored once config is
 * cached — exactly the same mechanism .gitlab-ci.yml already documents for
 * CACHE_STORE/SESSION_DRIVER/QUEUE_CONNECTION. CI's `test` job builds that
 * cache from a copy of the production .env, so `App::environment()` stayed
 * "production" for the whole job no matter what ran afterwards, and every
 * AdminAuthenticate check on every /admin/* route required real auth. Twelve
 * AssocAdminTest cases failed in CI with 302s while passing locally (no
 * config cache there) for this reason — a route-registration-time theory
 * was tried first and ruled out, because this same test's first version
 * (asserting only middleware attachment) passed in that same failing CI run.
 * The fix is a job-level `APP_ENV: testing` variable on `test` in
 * .gitlab-ci.yml/integrationtest.yml: dotenv doesn't overwrite a
 * already-set real environment variable, so it wins over the copied-in
 * production .env and gets baked into the cache instead.
 */
class AdminRouteAuthenticationTest extends TestCase
{
    public function testEveryAdminRouteCarriesTheEnvironmentGatedMiddleware(): void
    {
        $adminRoutes = collect(app("router")->getRoutes())
            ->filter(fn ($route) => str_starts_with($route->uri(), "admin/"));

        $this->assertNotEmpty($adminRoutes, "Expected at least one route under admin/.");

        foreach ($adminRoutes as $route) {
            $this->assertContains(
                AdminAuthenticate::class,
                $route->gatherMiddleware(),
                "Route {$route->uri()} is missing AdminAuthenticate — its auth gating must not depend on when routes were registered."
            );
        }
    }

    public function testTheAppEnvironmentIsTestingEvenUnderAWarmConfigCache(): void
    {
        $this->assertSame(
            "testing",
            app()->environment(),
            "App::environment() must be \"testing\" here. If this is \"production\", the test job's config " .
                "cache was built without APP_ENV=testing as a real job variable, and every AdminAuthenticate " .
                "check on every /admin/* route will require real Keycloak auth."
        );
    }
}
