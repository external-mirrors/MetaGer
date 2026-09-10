<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Prometheus\CollectorRegistry;
use Throwable;

/**
 * Counters and timings for the things worth watching, none of which are worth
 * a page.
 *
 * Every method here goes through {@see record}, which swallows whatever the
 * metrics backend throws. That is not defensive habit, it is the finding from
 * the 2026-09-08 node drains: of 118 production errors across four drain
 * windows, 109 came from this class — `LocaleDecision` alone, because
 * LocalizationRedirect calls it on every single request before routing, so a
 * blip in the Redis holding the counters was a broken page for every visitor
 * on the site regardless of what they had asked for.
 *
 * `LocaleDecision` had *tried* to guard that, and the guard never once fired.
 * It caught `Prometheus\Exception\StorageException`, which the library
 * documents on the Redis adapter (`@throws StorageException` all over
 * Storage\AbstractRedis) and then does not actually throw: `PHPRedis::eval()`
 * calls `\Redis::eval()` bare, so an ext-redis `RedisException` — a
 * `RuntimeException`, unrelated to `StorageException` — goes straight past it.
 * The predis client leaks `Predis\Connection\ConnectionException` the same
 * way. Hence `Throwable` here rather than a tidier list: the whole point is
 * that the exception this throws is not knowable from its signature, and
 * getting the class wrong a second time would look exactly like it did the
 * first — green tests, and a 500 for everyone the next time a node is drained.
 *
 * The /metrics endpoint (App\Http\Controllers\Prometheus) deliberately does
 * not go through here. A scrape that cannot reach storage should fail and be
 * seen to fail; it has no user waiting on it.
 */
class PrometheusExporter
{
    /**
     * Record something, or don't, but never fail the request over it.
     *
     * The log line is the entire fallback. It is deliberately not a rethrow,
     * not a queued retry and not a fallback storage: a lost counter tick costs
     * a gap in a graph, and anything more elaborate would reintroduce the
     * failure mode this exists to remove.
     */
    private static function record(callable $operation): void
    {
        try {
            $operation();
        } catch (Throwable $e) {
            Log::warning('Dropped a Prometheus metric: ' . $e->getMessage());
        }
    }

    public static function Duration($duration, $type)
    {
        self::record(function () use ($duration, $type) {
            $registry = CollectorRegistry::getDefault();
            $histogram = $registry->getOrRegisterHistogram('metager', 'request_time', 'Loading Times for different cases', ['type'], [0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0, 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 2.0, 2.2, 2.4, 2.6, 2.8, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0, 15.0, 20.0, 30.0, 35.0]);
            $histogram->observe($duration, [$type]);
        });
    }

