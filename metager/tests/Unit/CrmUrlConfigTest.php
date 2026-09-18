<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * config/metager/metager.php's `crm.url` is a visitor's own browser's
 * address for suma-crm (the "become a member"/donation redirects); `crm.internal_url`
 * is what the fpm container itself uses for server-to-server API calls
 * (MembershipIssuer, DonationCheckoutIssuer, etc.). Under compose those
 * differ: suma-crm publishes its port on the host, so the browser reaches it
 * at `localhost:8002`, but `localhost` from inside fpm is the container
 * itself, not the host — CRM_INTERNAL_URL has to say `host.docker.internal`
 * instead. In every other environment (production included, until it wires
 * up its own value) there is only one real address, so CRM_INTERNAL_URL
 * defaults to CRM_BASE_URL rather than requiring both to be set.
 *
 * config/metager/metager.php reads env() directly, so the file is
 * re-evaluated with the environment set rather than read back through
 * config(), which was resolved at boot. All three layers, not just
 * putenv() — see DatabaseReadReplicaConfigTest's own docblock on why: this
 * repo's local .env already sets both variables, and CI runs against a copy
 * of it, so a test that only calls putenv() would still read .env's values
 * out of $_ENV underneath it.
 */
class CrmUrlConfigTest extends TestCase
{
    private const KEYS = ['CRM_BASE_URL', 'CRM_INTERNAL_URL'];

    /**
     * @var array<string, array{0: ?string, 1: ?string, 2: string|false}>
     */
    private array $restore = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::KEYS as $key) {
            $this->restore[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null, getenv($key)];
            $this->forgetEnv($key);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->restore as $key => [$env, $server, $putenv]) {
            $this->forgetEnv($key);

            if ($env !== null) {
                $_ENV[$key] = $env;
            }
            if ($server !== null) {
                $_SERVER[$key] = $server;
            }
            if ($putenv !== false) {
                putenv("{$key}={$putenv}");
            }
        }

        parent::tearDown();
    }

    private function forgetEnv(string $key): void
    {
        unset($_ENV[$key], $_SERVER[$key]);
        putenv($key);
    }

    private function setEnv(string $key, string $value): void
    {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }

    /**
     * @param array<string, string> $env
     * @return array<string, mixed>
     */
    private function crmConfigWith(array $env): array
    {
        foreach ($env as $key => $value) {
            $this->setEnv($key, $value);
        }

        $config = require base_path('config/metager/metager.php');

        return $config['crm'];
    }

    /**
     * A fresh docker-compose checkout sets only CRM_BASE_URL and never
     * CRM_INTERNAL_URL — production's own single-URL deployment relies on
     * this too, until it gets a container-networking split of its own.
     */
    public function testAnUnsetInternalUrlFallsBackToTheBaseUrl(): void
    {
        $crm = $this->crmConfigWith(['CRM_BASE_URL' => 'https://crm.example.com']);

        $this->assertSame('https://crm.example.com', $crm['url']);
        $this->assertSame('https://crm.example.com', $crm['internal_url']);
    }

    /**
     * The compose case this split exists for: the browser and the fpm
     * container need different addresses for the same suma-crm instance.
     */
    public function testAnExplicitInternalUrlOverridesTheBaseUrl(): void
    {
        $crm = $this->crmConfigWith([
            'CRM_BASE_URL' => 'http://localhost:8002',
            'CRM_INTERNAL_URL' => 'http://host.docker.internal:8002',
        ]);

        $this->assertSame('http://localhost:8002', $crm['url']);
        $this->assertSame('http://host.docker.internal:8002', $crm['internal_url']);
    }
}
