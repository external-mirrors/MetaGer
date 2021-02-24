<?php

namespace App\Models;

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

    private $hash;

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
        foreach($results as $result){
            $resultLinks[] = $result->originalLink;
        }

        $lang = LaravelLocalization::getCurrentLocale();
        if(!in_array($lang, self::VALID_LANGS)){
            $lang = "de";
        }

        $requestData = [
            "lang" => $lang,
            "urls" => $resultLinks,
        ];
        $requestData = json_encode($requestData);
        $this->hash = md5($requestData);

        $url = "https://direct.metager.de/check";
        $token = env("ADMITAD_TOKEN", "");

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
     * Parses the response from Admitad server and converts all Affiliate Links.
     * 
     * @param \App\Models\Result[] $results
     */
    public function parseAffiliates(&$results){
        $answer = Cache::get($this->hash);
        $answer = json_decode($answer, true);

        if(empty($answer) || !isset($answer["error"]) || $answer["error"] || !is_array($answer["result"])){
            return;
        }

        foreach($answer["result"] as $linkResult){
            $originalUrl = $linkResult["originalUrl"];
            $redirUrl = $linkResult["redirUrl"];
            $image = $linkResult["image"];

            if(empty($redirUrl)){
                // No Partnershop
                continue;
            }

            foreach ($results as $result) {
                if ($result->originalLink === $originalUrl) {
                    # Ein Advertiser gefunden
                    if ($result->image !== "") {
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

        Log::info("tzewst");
    }
}
