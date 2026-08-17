<?php

namespace App\Console\Commands;

use App;
use App\Search\Fetch\MissionOptions;
use Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Log;
use Carbon;

class RequestFetcher extends Command
{
    const HEALTHCHECK_KEY = "fetcher_healthcheck";
    const HEALTHCHECK_FORMAT = "Y-m-d H:i:s";

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
        pcntl_signal(SIGQUIT, [$this, "sig_handler"]);

        // Redis might not be available now
        for ($count = 0; $count < 10; $count++) {
            try {
                Redis::set(self::HEALTHCHECK_KEY, Carbon::now()->format(self::HEALTHCHECK_FORMAT));
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
                Redis::set(self::HEALTHCHECK_KEY, Carbon::now()->format(self::HEALTHCHECK_FORMAT));
                $operationsRunning = true;
                curl_multi_exec($this->multicurl, $operationsRunning);
                $status = $this->readMultiCurl($this->multicurl);
                $answersRead = $status[0];
                $messagesLeft = $status[1];
                $newJobs = $this->checkNewJobs($operationsRunning, $messagesLeft);
                if ($newJobs === 0 && $answersRead === 0) {
                    $this->waitForActivity($operationsRunning);
                }

                if (!$this->shouldRun && $operationsRunning === 0 && Redis::get(FPMGracefulStop::REDIS_FPM_STOPPED_KEY) !== NULL) {
                    break;
                }
            }
        } finally {
            curl_multi_close($this->multicurl);
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
    private function checkNewJobs($operationsRunning, $messagesLeft)
    {
        $newJobs = [];
        if ($operationsRunning === 0 && $messagesLeft === -1) {
            $newJob = Redis::blpop(\App\MetaGer::FETCHQUEUE_KEY, 1);
            if (!empty($newJob)) {
                $newJobs[] = $newJob[1];
            }
        } else {
            $newJobs = Redis::lpop(\App\MetaGer::FETCHQUEUE_KEY, 50);
            if ($newJobs === null)
                $newJobs = [];
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

                Redis::pipeline(function ($pipe) use ($resulthash, $info, $body) {
                    $pipe->lpush($resulthash, json_encode(["info" => curl_getinfo($info["handle"]), "body" => $body]));
                    $pipe->expire($resulthash, 60);
                });

                if ($cacheDurationMinutes > 0) {
                    try {
                        Cache::put($resulthash, $body, $cacheDurationMinutes * 60);
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

    public function sig_handler($sig)
    {
        $this->shouldRun = false;
        $this->info("Terminating Process\n");
    }
}