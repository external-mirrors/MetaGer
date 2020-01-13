<?php

namespace App\Console\Commands;

use Artisan;
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
        $pids = [];
        $pid = null;
        for ($i = 0; $i < 5; $i++) {
            $pid = \pcntl_fork();
            $pids[] = $pid;
            if ($pid === 0) {
                break;
            }
        }
        if ($pid === 0) {
            Artisan::call('requests:cacher');
            exit;
        } else {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, [$this, "sig_handler"]);
            pcntl_signal(SIGTERM, [$this, "sig_handler"]);
            pcntl_signal(SIGHUP, [$this, "sig_handler"]);
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
                    $blocking = false;
                    $active = true;
                }

                $answerRead = false;
                while (($info = curl_multi_info_read($this->multicurl)) !== false) {
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
                        $cacherItem = [
                            'timeSeconds' => $cacheDurationMinutes * 60,
                            'key' => $resulthash,
                            'value' => $body,
                        ];
                        $pipe->rpush(\App\Console\Commands\RequestCacher::CACHER_QUEUE, json_encode($cacherItem));
                    });
                    \curl_multi_remove_handle($this->multicurl, $info["handle"]);
                }
                if (!$active && !$answerRead) {
                    $blocking = true;
                }
            }
        } finally {
            curl_multi_close($this->multicurl);
        }
        foreach ($pids as $tmppid) {
            \pcntl_waitpid($tmppid, $status, WNOHANG);
        }
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
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_MAXCONNECTS => 500,
            CURLOPT_LOW_SPEED_LIMIT => 500,
            CURLOPT_LOW_SPEED_TIME => 5,
            CURLOPT_TIMEOUT => 10,
        ));

        if (!empty($this->proxyhost) && !empty($this->proxyport) && !empty($this->proxyuser) && !empty($this->proxypassword)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyhost);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxyuser . ":" . $this->proxypassword);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxyport);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }

        if (!empty($job["username"]) && !empty($job["password"])) {
            curl_setopt($ch, CURLOPT_USERPWD, $job["username"] . ":" . $job["password"]);
        }

        if (!empty($job["headers"])) {
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
