<?php

namespace App\Console\Commands;

use App;
use App\Search\Fetch\MissionOptions;
use App\Support\RedisFailover;
use Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Log;
use Carbon;
use Predis\PredisException;

class RequestFetcher extends Command
{
    const HEALTHCHECK_KEY = "fetcher_healthcheck";
    const HEALTHCHECK_FORMAT = "Y-m-d H:i:s";

    /**
     * Every Redis call this command makes goes over its own connection, and
     * every cached body over the store bound to it.
     *
     * Not the `default` connection, whose read_write_timeout is -1 because
     * other callers on it block for up to 30s. This loop's longest blocking
     * call is a one-second blpop, so it can afford a bounded read timeout — and
     * needs one: without it a Valkey master that disappears without closing its
     * sockets is a read that never returns, which is not an error any retry can
     * see. See the 'fetcher' entries in config/database.php and config/cache.php.
     */
    const REDIS_CONNECTION = "fetcher";
    const CACHE_STORE = "fetcher";

    /**
     * How long this worker keeps retrying a Redis call across a failover.
     *
     * Four times RedisFailover::BUDGET_SECONDS, because the default is sized
     * for a request that has promised the user an answer within
     * EngineOrchestrator::WAIT_SECONDS and this worker has promised nobody
     * anything. It has to be larger than the read timeout above or the budget
     * would be spent by the first timeout, leaving no attempt on the fresh
     * connection that the reconnect just prepared — which is the attempt that
     * actually recovers.
     *
     * deliverAnswer deliberately does not use it; see the note there.
     */
    const RETRY_BUDGET_SECONDS = 12.0;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:fetcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commands fetches requests to the installed search engines';

    protected $shouldRun = true;
    protected $multicurl = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->multicurl = curl_multi_init();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // pcntl_signal() alone only installs the handler at the OS level; without
        // async signals turned on (or an explicit pcntl_signal_dispatch() call)
        // it is never actually invoked. Symfony Console's own Application
        // constructor already turns this on unconditionally for every artisan
        // command (it builds a SignalRegistry whether or not the command
        // subscribes to anything), so this call is a no-op in practice today —
        // kept explicit rather than depending on that framework internal.
        pcntl_async_signals(true);
        // The previous bug here was not signal dispatch (that part already
        // worked) — it was that this command only ever registered SIGQUIT.
        // Docker's default stop signal for this image is SIGQUIT (inherited
        // STOPSIGNAL from the php-fpm base image), but Kubernetes always sends
        // SIGTERM, which had no handler at all and so hit PHP's default
        // (immediate, ungraceful) disposition. Both are handled here now so the
        // drain below runs in either place.
        pcntl_signal(SIGQUIT, [$this, "sig_handler"]);
        pcntl_signal(SIGTERM, [$this, "sig_handler"]);

        // Redis might not be available now
        for ($count = 0; $count < 10; $count++) {
            try {
                $this->redis()->set(self::HEALTHCHECK_KEY, Carbon::now()->format(self::HEALTHCHECK_FORMAT));
                break;
            } catch (\Exception $e) {
                if ($count >= 60) {
                    // If its not available after 60 seconds we will exit
                    return;
                }
                sleep(1);
            }
        }

