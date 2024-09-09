<?php

namespace App\Models\parserSkripte;
use App\Localization;
use App\Models\Searchengine;
use App\Models\SearchEngineInfos;
use Exception;
use Request;
use Log;


class Takeads extends Searchengine
{
    public function __construct($name, \App\Models\SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        // Apply languages
        $this->configuration->setLanguages("countryCode", [], [
            "de_DE" => "de",
            "de_AT" => "at",
            "de_CH" => "ch",
            "da_DK" => "dk",
            "en_US" => "us",
            "en_GB" => "uk",
            "en_IE" => "ie",
            "en_MY" => "my",
            "es_ES" => "es",
            "es_MX" => "mx",
            "fi_FI" => "fi",
            "sv_SE" => "se",
            "it_IT" => "it",
            "nl_NL" => "nl",
            "pl_PL" => "pl",
            "fr_FR" => "fr",
            "fr_CA" => "ca"
        ]);
        $this->configuration->addQueryParameters([
            "languageCode" => strtolower(Localization::getLanguage()),
            "count" => 6,
            "sessionId" => hash_hmac("sha256", Request::ip() . now()->format("Y-m-d"), config("app.key")),
            "deviceId" => hash_hmac("sha256", Request::userAgent() . now()->format("Y-m-d"), config("app.key")),
        ]);

        $this->configuration->addRequestHeaders([
            "Content-Type" => "application/json",
        ]);
        $this->configuration->ads = true;
        $this->configuration->infos = new SearchEngineInfos(
            display_name: "Takeads"
        );
    }

    public function applySettings()
    {
        parent::applySettings();

        $this->configuration->curl_opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($this->configuration->getParameter)
        ];
        $this->configuration->getParameter = new \stdClass;
    }

    public function loadResults($result)
    {
        try {
            $result = json_decode($result);
            foreach ($result->matches as $match) {
                $this->counter++;
                $new_result = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $match->title,
                    $match->trackingLink,
                    $match->displayUrl,
                    $match->description,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    []
                );
                if (property_exists($match, "imageUrl")) {
                    $new_result->image = $match->imageUrl;
                }
                $this->ads[] = $new_result;
            }
        } catch (Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}