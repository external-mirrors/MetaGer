<?php

namespace App\Models\Authorization;

use App\PrometheusExporter;
use App\Support\RedisFailover;
use Illuminate\Support\Facades\Redis;

class KeyAuthorization extends Authorization
{
    public $key;
    private $keyserver = "";
    public function __construct($key)
    {
        parent::__construct();
        $this->key = trim($key);
        if (!empty($this->key)) {
            $this->loggedIn = true;
        }
        // Use Keymanager Server from .env if defined or App URL otherwise
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";

        $this->fetchKeyData();
    }

    public function fetchKeyData()
    {
        if (empty($this->key)) {
            return;
        }
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
            "proxy" => false,
            // Don't use Http Proxy if defined in .env
            "name" => "Key Login",
        ];
        $mission = json_encode($mission);
        // The Keymanager login round trip. Retried across a failover rather
        // than answered with an error: a planned drain must not log anyone
        // out or make a key look unusable. GlitchTip issue 1159/1160 is this
        // call site during the 2026-09-08 drains.
        RedisFailover::retry(fn() => Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission));

        $result = RedisFailover::retry(fn() => Redis::brpop($result_hash, 10));
        try {
            if ($result && \is_array($result) && sizeof($result) === 2) {
                $result = \json_decode($result[1]);
                $body = json_decode($result->body);
                if ($body === null) {
                    return false;
                } else {
                    $this->availableTokens = $body->charge;
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function makePayment(float $cost): bool
    {
        $cost = round($cost, 1);
        if (!$this->canDoAuthenticatedSearch()) {
            return false;
        }
        $url = $this->keyserver . "/key/" . urlencode($this->key) . "/discharge";
        $result_hash = md5($url . microtime(true));
        $mission = [
            "resulthash" => $result_hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "headers" => [
                "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
                "Content-Type" => "application/json",
            ],
            "cacheDuration" => 0,
            "name" => "Key Login",
            "proxy" => false,
            // Don't use Http Proxy if defined in .env
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(["amount" => $cost])
            ]
        ];
        $mission = json_encode($mission);
        RedisFailover::retry(fn() => Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission));


        /** @var array $uniMainzKeys */
        $uniMainzKeys = config('metager.metager.keys.uni_mainz', []);
        if (in_array($this->key, $uniMainzKeys)) {
            PrometheusExporter::UpdateKeyStatus(key: $this->key, tokens: $this->availableTokens, owner: "mainz");
        }

        return true;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->key;
    }
}