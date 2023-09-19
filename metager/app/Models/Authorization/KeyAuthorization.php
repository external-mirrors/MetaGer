<?php

namespace App\Models\Authorization;

use App\PrometheusExporter;
use Illuminate\Support\Facades\Redis;

class KeyAuthorization extends Authorization
{
    public $key;
    private $keyserver = "";
    public function __construct($key)
    {
        parent::__construct();
        $this->key = trim($key);
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
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        $result = Redis::blpop($result_hash, 10);
        try {
            if ($result && \is_array($result) && sizeof($result) === 2) {
                $result = \json_decode($result[1]);
                if ($result === null) {
                    return false;
                } else {
                    $this->availableTokens = $result->charge;
                }
            }
        } catch (\ErrorException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function makePayment(int $cost)
    {
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
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);

        if (config('metager.metager.keys.uni_mainz') === $this->key) {
            PrometheusExporter::UpdateMainzKeyStatus($this->availableTokens);
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