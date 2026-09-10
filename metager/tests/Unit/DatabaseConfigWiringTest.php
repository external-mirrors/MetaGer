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
    /**
     * TEST-NET-3, reserved by RFC 5737: guaranteed to sit there un-ACKed rather
     * than reset quickly, unlike a closed local port — which is what an
     * unreachable cluster-internal Postgres looks like from the app.
     */
    private const UNREACHABLE_HOST = '10.255.255.1';

    public function testThePgsqlConnectionHasAPdoTimeout(): void
    {
        $options = config('database.connections.pgsql.options');

        $this->assertArrayHasKey(\PDO::ATTR_TIMEOUT, $options);
        $this->assertGreaterThan(0, $options[\PDO::ATTR_TIMEOUT]);
    }

    /**
     * The config key alone doesn't prove PDO honours it — confirms end to
     * end against a non-routable address ({@see UNREACHABLE_HOST}), the same
     * way an unreachable cluster-internal Postgres host looks from the app
     * during an outage.
     */
    public function testAnUnreachablePostgresFailsWithinTheConfiguredTimeoutNotTheOsDefault(): void
    {
        $this->pointPgsqlAtAnUnreachableHost(timeout: 2);

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

    /**
     * Send every half of the connection to a hole in the network.
     *
     * `host` on its own is not enough any more. config/database.php gives the
     * pgsql connection `read` and `write` blocks whenever DB_READ_HOST is set,
     * and Laravel merges those *over* the rest of the connection config
     * (ConnectionFactory::mergeReadWriteConfig) — so an override of the
     * top-level `host` is silently discarded and the query goes to the real
     * replica. `select` takes the read half, which is exactly the half that
     * override cannot reach.
     *
     * Not hypothetical: this test started answering `select 1` in CI the day
     * DB_READ_HOST was added to the deployed .env, because the runner shares a
     * cluster with the database and the replica answered. It reported the
     * timeout as broken while nothing about the timeout had changed.
     *
     * Both halves are set only when they already exist, so on an unsplit
     * connection — compose, and any deployment that has not opted in — this is
     * the single-host override it always was.
     */
    private function pointPgsqlAtAnUnreachableHost(int $timeout): void
    {
        $pgsql = config('database.connections.pgsql');

        $pgsql['host'] = self::UNREACHABLE_HOST;
        $pgsql['options'][\PDO::ATTR_TIMEOUT] = $timeout;

        foreach (['read', 'write'] as $half) {
            if (isset($pgsql[$half])) {
                $pgsql[$half]['host'] = self::UNREACHABLE_HOST;
            }
        }

        config(['database.connections.pgsql' => $pgsql]);

        // The connection may already have been resolved with the real host by
        // something earlier in the process; config() alone would not move it.
        \DB::purge('pgsql');

        // The guard the missing line above needed. Without it the failure is a
        // test that quietly reaches a real database and reports the timeout as
        // broken — and it only does so where a real database is reachable,
        // which is CI and production and not a laptop.
        $this->assertSame(
            [self::UNREACHABLE_HOST],
            $this->hostsOf(config('database.connections.pgsql')),
            'a half of the pgsql connection still names a real host, so this test could pass by connecting to it'
        );
    }

    /**
     * Every host this connection could reach, deduplicated.
     *
     * @param array<string, mixed> $connection
     * @return list<string>
     */
    private function hostsOf(array $connection): array
    {
        $hosts = [$connection['host'] ?? null];

        foreach (['read', 'write'] as $half) {
            if (isset($connection[$half])) {
                $hosts[] = $connection[$half]['host'] ?? $connection['host'] ?? null;
            }
        }

        return array_values(array_unique(array_filter($hosts)));
    }
}
