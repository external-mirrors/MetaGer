<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every provider listed in bootstrap/providers.php is a class that exists.
 *
 * Laravel does not enforce this. It skips a provider whose class is missing, so
 * the app boots, every page renders and the whole suite stays green while the
 * list quietly names a package that was uninstalled some commits ago — which is
 * exactly what Jenssegers\Agent\AgentServiceProvider was doing after the device
 * detection was folded into App\Support\Browser.
 *
 * That tolerance is the problem: a line here is either load-bearing or noise,
 * and nothing else tells the two apart. If a provider genuinely should not
 * load, delete the line rather than lean on the framework ignoring it.
 */
class ServiceProviderRegistrationTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function registeredProviders(): array
    {
        // tests/Feature/ is two levels below the project root, and providers
        // are read before the application boots, so base_path() is unavailable.
        $providers = require dirname(__DIR__, 2) . "/bootstrap/providers.php";

        return array_combine(
            $providers,
            array_map(fn($provider) => [$provider], $providers)
        );
    }

    #[DataProvider("registeredProviders")]
    public function testTheProviderClassExists(string $provider): void
    {
        $this->assertTrue(
            class_exists($provider),
            "bootstrap/providers.php registers [$provider], which is not installed. " .
                "Laravel skips it silently, so nothing else will tell you."
        );
    }
}
