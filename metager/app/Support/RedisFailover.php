<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;
use Predis\ClientException;
use Predis\CommunicationException;
use Predis\Connection\Resource\Exception\StreamInitException;
use Predis\PredisException;
use Predis\Response\ServerException;
use Throwable;

/**
 * Runs a Redis operation across a Sentinel failover, instead of answering the
 * user with it.
 *
 * A planned node drain is not supposed to cost anyone a page, and with the
 * chart alone it always does. The subchart's preStop hook on the master runs
 * `CLIENT PAUSE 22000 WRITE` *before* asking Sentinel to fail over
 * (chart/charts/valkey-*.tgz, templates/prestop-configmap.yaml). The HAProxy
 * master-proxy's health check is AUTH + PING + INFO replication — all reads,
 * which that pause does not block — so for the whole pause the dying master
 * still answers `role:master`, `connected_slaves:N`,
 * `master_failover_state:no-failover`, keeps passing even the strict backend
 * check, and keeps being handed new connections. The writes queue behind the
 * pause; when Sentinel finishes and demotes the node they run against a
 * replica and come back `-READONLY`. That is the 14-26s gap measured between
 * the socket burst and the READONLY burst in all four drain windows of
 * 2026-09-08.
 *
 * Draining a node that is no longer the master avoids the pause entirely (the
 * hook's `else` branch exits immediately), which is why the runbook fails the
 * master away first. This class covers what is left: the ~1-2s in which
 * Sentinel has promoted nobody yet, and no node in the cluster can accept a
 * write. That window is irreducible — HAProxy runs in TCP mode and cannot
 * replay a command onto a different backend mid-stream, and Predis' own retry
 * layer (SentinelReplication::retryCommandOnFailure) only catches
 * CommunicationException and -LOADING, never -READONLY. Something has to
 * retry, and the client is the only thing that can.
 *
 * Not a general-purpose "make Redis reliable" wrapper: only the errors a
 * failover actually produces are retried, and everything else — a WRONGTYPE, a
 * bad command — is rethrown on the first attempt, because retrying a bug just
 * makes it slower to find.
 */
class RedisFailover
{
    /**
     * How long to keep trying before giving up and letting the caller answer.
     *
     * Sized against what it is covering, not against patience in general: a
     * Sentinel promotion is ~1-2s, so 3s clears it with margin while staying
     * well inside EngineOrchestrator::WAIT_SECONDS, which the search page has
     * already promised the user.
     */
    public const BUDGET_SECONDS = 3.0;

    /** Doubles per attempt: 50ms, 100ms, 200ms, 400ms, 800ms, ... */
    private const FIRST_BACKOFF_MICROSECONDS = 50000;

    /**
     * Server errors that mean "ask again in a moment", as opposed to "you
     * asked for something impossible".
     *
     * READONLY   — wrote to a node Sentinel has demoted since we connected.
     * LOADING    — a freshly promoted node still reading its dataset in.
     * MASTERDOWN — a replica that has lost its master and is set to refuse
     *              stale reads.
     */
    private const RETRYABLE_ERROR_TYPES = ['READONLY', 'LOADING', 'MASTERDOWN'];

    /**
     * @template T
     * @param callable():T $operation
     * @param string|null $connection Connection to drop before retrying, as
     *        named in config/database.php. Null means the default one.
     * @return T
     */
    public static function retry(
        callable $operation,
        float $budgetSeconds = self::BUDGET_SECONDS,
        ?string $connection = null
    ) {
        $deadline = microtime(true) + $budgetSeconds;
        $backoff = self::FIRST_BACKOFF_MICROSECONDS;

        while (true) {
            try {
                return $operation();
            } catch (PredisException $e) {
                if (!self::isFailover($e)) {
                    throw $e;
                }

                // The connection is pinned to a node that just stopped being
                // the master. Dropping it is the point of the retry: a fresh
                // one is routed by HAProxy again, which by now has seen the
                // promotion. Retrying on the same socket would only collect
                // the same -READONLY.
                //
                // Done *before* the budget check, so giving up drops the
                // socket too. For a request that hardly matters — the process
                // is about to end. For a long-lived daemon it is the difference
                // between recovering and not: a connection this failed on is
                // not going to start working, and the manager pools it, so
                // whatever the next loop iteration does would inherit it. The
                // fetch worker hit exactly that on 2026-09-10 — one timeout
                // exhausts a budget shorter than the timeout, so every retry
                // afterwards would have been made on the same dead socket.
                self::reconnect($connection);

                // Sleeping past the deadline and trying anyway would make the
                // budget a lie, so the last attempt is the one that still fits.
                $remaining = $deadline - microtime(true);
                if ($remaining <= $backoff / 1000000) {
                    throw $e;
                }

                usleep($backoff);
                $backoff *= 2;
            }
        }
    }

    /**
     * Whether this is a failover in progress rather than a broken call.
     */
    public static function isFailover(Throwable $e): bool
    {
        if ($e instanceof CommunicationException) {
            // The socket died: HAProxy closing connections to a node it has
            // just seen demoted, or the pod going away underneath us.
            return true;
        }

        if ($e instanceof StreamInitException) {
            // Could not open a socket at all — "Connection refused" while the
            // pod is going down, "No route to host" once it has. Extends
            // PredisException *directly* rather than CommunicationException,
            // so it has to be named separately or it reads as a caller bug and
            // is rethrown on the first attempt. It was 5 of the production
            // events on 2026-09-08 (GlitchTip issue 1159, the key-login path).
            return true;
        }

        if ($e instanceof ServerException) {
            return in_array($e->getErrorType(), self::RETRYABLE_ERROR_TYPES, true);
        }

        if ($e instanceof ClientException) {
            // Predis exhausted its sentinel list. A sibling of
            // ConnectionException rather than a subclass, and its own retry
            // layer does not catch it either (GlitchTip METAGER-I/L, and
            // App\MetaGer's constructor for the same reason).
            return str_contains($e->getMessage(), 'No sentinel server available');
        }

        return false;
    }

    /**
     * Drop the pooled connection so the next attempt opens a new one.
     *
     * Best-effort on purpose: if closing the socket is itself what throws,
     * that is not a reason to lose the retry we were about to make.
     */
    private static function reconnect(?string $connection): void
    {
        try {
            Redis::connection($connection)->client()->disconnect();
        } catch (Throwable $ignored) {
            // Nothing to do — the next command reconnects regardless.
        }
    }
}
