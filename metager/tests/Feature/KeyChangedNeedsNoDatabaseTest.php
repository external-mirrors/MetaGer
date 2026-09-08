<?php

namespace Tests\Feature;

use App\Events\KeyChanged;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Queue;
use PDOException;
use Tests\TestCase;

/**
 * The start page and the result page do not need a database. One line of an
 * unrelated feature quietly made them need one.
 *
 * `App\Events\KeyChanged` is broadcast whenever a key's charge changes.
 * Broadcasting queues a job, and `QUEUE_CONNECTION` is unset in
 * .gitlab/production.yaml — so config/queue.php's
 * `env('QUEUE_CONNECTION', 'database')` falls back to `database` and the
 * dispatch became a synchronous Postgres INSERT on the request path. On
 * 2026-09-08, with `database-rw.postgresql` briefly unreachable, that one line
 * produced three separate GlitchTip issues from three unrelated callers:
 *
 *   - 1209, the start page: `TilesController::STATIC_TILES()` →
 *     `KeyUser::getKeyState()` → `getKeyData()`, asked only to decide whether
 *     to show a tile.
 *   - 1211, the result page: `AuthenticationValidation` →
 *     `Searchengines::__construct` → `KeyUser::authorize()` → `getKeyData()`.
 *   - 434, the Keymanager webhook: `EventController::keyUpdateEvent()` on
 *     POST /api/event/key/update, which constructs the event directly.
 *
 * Three entry points, one line. That is why the fix belongs on the event and
 * not at any call site: `ShouldRescue` makes `BroadcastManager::queue()` wrap
 * the push in the framework's `rescue()` helper, which reports the failure and
 * swallows it. A missed charge broadcast costs a live-updating balance one
 * refresh; it must never cost the page.
 *
 * ## Why this is not driven through a page
 *
 * It was tried, both ways round, and neither version could fail. The dispatch
 * only happens on the cache-miss branch of `getKeyData()`, and
 * `FakesSearchEngines::actingAsSearchUser()` pre-warms `keyserver:key:*`
 * precisely so tests don't depend on the keyserver — so a page request never
 * reaches the line under test, and the test passes with the bug present.
 * Forcing the cache cold instead sends the keyserver GET into the fixture's
 * catch-all empty response: `charge` comes back null, every paid engine is
 * disabled, and the result page answers 302 for reasons that have nothing to
 * do with the database. A green page-level test here would be measuring the
 * fixture rather than the behaviour, so the dispatch is exercised directly.
 * It is the whole of what changed, it is common to all three call sites, and
 * it does fail without the fix.
 */
class KeyChangedNeedsNoDatabaseTest extends TestCase
{
    /**
     * A queue whose push fails exactly like an unreachable database.
     *
     * Faked at the queue rather than asserted on the interface: checking that
     * KeyChanged implements ShouldRescue would keep passing if Laravel ever
     * changed what it does with it.
     */
    private function installUnreachableQueue(): void
    {
        $failure = new QueryException(
            'pgsql',
            'insert into "jobs" ...',
            [],
            new PDOException(
                'SQLSTATE[08006] [7] connection to server at "database-rw.postgresql" '
                . '(10.109.239.79), port 5432 failed: No route to host'
            )
        );

        $this->app->instance("queue", new class ($failure) {
            public function __construct(private QueryException $failure) {}

            public function connection($name = null): mixed
            {
                return new class ($this->failure) {
                    public function __construct(private QueryException $failure) {}

                    public function pushOn($queue, $job): mixed
                    {
                        throw $this->failure;
                    }
                };
            }
        });
        Queue::clearResolvedInstances();
    }

    public function testBroadcastingAKeyChangeSurvivesAnUnreachableDatabase(): void
    {
        $this->installUnreachableQueue();

        // An uncaught QueryException here is the bug, and it is what all three
        // call sites propagated to the user. PHPUnit reports it as an error
        // without any assertion of ours.
        KeyChanged::dispatch("d00113f5-7fd4-4f7b-95d6-05b097bd836d", 0, 8311.3);

        $this->assertTrue(true);
    }
}
