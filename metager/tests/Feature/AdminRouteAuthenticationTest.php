<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminAuthenticate;
use Tests\TestCase;

/**
 * Pins the invariant CLAUDE.md's route-cache paragraph describes: the route
 * table is a constant, so nothing that decides per-environment may run while
 * routes/session.php registers routes — that decision gets baked into the
 * cache `php artisan optimize` builds in CI and stops varying afterwards.
 *
 * This broke for real: routes/session.php used to build $auth_middleware
 * from App::environment() before registering the admin group, so a route
 * cache built from a copy of the production .env locked every /admin/*
 * route to keycloak-web regardless of which environment served the request
 * later. `AssocAdminTest`'s twelve admin-route tests all failed in CI with
 * 302s because of this, while passing locally where no route cache exists.
 * This test fails under a warm cache the same way those did, and under a
 * cold one too — every /admin/* route must carry AdminAuthenticate, full
 * stop, with the environment check living inside that middleware instead.
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
}
