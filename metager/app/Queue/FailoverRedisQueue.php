<?php

namespace App\Queue;

use App\Support\RedisFailover;
use Illuminate\Queue\RedisQueue;
use Illuminate\Support\Facades\Log;
use Predis\PredisException;

/**
 * The Redis queue, minus the part where a failover ends the worker.
 *
 * `queue:work` is a daemon. It resolves one queue connection at startup and
 * keeps it: `redis` is a container singleton, so the Predis client — and its
 * socket — lives as long as the pod. Every iteration of Worker::daemon calls
 * pop() on it, and pop() is the only Redis call left in that loop
 * (App\Providers\AppServiceProvider turns off the interruption polling that
 * added two more).
 *
 * That single call is enough to stop the worker for good. When a Valkey
 * failover leaves the socket pointing at a node that is gone, pop() throws;
 * Worker::getNextJob catches it, reports it, sleeps a second and pops again —
 * on the same pooled socket, which is not going to start working. Predis'
 * "Operation has timed out" is not in Laravel's lost-connection list either
 * (that list is entirely PDO messages), so stopWorkerIfLostConnection does not
 * fire and the worker never quits to be replaced. Before the --max-time
 * recycle existed the loop would spin like that until someone noticed; with it,
 * recovery took up to five minutes of undelivered balance updates.
 *
 * RedisFailover::retry closes both halves: it drops the socket and tries again
 * within the budget, and — since App\Support\RedisFailover reconnects before
 * rethrowing — even giving up leaves a fresh connection for the next iteration.
 * So the worst case is one report and a one-second sleep, not a dead worker.
 *
 * Retrying a pop cannot lose a job. migrateExpiredJobs is idempotent, and the
 * pop itself is one atomic Lua script: if the reply is lost after the server
 * ran it, the job is sitting in the `:reserved` set and comes back after
 * `retry_after`. That is the queue's existing at-least-once contract — the same
 * thing that happens whenever a worker dies holding a job — not something this
 * subclass introduces.
 *
 * pushRaw() is wrapped too, for fpm rather than the worker: it is
 * KeyChanged's broadcast push, made on whatever page a key holder loads, over a
 * fresh socket with this connection's 1s connect timeout. Linux retransmits a
 * lost SYN at exactly 1s, so a single dropped packet failed it while Valkey was
 * answering the same request's cache read on the default connection — once a
 * day, reported through ShouldRescue's rescue() as a new GlitchTip issue for
 * every page it happened on (METAGER-U6 and twenty siblings). A retried push
 * whose reply was lost may enqueue the broadcast twice; that is the same
 * duplicate delivery this queue already tolerates below.
 *
 * Neither retry clears a *planned* failover, and that is the common one. Every
 * Valkey rollout — each subchart bump renovate merges — restarts the master
 * pod, whose preStop hook holds `CLIENT PAUSE 22000 WRITE` before Sentinel
 * hands over (see App\Support\RedisFailover). The push is a write, so it
 * waits out this connection's 3s read timeout, which spends the push budget in
 * one go, and the reconnected socket would only join the same pause. On
 * 2026-10-16 and 2026-10-21 that was a burst of Predis\TimeoutException from
 * every page a key holder loaded during the rollout, each reported through
 * rescue() (METAGER-193 to 19E, 19H, 19I). So once the retry has given up on a
 * failover, the push is dropped with a log line instead of an exception: this
 * connection carries nothing but broadcasts (config/queue.php), and a lost
 * broadcast costs a refresh — the trade config/broadcasting.php and the
 * worker's --tries=1 are both built on. Anything else still throws.
 *
 * deleteReserved() is wrapped the same way, for the worker. It runs after the
 * broadcast has been delivered, and the same pause fails it (METAGER-19D). A
 * reserved entry that is not removed comes back after `retry_after` and, with
 * --tries=1, is failed as MaxAttemptsExceededException (METAGER-19F) — not
 * delivered twice — so the cost is a report and a failed_jobs row, not a
 * duplicate. ZREM is idempotent, so retrying it is safe; when even the retry
 * cannot get through, bootstrap/app.php keeps that resurfaced job from being
 * reported as well.
 *
 * deleteAndRelease() is not wrapped: retrying it would not be safe — its
 * script removes and re-pushes, so a lost reply would double-release — and
 * with --tries=1 the worker never releases a job anyway.
 *
 * @see \App\Console\Commands\RequestFetcher  the same fix, for the fetch loop
 */
class FailoverRedisQueue extends RedisQueue
{
    /**
     * Longer than a request's budget on purpose: nobody is blocked on a job
     * pop, so it can afford to sit through a promotion rather than report and
     * sleep. It has to exceed the read timeout on the connection or the first
     * timeout spends the whole budget and the retry rethrows before it ever
     * attempts the reconnected socket — the pairing that
     * Tests\Unit\QueueRedisConnectionTest pins.
     */
    public const RETRY_BUDGET_SECONDS = 12.0;

    /**
     * A request's budget, not the worker's: somebody is waiting on this push —
     * the page it is made from — and RedisFailover's default is sized to clear
     * a promotion without outlasting what that page has already promised.
     */
    public const PUSH_BUDGET_SECONDS = RedisFailover::BUDGET_SECONDS;

    /**
     * @param  string  $payload
     * @param  \UnitEnum|string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->dropOnFailover(
            "push",
            fn() => RedisFailover::retry(
                fn() => parent::pushRaw($payload, $queue, $options),
                self::PUSH_BUDGET_SECONDS,
                $this->connection
            )
        );
    }

    /**
     * @param  string  $queue
     * @param  \Illuminate\Queue\Jobs\RedisJob  $job
     * @return void
     */
    public function deleteReserved($queue, $job)
    {
        $this->dropOnFailover(
            "delete of a handled job",
            fn() => RedisFailover::retry(
                fn() => parent::deleteReserved($queue, $job),
                self::RETRY_BUDGET_SECONDS,
                $this->connection
            )
        );
    }

    /**
     * Run $operation, and give up quietly if it failed on a failover the retry
     * inside it could not outlast. See the class docblock for why that is a
     * log line on this queue and not a report.
     *
     * @template T
     * @param callable():T $operation
     * @return T|null
     */
    private function dropOnFailover(string $what, callable $operation)
    {
        try {
            return $operation();
        } catch (PredisException $e) {
            if (!RedisFailover::isFailover($e)) {
                throw $e;
            }

            Log::warning("broadcast queue {$what} dropped during a Valkey failover: " . $e->getMessage());

            return null;
        }
    }

    /**
     * @param  \UnitEnum|string|null  $queue
     * @param  int  $index
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null, $index = 0)
    {
        return RedisFailover::retry(
            fn() => parent::pop($queue, $index),
            self::RETRY_BUDGET_SECONDS,
            // Null means "the default connection" to both this class and
            // RedisFailover, so an unset queue connection still reconnects the
            // one it actually used.
            $this->connection
        );
    }
}
