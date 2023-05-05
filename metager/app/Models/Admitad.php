<?php

namespace App\Models;

use App\Localization;
use App\Models\Configuration\Searchengines;
use Cache;
use Illuminate\Support\Facades\Redis;
use Log;

class Admitad
{
    const COUNTRIES = [
        "af",
        "al",
        "dz",
        "um",
        "as",
        "vi",
        "ad",
        "ao",
        "ai",
        "ag",
        "ar",
        "am",
        "aw",
        "az",
        "au",
        "eg",
        "gq",
        "et",
        "bs",
        "bh",
        "bd",
        "bb",
        "be",
        "bz",
        "bj",
        "bm",
        "bt",
        "bo",
        "ba",
        "bw",
        "bv",
        "br",
        "vg",
        "io",
        "bn",
        "bg",
        "bf",
        "bi",
        "cl",
        "cn",
        "ck",
        "cr",
        "ci",
        "dk",
        "de",
        "dm",
        "do",
        "dj",
        "ec",
        "sv",
        "er",
        "ee",
        "eu",
        "fk",
        "fo",
        "fj",
        "fi",
        "fr",
        "gf",
        "pf",
        "tf",
        "ga",
        "gm",
        "ge",
        "gh",
        "gi",
        "gd",
        "gr",
        "gb",
        "uk",
        "gl",
        "gp",
        "gu",
        "gt",
        "gn",
        "gw",
        "gy",
        "ht",
        "hm",
        "hn",
        "hk",
        "in",
        "id",
        "iq",
        "ir",
        "ie",
        "is",
        "il",
        "it",
        "jm",
        "sj",
        "jp",
        "ye",
        "jo",
        "yu",
        "ky",
        "kh",
        "cm",
        "ca",
        "cv",
        "kz",
        "qa",
        "ke",
        "kg",
        "ki",
        "cc",
        "co",
        "km",
        "cg",
        "cd",
        "hr",
        "cu",
        "kw",
        "la",
        "ls",
        "lv",
        "lb",
        "lr",
        "ly",
        "li",
        "lt",
        "lu",
        "mo",
        "mg",
        "mw",
        "my",
        "mv",
        "ml",
        "mt",
        "mp",
        "ma",
        "mh",
        "mq",
        "mr",
        "mu",
        "yt",
        "mk",
        "mx",
        "fm",
        "md",
        "mc",
        "mn",
        "ms",
        "mz",
        "mm",
        "na",
        "nr",
        "np",
        "nc",
        "nz",
        "ni",
        "nl",
        "an",
        "ne",
        "ng",
        "nu",
        "kp",
        "nf",
        "no",
        "om",
        "tp",
        "at",
        "pk",
        "pw",
        "ps",
        "pa",
        "pg",
        "py",
        "pe",
        "ph",
        "pn",
        "pl",
        "pt",
        "pr",
        "re",
        "rw",
        "ro",
        "ru",
        "st",
        "sb",
        "zm",
        "ws",
        "sm",
        "sa",
        "se",
        "ch",
        "sn",
        "sc",
        "sl",
        "zw",
        "sg",
        "sk",
        "si",
        "so",
        "es",
        "lk",
        "sh",
        "kn",
        "lc",
        "pm",
        "vc",
        "sd",
        "sr",
        "za",
        "kr",
        "sz",
        "sy",
        "tj",
        "tw",
        "tz",
        "th",
        "tg",
        "to",
        "tt",
        "td",
        "cz",
        "tn",
        "tm",
        "tc",
        "tv",
        "tr",
        "us",
        "ug",
        "ua",
        "xx",
        "hu",
        "uy",
        "uz",
        "vu",
        "va",
        "ve",
        "ae",
        "vn",
        "wf",
        "cx",
        "by",
        "eh",
        "ww",
        "zr",
        "cf",
        "cy",
    ];


    public $hash;
    public $finished = false; // Is true when the Request was sent to and read from Admitad App
    private $affiliates = null;
    private $startTime;

    /**
     * Creates a new Adgoal object which will start a request for affiliate links
     * based on a result List from MetaGer.
     * It will parse the Links of the results and query any affiliate shops.
     * 
     * @param \App\MetaGer $metager
     */
    public function __construct(&$metager)
    {
        $publicKey = Localization::getLanguage() === "de" ? \config("metager.metager.admitad.germany_public_key") : \config("metager.metager.admitad.international_public_key");

        if ($publicKey === false) {
            return true;
        }
        $postData = [
            "iris" => [],
            "subId" => Localization::getLanguage() === "de" ? "metager_de" : "metager_org",
            "withImages" => true,
        ];
        foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
            foreach ($engine->results as $result) {
                if (!$result->new) {
                    continue;
                }
                $link = $result->originalLink;
                if (strpos($link, "http") !== 0) {
                    $link = "http://" . $link;
                }
                $postData["iris"][] = $link;
            }
        }
        if (empty($postData["iris"])) {
            return;
        }

        $link = "https://api.monetize.admitad.com/v1/product/monetize-api/v1/resolve";

        // Submit fetch job to worker
        $this->hash = hash("sha256", \json_encode($postData));
        $mission = [
            "resulthash" => $this->hash,
            "url" => $link,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "username" => null,
            "password" => null,
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $publicKey",
            ],
            "cacheDuration" => 60,
            "name" => "Admitad",
            "curlopts" => [
                \CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => \json_encode($postData),
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
    }

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

        if ($answer === null) {
            return;
        }

        $json_answer = \json_decode($answer, true);
        if ($json_answer === null) {
            Log::error("Invalid JSON: $answer");
        }

        // If the fetcher had an Error
        if ($json_answer === null || $json_answer === "no-result" || !\array_key_exists("data", $json_answer)) {
            $this->affiliates = [];
            return;
        } else {
            $json_answer = $json_answer["data"];
        }

        if (!is_array($json_answer)) {
            $this->affiliates = [];
            return;
        }

        $this->affiliates = $json_answer;
    }

    /**
     * Converts all Affiliate Links.
     * 
     * @param \App\Models\Result[] $results
     */
    public function parseAffiliates()
    {
        if ($this->affiliates === null) {
            return;
        }

        foreach ($this->affiliates as $partnershop) {
            $targetUrl = $partnershop["iri"];

            $targetHost = parse_url($targetUrl, PHP_URL_HOST);

            foreach (app(Searchengines::class)->getEnabledSearchengines() as $engine) {
                foreach ($engine->results as $result) {
                    if ($result->originalLink === $targetUrl && !$result->partnershop) {
                        # Ein Advertiser gefunden
                        # Check ob er auf der Blacklist steht
                        if (Redis::hexists(\App\Http\Controllers\AdgoalController::REDIS_BLACKLIST_KEY, $targetHost)) {
                            continue;
                        }

                        if ($result->image !== "") {
                            $result->logo = $partnershop["imageUrl"];
                        } else {
                            $result->image = $partnershop["imageUrl"];
                        }

                        # Den Link hinzufügen:
                        # Redirect the user over our tracker
                        # see \App\Http\Controllers\AdgoalController 
                        # for more information
                        $result->link = \App\Http\Controllers\AdgoalController::generateRedirectUrl($partnershop["deeplink"], $targetUrl);
                        $result->partnershop = true;
                        $result->changed = true;
                    }
                }
            }
        }

        $this->finished = true;
    }
}