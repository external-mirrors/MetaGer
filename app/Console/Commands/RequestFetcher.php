<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Log;

class RequestFetcher extends Command
{
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
    protected $oldMultiCurl = null;
    protected $maxFetchedDocuments = 1000;
    protected $fetchedDocuments = 0;
    protected $proxyhost, $proxyuser, $proxypassword;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->multicurl = curl_multi_init();
        $this->proxyhost = env("PROXY_HOST", "");
        $this->proxyport = env("PROXY_PORT", "");
        $this->proxyuser = env("PROXY_USER", "");
        $this->proxypassword = env("PROXY_PASSWORD", "");

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $pidFile = "/tmp/fetcher";
        pcntl_signal(SIGINT, [$this, "sig_handler"]);
        pcntl_signal(SIGTERM, [$this, "sig_handler"]);
        pcntl_signal(SIGHUP, [$this, "sig_handler"]);

        // Redis might not be available now
        for ($count = 0; $count < 10; $count++) {
            try {
                Redis::connection();
                break;
            } catch (\Predis\Connection\ConnectionException $e) {
                if ($count >= 9) {
                    // If its not available after 10 seconds we will exit
                    return;
                }
                sleep(1);
            }
        }

        touch($pidFile);

        if (!file_exists($pidFile)) {
            return;
        }

        try {
            $blocking = false;
            while ($this->shouldRun) {
                $status = curl_multi_exec($this->multicurl, $active);
                $currentJob = null;
                if (!$blocking) {
                    $currentJob = Redis::lpop(\App\MetaGer::FETCHQUEUE_KEY);
                } else {
                    $currentJob = Redis::blpop(\App\MetaGer::FETCHQUEUE_KEY, 1);
                    if (!empty($currentJob)) {
                        $currentJob = $currentJob[1];
                    }
                }

                if (!empty($currentJob)) {
                    $currentJob = json_decode($currentJob, true);
                    $ch = $this->getCurlHandle($currentJob);
                    curl_multi_add_handle($this->multicurl, $ch);
                    $this->fetchedDocuments++;
                    if ($this->fetchedDocuments > $this->maxFetchedDocuments) {
                        Log::info("Reinitializing Multicurl after " . $this->fetchedDocuments . " requests.");
                        $this->oldMultiCurl = $this->multicurl;
                        $this->multicurl = curl_multi_init();
                    }
                    $blocking = false;
                    $active = true;
                }

                $answerRead = $this->readMultiCurl($this->multicurl);
                if ($this->oldMultiCurl != null) {
                    $this->readMultiCurl($this->oldMultiCurl);
                }

                if (!$active && !$answerRead) {
                    $blocking = true;
                } else {
                    usleep(50 * 1000);
                }
            }
        } finally {
            unlink($pidFile);
            curl_multi_close($this->multicurl);
        }
    }

    private function readMultiCurl($mc)
    {
        $answerRead = false;
        while (($info = curl_multi_info_read($mc)) !== false) {
            try {
                $answerRead = true;
                $infos = curl_getinfo($info["handle"], CURLINFO_PRIVATE);
                $infos = explode(";", $infos);
                $resulthash = $infos[0];
                $cacheDurationMinutes = intval($infos[1]);
                $responseCode = curl_getinfo($info["handle"], CURLINFO_HTTP_CODE);
                $body = "";

                $error = curl_error($info["handle"]);
                if (!empty($error)) {
                    Log::error($error);
                }

                if ($responseCode !== 200) {
                    Log::debug("Got responsecode " . $responseCode . " fetching \"" . curl_getinfo($info["handle"], CURLINFO_EFFECTIVE_URL) . "\n");
                } else {
                    $body = \curl_multi_getcontent($info["handle"]);
                }

                Redis::pipeline(function ($pipe) use ($resulthash, $body, $cacheDurationMinutes) {
                    $pipe->set($resulthash, $body);
                    $pipe->expire($resulthash, 60);
                });
            } finally {
                \curl_multi_remove_handle($mc, $info["handle"]);
            }
        }
        return $answerRead;
    }

    private function getCurlHandle($job)
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $job["url"],
            CURLOPT_PRIVATE => $job["resulthash"] . ";" . $job["cacheDuration"],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_MAXCONNECTS => 500,
            CURLOPT_LOW_SPEED_LIMIT => 50000,
            CURLOPT_LOW_SPEED_TIME => 2,
            CURLOPT_TIMEOUT => 3,
        ));

        if (!empty($this->proxyhost) && !empty($this->proxyport) && !empty($this->proxyuser) && !empty($this->proxypassword)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyhost);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyuser . ":" . $this->proxypassword);
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
        echo ("Terminating Process\n");
    }

}
