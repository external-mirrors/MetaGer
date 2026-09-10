<?php

namespace Tests\Feature;

use App\Events\KeyChanged;
use Tests\TestCase;

/**
 * A key-change broadcast must not be pushed onto a SQL queue.
 *
 * The sibling test, {@see KeyChangedNeedsNoDatabaseTest}, pins that a push
 * which *fails* cannot reach the user — `ShouldRescue` swallows it. This one
 * pins the half that rescuing does not cover: that the push does not go to
 * Postgres in the first place.
 *
 * The difference is latency, and it is not small. `QUEUE_CONNECTION` is unset
 * in .gitlab/production.yaml, so config/queue.php falls back to `database`,
 * which is the CNPG cluster. During the 2026-09-09 outage every dispatch of
 * this event opened a PDO connection and waited out
 * `DB_CONNECT_TIMEOUT` — 3 seconds, config/database.php — before `rescue()`
 * threw the result away. `KeyUser::getKeyData()` dispatches on every cache miss
 * of the 10-second keyserver cache, so that was three seconds added to a start
 * page or a search, repeatedly, for a broadcast nobody was waiting on.
 *
 * Asserted on the connection the push is *addressed to*, rather than by
 * counting queries: with `QUEUE_CONNECTION=sync` in the test environment a
 * database assertion would pass for the wrong reason, and the production value
 * is not reproducible here. What is reproducible, and what actually changed, is
 * which connection `BroadcastManager::queue()` asks the queue manager for.
 *
 * @see \App\Events\KeyChanged
 * @see config/broadcasting.php
 */
class KeyChangedAvoidsTheDatabaseQueueTest extends TestCase
{
    /**
     * A queue manager that records what it was asked for instead of pushing.
     *
     * Hand-rolled rather than `Queue::fake()`: the fake answers
     * `connection($name)` with itself and records the job, not the connection
     * name, which is the single fact this test is about.
     */
    private function recordingQueue(): object
    {
        $recorder = new class {
            /** @var list<array{connection: ?string, queue: ?string}> */
            public array $pushes = [];

            public function connection($name = null): mixed
            {
                return new class ($this, $name) {
                    public function __construct(private object $recorder, private ?string $name) {}

                    public function pushOn($queue, $job): mixed
                    {
                        $this->recorder->pushes[] = [
                            "connection" => $this->name,
                            "queue" => $queue,
                        ];

                        return null;
                    }
                };
            }
        };

        $this->app->instance("queue", $recorder);
        \Illuminate\Support\Facades\Queue::clearResolvedInstances();

        return $recorder;
    }

    public function testTheBroadcastIsPushedToTheConfiguredConnectionAndQueue(): void
    {
        config([
            "broadcasting.queue_connection" => "redis",
            "broadcasting.queue" => "broadcasts",
        ]);

        $recorder = $this->recordingQueue();

        KeyChanged::dispatch("d00113f5-7fd4-4f7b-95d6-05b097bd836d", -1.5, 8311.3);

        $this->assertSame(
            [["connection" => "redis", "queue" => "broadcasts"]],
            $recorder->pushes,
            "KeyChanged must name its own queue connection, or the push falls "
            . "through to QUEUE_CONNECTION — `database` in production."
        );
    }

    /**
     * `null` is the value that means "the application default", so it is the
     * one value this event must never hand to the queue manager. Without the
     * constructor assignment that is exactly what it hands over, and the
     * assertion above would be the only thing standing between a search and a
     * Postgres round trip.
     */
    public function testTheBroadcastNeverFallsBackToTheApplicationDefault(): void
    {
        $recorder = $this->recordingQueue();

        KeyChanged::dispatch("d00113f5-7fd4-4f7b-95d6-05b097bd836d", 0, 12.0);

        $this->assertNotEmpty($recorder->pushes, "nothing was pushed at all");
        $this->assertNotNull(
            $recorder->pushes[0]["connection"],
            "a null connection is the application default, which is `database` "
            . "in production — the whole failure this event was moved away from."
        );
    }

    /**
     * The chart runs one worker per queue connection, and the broadcast one is
     * pinned to the pair named in config (chart/templates/deployment-queue.yaml,
     * asserted in chart/tests/assertions.sh). Changing a name here without
     * changing that worker means the jobs are pushed and never run — which
     * fails silently, so it is worth a test that does not.
     *
     * Only the queue name is checkable from here. The *connection* is
     * deliberately overridden to `sync` for the test run (phpunit.xml), because
     * a suite that pushed real broadcasts onto a real Redis list would leave
     * them there — no worker in the test environment drains one. That half of
     * the pair is pinned on the chart side instead, where the rendered
     * `queue:work redis` argument can be read directly.
     */
    public function testTheConfiguredQueueMatchesTheWorkerTheChartRuns(): void
    {
        $this->assertSame("broadcasts", config("broadcasting.queue"), <<<'TXT'
            config/broadcasting.php's queue name changed. chart/templates/deployment-queue.yaml
            runs `queue:work redis --queue=broadcasts`; update it and the assertion in
            chart/tests/assertions.sh to match, or the broadcasts stop being delivered.
            TXT);
    }
}
