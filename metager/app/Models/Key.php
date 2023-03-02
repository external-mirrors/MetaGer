<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use \Carbon\Carbon;

class Key
{
    public $key;
    public $status; # Null If Key invalid | false if valid but has no adFreeSearches | true if valid and has adFreeSearches
    private $keyserver = "";
    public $keyinfo;
    public function __construct($key, $status = null)
    {
        $this->key = $key;
        $this->status = $status;
        $this->keyserver = config("metager.metager.keymanager.server") . "/keys/api/json";
    }

    # always returns true or false
    public function getStatus()
    {
        if ($this->key !== '' && $this->status === null) {
            $this->updateStatus();
            if ($this->status === null) {
                // The user provided an invalid key which we will log to fail2ban
                $fail2banEnabled = config("metager.metager.fail2ban.enabled");
                if (!empty($fail2banEnabled) && $fail2banEnabled && !config("metager.metager.fail2ban.url") && !config("metager.metager.fail2ban.user") && !config("metager.metager.fail2ban.password")) {
                    // Submit fetch job to worker
                    $mission = [
                        "resulthash" => "captcha",
                        "url" => config("metager.metager.fail2ban.url") . "/mgkeytry/",
                        "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                        "username" => config("metager.metager.fail2ban.user"),
                        "password" => config("metager.metager.fail2ban.password"),
                        "headers" => [
                            "ip" => \request()->ip()
                        ],
                        "cacheDuration" => 0,
                        "name" => "Captcha",
                    ];
                    $mission = json_encode($mission);
                    Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
                }
            }
        }
        return $this->status;
    }

    public function updateStatus()
    {
        // Submit fetch job to worker
        $url = $this->keyserver . "/key/" . urlencode($this->key);
        $result_hash = md5($url . microtime(true));
        $mission = [
            "resulthash" => $result_hash,
            "url" => $url,
            "headers" => [
                "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")
            ],
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "cacheDuration" => 0,
            "name" => "Key Login",
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        $result = Redis::blpop($result_hash, 10);
        try {
            if ($result && \is_array($result) && sizeof($result) === 2) {
                $result = \json_decode($result[1]);
                if ($result === null) {
                    return false;
                } else {
                    $this->keyinfo = $result;
                    $this->key = $this->keyinfo->key;
                    if ($this->keyinfo->charge > 0) {
                        $this->status = true;
                    } else {
                        $this->status = false;
                    }
                    return true;
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function requestPermission()
    {
        $url = $this->keyserver . "v2/key/" . urlencode($this->key) . "/request-permission";
        $result_hash = md5($url . microtime(true));
        $mission = [
            "resulthash" => $result_hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => config("metager.metager.keyserver.user"),
            "password" => config("metager.metager.keyserver.password"),
            "cacheDuration" => 0,
            "name" => "Key Login",
            "headers" => [
                'Content-type' => "application/x-www-form-urlencoded"
            ],
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => \http_build_query(["dummy" => 0])
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        $result = Redis::blpop($result_hash, 10);
        try {
            if ($result && \is_array($result) && sizeof($result) === 2) {
                $result = \json_decode($result[1]);
                if ($result === null) {
                    return false;
                } else {
                    if ($result->{'apiAccess'} == true) {
                        return true;
                    } else {
                        $this->status = false;
                        return false;
                    }
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }
}