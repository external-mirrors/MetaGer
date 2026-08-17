<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Every artisan command the container's entrypoint runs at boot has to exist.
 *
 * The failure this catches is quiet and total: the entrypoint runs under
 * `set -e`, so an unknown command name aborts the script before php-fpm is
 * started at all. The pod then fails its readiness probe and rolls back, and
 * the reason is one line of "Command not found" in a log nobody is reading yet.
 *
 * Nothing else connects the two. Deleting a command is a change in
 * app/Console/Commands and deleting its caller is a change in build/, and the
 * PHP test suite has no view of build/ — the compose file mounts only
 * ./metager. What it does have is the image itself: the Dockerfile installs the
 * per-environment entrypoint at /usr/local/bin/entrypoint, and the test suite
 * runs inside that image, so this reads the script that will actually run
 * rather than the repository copy of it.
 *
 * Written after removing the advertising subsystem, which deleted the
 * load:affiliate-blacklist command that entrypoint_production.sh had been
 * calling on every production boot since 2021.
 */
class EntrypointCommandsTest extends TestCase
{
    private const ENTRYPOINT = "/usr/local/bin/entrypoint";

    public function testEveryCommandTheEntrypointRunsIsRegistered(): void
    {
        if (!is_readable(self::ENTRYPOINT)) {
            $this->markTestSkipped("Not running inside the fpm image; " . self::ENTRYPOINT . " is not there to read.");
        }

        preg_match_all(
            '/^\s*php\s+artisan\s+([a-z0-9:_-]+)/mi',
            file_get_contents(self::ENTRYPOINT),
            $matches
        );

        $called = array_unique($matches[1]);

        $this->assertNotEmpty($called, "Found no artisan calls in the entrypoint at all, so this test is not looking at what it thinks it is.");

        $registered = array_keys(Artisan::all());

        foreach ($called as $command) {
            $this->assertContains(
                $command,
                $registered,
                "The entrypoint runs `php artisan $command`, which is not a registered command. "
                    . "Under `set -e` that aborts boot before php-fpm starts."
            );
        }
    }
}
