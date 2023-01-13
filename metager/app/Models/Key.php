<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use \Carbon\Carbon;

class Key
{
    public $key;
    public $status; # Null If Key invalid | false if valid but has no adFreeSearches | true if valid and has adFreeSearches
    private $keyserver = "https://key.metager.de/";
    public $keyinfo;
    const CHANGE_EVERY = 1 * 24 * 60 * 60;

    public function __construct($key, $status = null)
    {
        $this->key = $key;
        $this->status = $status;
        if (\app()->environment() !== "production") {
            $this->keyserver = "https://key.metager.de/";
        }
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
        $url = $this->keyserver . "v2/key/" . urlencode($this->key);
        $result_hash = md5($url . microtime(true));
        $mission = [
            "resulthash" => $result_hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => config("metager.metager.keyserver.user"),
            "password" => config("metager.metager.keyserver.password"),
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
                    if ($this->keyinfo->adFreeSearches > 0 || $this->keyinfo->apiAccess === "unlimited") {
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
        $url = $this->keyserver . "v2/key/" . urlencode($this->key) .  "/request-permission";
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
    public function generateKey($payment = null, $adFreeSearches = null, $key = null, $notes = "")
    {
        $postdata = array(
            'apiAccess' => 'normal',
            'expiresAfterDays' => 365,
            'notes' => $notes
        );
        if (!empty($key)) {
            $postdata["key"] = $key;
        }

        if (!empty($payment)) {
            $postdata["payment"] = $payment;
        } else if (!empty($adFreeSearches)) {
            $postdata["adFreeSearches"] = $adFreeSearches;
        } else {
            return false;
        }

        $url = $this->keyserver . "v2/key/";
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
                CURLOPT_POSTFIELDS => \http_build_query($postdata)
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
                    return $result->{'mgKey'};
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    public function reduce($count)
    {
        $postdata = array(
            'adFreeSearches' => $count,
        );
        $url = $this->keyserver . "v2/key/" . $this->key . "/reduce-searches";
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
                CURLOPT_POSTFIELDS => \http_build_query($postdata)
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
                    return $result;
                }
            }
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
    public function canChange()
    {
        if (empty($this->status) || !preg_match("/^Mitgliederschlüssel\./", $this->keyinfo->notes) || $this->keyinfo->adFreeSearches < \App\Http\Controllers\KeyController::KEYCHANGE_ADFREE_SEARCHES) {
            return false;
        }
        if (!empty($this->keyinfo->KeyChangedAt)) {
            // "2021-03-09T09:19:44.000Z"
            $keyChangedAt = Carbon::createFromTimeString($this->keyinfo->KeyChangedAt, 'Europe/London');
            if ($keyChangedAt->diffInSeconds(Carbon::now()) > self::CHANGE_EVERY) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function checkForChange($hash, $newkey = "")
    {
        $postdata = array(
            'hash' => $hash,
            'key' => $newkey,
        );
        $url = $this->keyserver . "v2/key/can-change";
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
                CURLOPT_POSTFIELDS => \http_build_query($postdata)
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
                    if ($result->status === "success" && empty($result->results)) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }
}
