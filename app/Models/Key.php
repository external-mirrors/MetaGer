<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use Request;

class Key
{
    public $key;
    public $status; # valid key = true, invalid key = false, unidentified key = null
    private $keyserver = "https://key.metager.de/";

    public function __construct($key, $status = null)
    {
        $this->key = $key;
        $this->status = $status;
        if (getenv("APP_ENV") !== "production") {
            $this->keyserver = "https://dev.key.metager.de/";
        }
    }

    # always returns true or false
    public function getStatus()
    {
        if ($this->key !== '' && $this->status === null) {
            $this->updateStatus();
            if(empty($this->status)){
                // The user provided an invalid key which we will log to fail2ban
                $fail2banEnabled = config("metager.metager.fail2ban_enabled");
                if(empty($fail2banEnabled) || !$fail2banEnabled || !env("fail2banurl", false) || !env("fail2banuser") || !env("fail2banpassword")){
                    return false;
                }

                // Submit fetch job to worker
                $mission = [
                        "resulthash" => "captcha",
                        "url" => env("fail2banurl") . "/mgkeytry/",
                        "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
                        "username" => env("fail2banuser"),
                        "password" => env("fail2banpassword"),
                        "headers" => [
                            "ip" => Request::ip()
                        ],
                        "cacheDuration" => 0,
                        "name" => "Captcha",
                    ];
                $mission = json_encode($mission);
                Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            }
        }
        if ($this->status === null || $this->status === false) {
            return false;
        } else {
            return true;
        }
    }

    
    public function updateStatus()
    {
        $authKey = base64_encode(env("KEY_USER", "test") . ':' . env("KEY_PASSWORD", "test"));

        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Basic ' . $authKey ,
            ),
        );
        $context = stream_context_create($opts);

        try {
            $link = $this->keyserver . "v2/key/". urlencode($this->key);
            $result = json_decode(file_get_contents($link, false, $context));
            if ($result->{'apiAccess'} == 'unlimited') {
                $this->status = true;
                return true;
            } else if ($result->{'apiAccess'} == 'normal' && $result->{'adFreeSearches'} > 0){
                $this->status = true;
                return true;
            } else {
                $this->status = false;
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function requestPermission()
    {
        $authKey = base64_encode(env("KEY_USER", "test") . ':' . env("KEY_PASSWORD", "test"));
        $postdata = http_build_query(array(
            'dummy' => 0,
        ));
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => [
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Basic ' . $authKey
                ],
                'content' => $postdata,
            ),
        );

        $context = stream_context_create($opts);

        try {
            $link = $this->keyserver . "v2/key/". urlencode($this->key) . "/request-permission";
            $result = json_decode(file_get_contents($link, false, $context));
            if ($result->{'apiAccess'} == true) {
                return true;
            } else {
                $this->status = false;
                return false;
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }
}