        try {
            while (true) {
                $this->stampHealthcheck();
                $operationsRunning = true;
                curl_multi_exec($this->multicurl, $operationsRunning);
                $status = $this->readMultiCurl($this->multicurl);
                $answersRead = $status[0];
                $messagesLeft = $status[1];
                $newJobs = $this->checkNewJobs($operationsRunning, $messagesLeft);
                if ($newJobs === 0 && $answersRead === 0) {
                    $this->waitForActivity($operationsRunning);
                }

                // Drain whatever this process's own multicurl handle is still
                // carrying before exiting; no need to wait on any fpm pod's
                // lifecycle (see the removed FPMGracefulStop handshake below —
                // this worker is its own Deployment now, decoupled from fpm's,
                // and any other worker replica already services the shared
                // fetch queue).
                if (!$this->shouldRun && $operationsRunning === 0) {
                    break;
                }
            }
        } finally {
            curl_multi_close($this->multicurl);
        }
    }

    /**
     * Hand a finished answer to whoever is waiting for it.
     *
     * The one write in this loop that a search is blocked on: an fpm process is
     * sitting in `Redis::brpop` on exactly this hash
     * (EngineOrchestrator::waitForMainResults). Retried across a failover,
     * because the alternative is that the upstream request was made, paid for
     * and answered — and then thrown away because a Valkey node changed role in
     * the milliseconds afterwards.
     *
     * Giving up is per answer rather than per pass. Each call is a different
     * engine's response, and one that cannot be written back should not cost
     * the others queued behind it in the same multicurl handle — which is what
     * letting this propagate did, along with the process itself.
     *
     * A lost answer is not silent to the user, but it is survivable: the search
     * waiting on this hash times out and renders without this engine, exactly
     * as if the engine had been slow.
     *
     * `protected` for RequestFetcherFailoverTest — the write it makes is not
     * reachable otherwise without a live curl transfer, and the failover
     * behaviour is the whole point of the method.
     */
    protected function deliverAnswer(string $resulthash, string $payload): void
    {
        try {
            // The short default budget, not RETRY_BUDGET_SECONDS. Everything
            // else in this loop is retried patiently because nobody is waiting;
            // this one has someone waiting, and they stop waiting after
            // EngineOrchestrator::WAIT_SECONDS. Worse, the loop is
            // single-threaded, so time spent here is time every *other* answer
            // and the queue poll itself spend blocked. Failing fast and losing
            // one engine's answer is the cheaper mistake — and the reconnect
            // still happens, so the next call in this iteration gets a fresh
            // socket.
            RedisFailover::retry(
                fn() => $this->redis()->pipeline(function ($pipe) use ($resulthash, $payload) {
                    $pipe->lpush($resulthash, $payload);
                    $pipe->expire($resulthash, 60);
                }),
                RedisFailover::BUDGET_SECONDS,
                self::REDIS_CONNECTION
            );
        } catch (PredisException $e) {
            Log::warning("Could not deliver a fetched answer: " . $e->getMessage());
        }
    }

    /**
     * Say this worker is alive, or say nothing at all.
     *
     * A SET, so a demoted node answers it `-READONLY` — and this runs at the
     * top of every loop iteration, which made it the single most likely place
     * for a Sentinel failover to end this process. Losing the process is not a
     * restart and a shrug: the multicurl handle goes with it, so every engine
     * response in flight is discarded, and every search waiting on one of those
     * hashes waits out EngineOrchestrator::WAIT_SECONDS and renders without it.
     * A drain that was supposed to cost nothing costs six seconds and a thinner
     * result page for everyone mid-search.
     *
     * A missed stamp is worth nothing by comparison — it is read by the
     * liveness probe, which tolerates it being a little old — so this retries
     * and then gives up rather than propagating.
     *
     * That probe is `artisan fetcher:healthcheck`, via
     * App\Support\FetcherHeartbeat. It was not, for a long time: the stamp was
     * written here and read by nothing, while the chart probed
     * `pgrep -f requests:fetcher`. A wedge that leaves this process alive and
     * idle therefore went unnoticed for fifteen minutes on 2026-09-10. Because
     * the stamp is written at the top of every iteration, it goes stale for any
     * reason the loop stops — including a blocking read that never returns,
     * which raises nothing for RedisFailover to catch.
     */
    protected function stampHealthcheck(): void
    {
        try {
            RedisFailover::retry(
                fn() => $this->redis()->set(self::HEALTHCHECK_KEY, Carbon::now()->format(self::HEALTHCHECK_FORMAT)),
                self::RETRY_BUDGET_SECONDS,
                self::REDIS_CONNECTION
            );
        } catch (PredisException $e) {
            Log::warning("Could not stamp the fetcher healthcheck: " . $e->getMessage());
        }
    }

    /**
     * Nothing happened this pass, so wait — but wait on the right thing.
     *
     * With transfers in flight, that is the multi handle's own sockets: it
     * returns the moment an engine sends something. This used to be a flat
     * usleep(10ms), which meant a response landing just after the check sat
     * unnoticed for up to ten milliseconds. Nobody notices that on one engine,
     * but a result page waits for the slowest of them, so it came off the top of
     * every search.
     *
     * The ceiling stays at 10ms because that is also how long a *new job* can
     * sit in the Redis queue unseen — curl_multi_select knows nothing about
     * Redis, so the timeout is what brings us back to look.
     *
     * curl_multi_select answers -1 straight away when it has no socket to wait
     * on, which happens while libcurl is sitting on a timer of its own rather
     * than on the network. Sleeping briefly is what keeps that from spinning the
     * CPU; it is the pattern libcurl's own documentation prescribes.
     *
     * With nothing in flight there is nothing to select on, and checkNewJobs has
     * just spent up to a second blocked on Redis, so a plain short sleep is both
     * enough and all that is left.
     */
    protected function waitForActivity(int $operationsRunning): void
    {
        if ($operationsRunning <= 0) {
            $this->sleepMicroseconds(10 * 1000);
            return;
        }

        if ($this->selectOnMultiHandle(0.01) === -1) {
            $this->sleepMicroseconds(100);
        }
    }

    /** Seam for the tests; see RequestFetcherWaitTest. */
    protected function sleepMicroseconds(int $microseconds): void
    {
        usleep($microseconds);
    }

    /** Seam for the tests; see RequestFetcherWaitTest. */
    protected function selectOnMultiHandle(float $timeoutSeconds): int
    {
        return curl_multi_select($this->multicurl, $timeoutSeconds);
    }

    /**
     * Checks the Redis queue if any new fetch jobs where submitted
     * and adds them to multicurl if there are.
     * Will be blocking call to redis if there are no running jobs in multicurl
     */
    protected function checkNewJobs($operationsRunning, $messagesLeft)
    {
        // Both branches pop, so both are writes and both come back -READONLY
        // from a node Sentinel has demoted. Retried, and on failure treated as
        // "no new jobs this pass": the missions stay on the queue and the next
        // iteration picks them up, which is exactly what an idle pass does
        // anyway. Letting this propagate would end the process and take every
        // in-flight transfer with it.
        try {
            $newJobs = [];
            if ($operationsRunning === 0 && $messagesLeft === -1) {
                $newJob = RedisFailover::retry(
                    fn() => $this->redis()->blpop(\App\MetaGer::FETCHQUEUE_KEY, 1),
                    self::RETRY_BUDGET_SECONDS,
                    self::REDIS_CONNECTION
                );
                if (!empty($newJob)) {
                    $newJobs[] = $newJob[1];
                }
            } else {
                $newJobs = RedisFailover::retry(
                    fn() => $this->redis()->lpop(\App\MetaGer::FETCHQUEUE_KEY, 50),
                    self::RETRY_BUDGET_SECONDS,
                    self::REDIS_CONNECTION
                );
                if ($newJobs === null)
                    $newJobs = [];
            }
        } catch (PredisException $e) {
            Log::warning("Could not read the fetch queue: " . $e->getMessage());

            return 0;
        }
        $addedJobs = 0;
        foreach ($newJobs as $newJob) {
            $newJob = json_decode($newJob, true);
            if (empty($newJob)) {
                Log::error("Couldn't json decode Job: $newJob");
                continue;
            }
            $ch = $this->getCurlHandle($newJob);
            if (curl_multi_add_handle($this->multicurl, $ch) !== 0) {
                $this->shouldRun = false;
                Log::error("Couldn't add Handle to multicurl");
                break;
            } else {
                $addedJobs++;
            }
        }

        return $addedJobs;
    }

    private function readMultiCurl($mc)
    {
        $messagesLeft = -1;
        $answersRead = 0;
        while (($info = curl_multi_info_read($mc, $messagesLeft)) !== false) {
            try {
                $answersRead++;
                $infos = curl_getinfo($info["handle"], CURLINFO_PRIVATE);
                $infos = explode(";", $infos);
                $resulthash = $infos[0];
                $cacheDurationMinutes = intval($infos[1]);
                $name = $infos[2];
                $responseCode = curl_getinfo($info["handle"], CURLINFO_HTTP_CODE);
                $body = "no-result";

                $totalTime = curl_getinfo($info["handle"], CURLINFO_TOTAL_TIME);
                \App\PrometheusExporter::Duration($totalTime, $name);

                if (!App::environment("production"))
                    Log::info(sprintf("Fetched: %s - Status %s - Time %s", curl_getinfo($info["handle"], CURLINFO_EFFECTIVE_URL), $responseCode, $totalTime));

                $error = curl_error($info["handle"]);
                if (!empty($error)) {
                    Log::error($error);
                }

                if ($responseCode < 200 || $responseCode > 299) {
                    Log::debug($resulthash);
                    Log::debug("Got responsecode " . $responseCode . " fetching \"" . curl_getinfo($info["handle"], CURLINFO_EFFECTIVE_URL) . "\n");
                    Log::debug(\curl_multi_getcontent($info["handle"]));
                } else {
                    $body = \curl_multi_getcontent($info["handle"]);
                }

                $this->deliverAnswer(
                    $resulthash,
                    json_encode(["info" => curl_getinfo($info["handle"]), "body" => $body])
                );

                if ($cacheDurationMinutes > 0) {
                    try {
                        Cache::store(self::CACHE_STORE)->put($resulthash, $body, $cacheDurationMinutes * 60);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            } finally {
                \curl_multi_remove_handle($mc, $info["handle"]);
            }
        }
        return [$answersRead, $messagesLeft];
    }

    private function getCurlHandle($job)
    {
        $ch = curl_init();
        curl_setopt_array($ch, MissionOptions::for($job));

        return $ch;
    }

    /**
     * This worker's Redis connection.
     *
     * `protected` so RequestFetcherFailoverTest can drive the loop's Redis
     * calls against a connection it controls, the same way the other seams
     * here are reachable.
     */
    protected function redis()
    {
        return Redis::connection(self::REDIS_CONNECTION);
    }

    public function sig_handler($sig)
    {
        $this->shouldRun = false;
        $this->info("Terminating Process\n");
    }
}