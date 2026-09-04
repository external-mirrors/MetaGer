<?php

namespace Tests\Feature\Search;

use Tests\TestCase;

/**
 * The stresstest harness is gone; this keeps it gone.
 *
 * `/admin/stress` drove a real search against a purpose-built "dummy engine"
 * (a separate repository) by swapping the whole engine registry for
 * config/stress.json, reached through a `dummy` flag on MetaGer that the search
 * path had to branch on. Load testing does not need a branch inside the object
 * under test, and the branch was one of two places that could decide where
 * engine configuration comes from.
 *
 * A test that asserts an absence looks odd, but the failure it prevents is
 * real: the route was one line in routes/session.php, and one line is easy to
 * restore by accident in a merge. If MetaGer needs load testing again, it
 * should drive the ordinary search path from outside rather than grow a second
 * configuration source inside it.
 */
class StresstestRemovedTest extends TestCase
{
    /**
     * The admin routes are unauthenticated outside development and production
     * (decided per-request in App\Http\Middleware\AdminAuthenticate, not in
     * routes/session.php — see its docblock), so under test these would
     * answer if they still existed.
     */
    public function testTheStresstestRoutesAreGone(): void
    {
        $this->get("/admin/stress")->assertNotFound();
        $this->get("/admin/stress/verify")->assertNotFound();
    }

    public function testTheStresstestEngineConfigurationIsGone(): void
    {
        $this->assertFileDoesNotExist(
            config_path("stress.json"),
            "config/stress.json is back. It is a second, parallel engine configuration; the registry is the only one."
        );
    }
}
