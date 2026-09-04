<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Runs migrations against an in-memory sqlite connection instead of the
 * developer's real database.sqlite (what `docker compose up`'s entrypoint
 * migrates and seeds) or production's pgsql. `config()` mutates the live
 * repository regardless of whether config is cached, so this works under
 * the CI test job too — unlike a phpunit.xml <env> override, which
 * `php artisan optimize` bakes past (see .gitlab/ci/integrationtest.yml).
 */
trait UsesInMemorySqlite
{
    protected function setUpInMemorySqlite(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Artisan::call('migrate', ['--database' => 'sqlite', '--force' => true]);
    }
}
