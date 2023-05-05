<?php

namespace App\Console\Commands;

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
    protected $proxyhost;
    protected $proxyuser;
    protected $proxypassword;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->multicurl = curl_multi_init();
        $this->proxyhost = config("metager.metager.fetcher.proxy.host");
        $this->proxyport = config("metager.metager.fetcher.proxy.port");
        $this->proxyuser = config("metager.metager.fetcher.proxy.user");
        $this->proxypassword = config("metager.metager.fetcher.proxy.password");
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
                    usleep(10 * 1000);
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
            $elements = Redis::pipeline(function ($redis) {
                $redis->lrange(\App\MetaGer::FETCHQUEUE_KEY, 0, -1);
                $redis->del(\App\MetaGer::FETCHQUEUE_KEY);
            });
            $newJobs = $elements[0];
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

                $error = curl_error($info["handle"]);
                if (!empty($error)) {
                    Log::error($error);
                }

                if ($responseCode !== 200 && $responseCode !== 201) {
                    Log::debug($resulthash);
                    Log::debug("Got responsecode " . $responseCode . " fetching \"" . curl_getinfo($info["handle"], CURLINFO_EFFECTIVE_URL) . "\n");
                    Log::debug(\curl_multi_getcontent($info["handle"]));
                } else {
                    $body = \curl_multi_getcontent($info["handle"]);
                }

                Redis::pipeline(function ($pipe) use ($resulthash, $body, $cacheDurationMinutes) {
                    $pipe->lpush($resulthash, $body);
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

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $job["url"],
                CURLOPT_PRIVATE => $job["resulthash"] . ";" . $job["cacheDuration"] . ";" . $job["name"],
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_USERAGENT => $job["useragent"],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_MAXCONNECTS => 500,
                CURLOPT_LOW_SPEED_LIMIT => 50000,
                CURLOPT_LOW_SPEED_TIME => 10,
                CURLOPT_TIMEOUT => 10,
            )
        );



        if (!empty($job["curlopts"])) {
            curl_setopt_array($ch, $job["curlopts"]);
        }

        if ((!array_key_exists("proxy", $job) || $job["proxy"] === true) && !empty($this->proxyhost) && !empty($this->proxyport)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyhost);
            if (!empty($this->proxyuser) && !empty($this->proxypassword)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyuser . ":" . $this->proxypassword);
            }
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyport);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        if (!empty($job["username"]) && !empty($job["password"])) {
            curl_setopt($ch, CURLOPT_USERPWD, $job["username"] . ":" . $job["password"]);
        }

        if (!empty($job["headers"]) && sizeof($job["headers"]) > 0) {
            $headers = [];
            foreach ($job["headers"] as $key => $value) {
                $headers[] = $key . ":" . $value;
            }
            # Headers are in the Form:
            # <key>:<value>;<key>:<value>
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        return $ch;
    }

    public function sig_handler($sig)
    {
        $this->shouldRun = false;
        $this->info("Terminating Process\n");
    }
}