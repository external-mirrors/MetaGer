<?php

namespace Tests\Support;

use Closure;
use Illuminate\Foundation\Application;

/**
 * Laravel rebuilds the application for every test, and reading the config
 * files and registering the routes was a large part of what the suite spent.
 * So each process builds them once and every later application takes them
 * from here, through the two cache files Laravel already knows how to read.
 *
 * Routes `php artisan optimize` has already cached, as in CI's test job, are
 * left alone: the suite is meant to run against them there
 * (.gitlab/ci/integrationtest.yml). The config is always taken from here,
 * starting from whatever the first application loaded, because a parallel
 * worker has to change it before anything connects to Redis.
 */
final class BootCache
{
    /** @var array<string, mixed>|null */
    public static ?array $config = null;

    /** @var array<string, mixed> */
    public static array $routes = [];

    /** @param Closure(): Application $createApplication */
    public static function prime(Closure $createApplication): void
    {
        if (self::$config !== null) {
            return;
        }

        $app = $createApplication();
        self::$config = self::withOwnBlocklistDirectory(self::withOwnRedisDatabase($app['config']->all()), $app->storagePath('framework/cache'));
        self::useCacheFile('APP_CONFIG_CACHE', 'config');

        if (! $app->routesAreCached()) {
            $routes = $app['router']->getRoutes();
            foreach ($routes as $route) {
                $route->prepareForSerialization();
            }
            self::$routes = $routes->compile();
            self::useCacheFile('APP_ROUTES_CACHE', 'routes');
        }
    }

    /**
     * Tests use fixed Redis keys (`keyserver:discharges`, `fetcher.queue`), so
     * each parallel worker gets a logical database of its own. Workers beyond
     * the server's sixteen databases would share one; CI runs eight.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private static function withOwnRedisDatabase(array $config): array
    {
        $token = (int) ($_SERVER['TEST_TOKEN'] ?? 0);
        if ($token === 0) {
            return $config;
        }

        foreach ($config['database']['redis'] as $name => $connection) {
            if (is_array($connection) && array_key_exists('database', $connection)) {
                $config['database']['redis'][$name]['database'] = ($token - 1) % 15 + 1;
            }
        }

        return $config;
    }

    /**
     * App\Search\Blacklists writes one compiled file per `blocklist:version`,
     * and that counter lives in the worker's own Redis database above, so two
     * workers at the same version hold different lists. Sharing a directory,
     * one read the other's file and flushed it from under it.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private static function withOwnBlocklistDirectory(array $config, string $cache): array
    {
        $token = (int) ($_SERVER['TEST_TOKEN'] ?? 0);
        if ($token !== 0) {
            $config['metager']['metager']['blocklist']['compiled_directory'] = $cache.DIRECTORY_SEPARATOR."blocklist-{$token}";
        }

        return $config;
    }

    private static function useCacheFile(string $variable, string $file): void
    {
        $_ENV[$variable] = $_SERVER[$variable] = "tests/Support/BootCache/{$file}.php";
    }
}
