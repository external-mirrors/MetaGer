<?php

namespace App\Models;

use App\Localization;
use Cache;
use Log;
use LaravelLocalization;
use Illuminate\Support\Facades\Redis;

class Admitad
{
    const VALID_LANGS = [
        "de",
        "en"
    ];

    public $hash;
    public $finished = false; // Is true when the Request was sent to and read from Admitad App
    private $affiliates = null;

    /**
     * Creates a new Admitad object which will start a request for affiliate links
     * based on a result List from MetaGer.
     * It will parse the Links of the results and query any affiliate shops.
     * 
     * @param \App\MetaGer $metager
     */
    public function __construct(&$metager)
    {
        $results = $metager->getResults();
        // Generate a list of URLs
        $resultLinks = [];
        foreach ($results as $result) {
            if ($result->new) {
                $resultLinks[] = $result->originalLink;
            }
        }

        if (empty($resultLinks)) {
            return;
        }

        $lang = Localization::getLanguage();
        if (!in_array($lang, self::VALID_LANGS)) {
            $lang = "de";
        }

        $requestData = [
            "lang" => $lang,
            "urls" => $resultLinks,
        ];
        $requestData = json_encode($requestData);
        $this->hash = md5($requestData);

        $url = "https://direct.metager.de/check";
        $token = config("metager.metager.admitad.token");

        // Submit fetch job to worker
        $mission = [
            "resulthash" => $this->hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => null,
            "password" => null,
            "headers" => [
                "Authorization" => "Bearer $token"
            ],
            "cacheDuration" => 60,
            "name" => "Admitad",
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $requestData
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }

    /**
     * Fetches the Admitad Response from Redis
     * @param Boolean $wait Whether or not to wait for a response
     */
    public function fetchAffiliates($wait = false)
    {
        if ($this->affiliates !== null) {
            return;
        }

        $answer = null;
        $startTime = microtime(true);
        if ($wait) {
            while (microtime(true) - $startTime < 5) {
                $answer = Cache::get($this->hash);
                if ($answer === null) {
                    usleep(50 * 1000);
                } else {
                    break;
                }
            }
        } else {
            $answer = Cache::get($this->hash);
        }
        $answer = json_decode($answer, true);

        // If the fetcher had an Error
        if ($answer === "no-result") {
            $this->affiliates = [];
            return;
        }

        if (empty($answer) || !isset($answer["error"]) || $answer["error"] || !is_array($answer["result"])) {
            return;
        }

        $this->affiliates = $answer["result"];
    }

    /**
     * Converts all Affiliate Links.
     * 
     * @param \App\Models\Result[] $results
     */
    public function parseAffiliates(&$results)
    {
        if ($this->finished || $this->affiliates === null) {
            return;
        }
        foreach ($this->affiliates as $linkResult) {
            $originalUrl = $linkResult["originalUrl"];
            $redirUrl = $linkResult["redirUrl"];
            $image = $linkResult["image"];

            if (empty($redirUrl)) {
                // No Partnershop
                continue;
            }

            foreach ($results as $result) {
                if ($result->originalLink === $originalUrl && (config("metager.metager.affiliate_preference", "adgoal") === "admitad" || !$result->partnershop)) {
                    # Ein Advertiser gefunden
                    if ($result->image !== "" && !$result->partnershop) {
                        $result->logo = $image;
                    } else {
                        $result->image = $image;
                    }

                    # Den Link hinzufügen:
                    $result->link = $redirUrl;
                    $result->partnershop = true;
                    $result->changed = true;
                }
            }
        }
        $this->finished = true;
    }
}
