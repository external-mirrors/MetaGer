<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use Request;
use \Carbon\Carbon;

class Key
{
    public $key;
    public $status; # valid key = true, invalid key = false, unidentified key = null
    private $keyserver = "https://key.metager.de/";
    private $keyinfo;

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
            $this->keyinfo = $result;
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
    public function generateKey($payment)
    {
        $authKey = base64_encode(env("KEY_USER", "test") . ':' . env("KEY_PASSWORD", "test"));
        $postdata = http_build_query(array(
            'payment' => $payment,
            'apiAccess' => 'normal',
            'notes' => 'Fuer ' . $payment . '€ aufgeladen am '. date("d.m.Y"),
            'expiresAfterDays' => 365
        ));
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => [
                    'Content-type: application/x-www-form-urlencoded',
                    'Authorization: Basic ' . $authKey
                ],
                'content' => $postdata,
                'timeout' => 5
            ),
        );

        $context = stream_context_create($opts);

        try {
            $link = $this->keyserver . "v2/key/";
            $result = json_decode(file_get_contents($link, false, $context));
            return $result->{'mgKey'};
        } catch (\ErrorException $e) {
            return false;
        }
    }

    /**
     * Tells if this key is liable to change to a custom key
     * Currently only members are allowed to do so and only every 2 days
     * Also only the original member key is allowed to be changed
     * 
     * @return boolean
     */
    public function canChange(){
        if(empty($this->status) || !preg_match("/^Mitgliederschlüssel\./", $this->keyinfo->notes)){
            return false;
        }
        if(!empty($this->keyinfo->KeyChangedAt)){
            // "2021-03-09T09:19:44.000Z"
            $keyChangedAt = Carbon::createFromTimeString($this->keyinfo->KeyChangedAt, 'Europe/London');
            if($keyChangedAt->diffInSeconds(Carbon::now()) > (2 * 24 * 60 * 60)){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }
}