    /**
     * @param string $language
     * @param array $type
     */
    public static function PreferredLanguage($language, $type)
    {
        self::record(function () use ($language, $type) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", $language, 'counts preferred language usages', ['type']);
            $counter->inc($type);
        });
    }

    public static function OvertureFail()
    {
        self::record(function () {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "overture_failed", "counts how often overture failed a response");
            $counter->inc();
        });
    }

    public static function KeyUsed(float $amount, string $source, bool $cached)
    {
        self::record(function () use ($amount, $source, $cached) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "key_used", "Counts MetaGer Key Usage", ["source", "cached"]);
            $counter->incBy($amount, [$source, json_encode($cached)]);
        });
    }

    public static function UpdateKeyStatus($key, $tokens, $owner)
    {
        self::record(function () use ($key, $tokens, $owner) {
            $registry = CollectorRegistry::getDefault();
            $gauge = $registry->getOrRegisterGauge("metager", "key_status", "Tracks status of the Key", ["key", "owner"]);
            $gauge->set($tokens, [$key, $owner]);
        });
    }

    public static function CreditcardDonation(string $status)
    {
        self::record(function () use ($status) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "donation_card", "Card Payment started", ["status"]);
            $counter->inc([$status]);
        });
    }

    public static function SuggestionResult(string $httpcode)
    {
        self::record(function () use ($httpcode) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "suggestion_results", "Suggestion Requests answered", ["httpcode"]);
            $counter->inc([$httpcode]);
        });
    }

    /**
     * Every locale decision, and whether it moved the user.
     *
     * The number the `LOCALE_DECOUPLED` rollout is watched on. Decoupling the
     * interface language from the domain removes two whole classes of redirect
     * — language-to-domain and stored-setting-to-prefixed-URL — so the
     * redirect share of this counter should fall and stay fallen. A rise means
     * a rule is firing that was supposed to be gone, which is the one failure
     * mode that costs a user a page load rather than merely a wrong word.
     *
     * `$reason` is a fixed vocabulary, never user input: Prometheus keeps one
     * time series per label value, so a free-form label is a memory leak.
     */
    public static function LocaleDecision(string $reason)
    {
        self::record(function () use ($reason) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "locale_decisions", "Locale resolutions, by what the request was answered with", ["reason"]);
            $counter->inc([$reason]);
        });
    }

    public static function SuggestionSessionCounter()
    {
        self::record(function () {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter("metager", "suggestion_sessions", "Suggestion Requests answered");
            $counter->inc();
        });
    }

    /**
     * How many results a search answered with, and that a search happened.
     *
     * These two lived in MetaGerSearch as bare `CollectorRegistry::getDefault()`
     * calls — the only metrics in the application that did not come through
     * here, and therefore the only ones that could still fail a request. Both
     * sit at the very end of the search, after the engines have answered and
     * the page has been assembled, so a Valkey blip there threw away a search
     * that had already succeeded in every way that matters to the user. That is
     * the exact shape of the 2026-09-08 finding this class was written for; it
     * had simply never been applied to the result page's own two counters.
     *
     * Split in two because the load-more path reports more results without
     * being another search.
     */
    public static function ResultsReturned(int $count)
    {
        self::record(function () use ($count) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'result_counter', 'counts total number of returned results', []);
            $counter->incBy($count);
        });
    }

    /**
     * What happened to a queued keyserver discharge.
     *
     * The charge for a search is no longer paid while the user waits — it goes
     * on a Redis list and `keys:settle-discharges` pays it
     * (App\Console\Commands\SettleKeyDischarges). That moves the failure out
     * of the request, where it was visible as a 500, and into a background
     * process, where without this it would be visible as nothing at all.
     *
     * The labels are the four outcomes, and only one of them is routine:
     * `settled` is the keyserver confirming the charge; `refused` is the
     * keyserver answering with a 4xx or 5xx; `unknown` is a request that timed
     * out, which is deliberately not retried because it may already have been
     * applied; `abandoned` is five failed attempts over five minutes. The last
     * three are money the operator did not collect, and are worth an alert on
     * their rate rather than a look at the logs.
     */
    public static function KeyDischargeSettled(string $result)
    {
        self::record(function () use ($result) {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'key_discharge', 'counts settled and lost keyserver discharges', ['result']);
            $counter->inc([$result]);
        });
    }

    /**
     * How many charges are waiting to be settled.
     *
     * Written once per run of `keys:settle-discharges`, which is once a minute.
     * Discharges go to the keyserver one at a time over one connection, so
     * there is a search rate above which a single run cannot drain what a
     * minute produces — and nothing fails when that happens. The queue simply
     * grows, the claims holding those tokens keep being extended, and the money
     * is settled later and later. A gauge that is not near zero is the only
     * warning there is.
     */
    public static function KeyDischargeQueueDepth(int $depth)
    {
        self::record(function () use ($depth) {
            $registry = CollectorRegistry::getDefault();
            $gauge = $registry->getOrRegisterGauge('metager', 'key_discharge_queue', 'keyserver charges waiting to be settled', []);
            $gauge->set($depth);
        });
    }

    public static function SearchAnswered()
    {
        self::record(function () {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('metager', 'query_counter', 'counts total number of search queries', []);
            $counter->inc();
        });
    }
}
