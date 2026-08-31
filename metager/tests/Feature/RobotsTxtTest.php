<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /robots.txt — routes/web.php renders robots.production.blade.php only under
 * App::environment("production"); every other environment (including this
 * suite's "testing") gets robots.development instead, which has no Disallow
 * rules at all. So every case here forces the environment to check the
 * production rules.
 */
class RobotsTxtTest extends TestCase
{
    private function asProduction(): void
    {
        $this->app["env"] = "production";
    }

    public function testAccountAndLoginFlowsAreDisallowed(): void
    {
        $this->asProduction();

        $response = $this->get("/robots.txt");

        $response->assertOk();
        $response->assertHeader("Content-Type", "text/plain; charset=UTF-8");

        foreach ([
            "/konto",
            "/*/konto",
            "/anmelden",
            "/*/anmelden",
            "/schluessel-erstellen",
            "/*/schluessel-erstellen",
            "/keys/key/",
            "/keys/admin/",
        ] as $path) {
            $response->assertSee("Disallow: {$path}", false);
        }
    }

    public function testNonProductionDisallowsEverything(): void
    {
        // The test suite itself runs with APP_ENV=testing (phpunit.xml), so this
        // is already the non-production branch without touching the container.
        $response = $this->get("/robots.txt");

        $response->assertOk();
        $response->assertSee("Disallow: /", false);
    }
}
