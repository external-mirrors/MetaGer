<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * config/database.php's `pgsql.options[PDO::ATTR_TIMEOUT]` is what turns a
 * Postgres outage into a fast, per-request failure instead of a hang bounded
 * only by the OS's own TCP timeout (tens of seconds to minutes). Losing this
 * silently reintroduces two production incidents at once: FPM's shared
 * worker pool exhausting because concurrent requests to DB-touching routes
 * (settings/membership/donations) each hang, which then fails the
 * liveness/readiness probe too since it also goes through FPM — and the
 * scheduler's single-process `schedule:run` loop stalling past
 * SchedulerHeartbeat::MAX_AGE_IN_MINUTES on a DB-touching scheduled command,
 * so its liveness probe restarts a pod a restart cannot fix.
 */
class DatabaseConfigWiringTest extends TestCase
{
    public function testThePgsqlConnectionHasAPdoTimeout(): void
    {
        $options = config('database.connections.pgsql.options');

        $this->assertArrayHasKey(\PDO::ATTR_TIMEOUT, $options);
        $this->assertGreaterThan(0, $options[\PDO::ATTR_TIMEOUT]);
    }

    /**
     * The config key alone doesn't prove PDO honours it — confirms end to
     * end against a non-routable address (10.255.255.1, TEST-NET-3 reserved
     * by RFC 5737: guaranteed to sit there un-ACKed rather than reset
     * quickly, unlike a closed local port), the same way an unreachable
     * cluster-internal Postgres host looks from the app during an outage.
     */
    public function testAnUnreachablePostgresFailsWithinTheConfiguredTimeoutNotTheOsDefault(): void
    {
        config([
            'database.connections.pgsql.host' => '10.255.255.1',
            'database.connections.pgsql.options.' . \PDO::ATTR_TIMEOUT => 2,
        ]);

        $start = microtime(true);

        try {
            \DB::connection('pgsql')->select('select 1');
            $this->fail('Expected connecting to an unreachable host to throw.');
        } catch (\Illuminate\Database\QueryException $e) {
            // expected
        }

        $elapsed = microtime(true) - $start;

        $this->assertLessThan(
            10,
            $elapsed,
            "Connecting to an unreachable Postgres took {$elapsed}s — the configured timeout was not honoured."
        );
    }
}
