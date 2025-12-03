<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\Models\SearchEngineInfos;
use App\Models\SearchEngineLanguages;
use LaravelLocalization;
use Log;

class BraveNews extends Searchengine
{
    const CONFIG_OVERLOAD = [
        "lang" => [
            "parameter" => "country",
            "languages" => [],
            "regions" => [
                "de_DE" => "de_DE",
                "de_AT" => "de_AT",
                "en_US" => "en_US",
                "en_GB" => "en_GB",
                "en_AU" => "en_AU",
                "es_ES" => "es_ES",
                "es_MX" => "es_MX",
                "da_DK" => "da_DK",
                "de_CH" => "de_CH",
                "fi_FI" => "fi_FI",
                "it_IT" => "it_IT",
                "nl_NL" => "nl_NL",
                "sv_SE" => "sv_SE",
                "fr_FR" => "fr_FR",
                "fr_CA" => "fr_CA",
                "pl_PL" => "pl_PL",
                "pt_PT" => "pt-pt_PT",
                "pt_BR" => "pt-br_BR",
            ]
        ]
    ];
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);

        $this->configuration->engineBoost = 1.2;
        $this->configuration->cost = 0.8;

        $this->configuration->addQueryParameters([
            "count" => 100,
            "offset" => 0
        ]);

        $this->configuration->infos = new SearchEngineInfos("https://search.brave.com/", "Brave Search", "Brave", "Juni 2021", "San Francisco", "Brave San Francisco", "einige Milliarden");
    }

    public function applySettings()
    {
        parent::applySettings();

        // Setup UI Lang to match users language
        $locale = LaravelLocalization::getCurrentLocale();
        $this->configuration->getParameter->ui_lang = $locale;
        // Brave has divided country search setting and language search setting
        // MetaGer will configure something like de_DE
        // We need to seperate both parameters and put them into their respective get parameters
        if (property_exists($this->configuration->getParameter, "country") && preg_match("/^[^_]+_[^_]+$/", $this->configuration->getParameter->country)) {
            $values = explode("_", $this->configuration->getParameter->country);
            $this->configuration->getParameter->search_lang = $values[0];
            $this->configuration->getParameter->country = $values[1];
        } else {
            $this->configuration->getParameter->search_lang = Localization::getLanguage();
            $this->configuration->getParameter->country = Localization::getRegion();
        }
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);

            // Check if the query got altered
            if (!empty($results->{"query"}) && !empty($results->{"query"}->{"altered"}) && $results->query->altered !== $results->query->original) {
                $this->alteredQuery = $results->{"query"}->{"altered"};
                $override = "";
                $original = trim($results->query->original);
                $wordstart = true;
                $inphrase = false;
                for ($i = 0; $i < strlen($original); $i++) {
                    $char = $original[$i];
                    if ($wordstart && !$inphrase) {
                        $override .= "+";
                    }
                    $override .= $char;
                    if (empty(trim($char))) {
                        $wordstart = true;
                    }
                    if (!empty(trim($char))) {
                        $wordstart = false;
                    }
                    if ($char === "\"") {
                        $inphrase = !$inphrase;
                    }

                }
                $this->alterationOverrideQuery = $override;
            }

            foreach ($results->results as $result) {
                $title = html_entity_decode($result->title);
                $link = $result->url;
                $anzeigeLink = $result->meta_url->netloc . " " . $result->meta_url->path;
                $descr = html_entity_decode($result->description);
                $this->counter++;
                $additionalInformation = [];
                if (property_exists($result, "age")) {
                    $additionalInformation["date_string"] = $result->age;
                }
                if (property_exists($result, "meta_url") && property_exists($result->meta_url, "favicon")) {
                    $additionalInformation["favicon_url"] = $result->meta_url->favicon;
                }
                $newResult = new Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    $additionalInformation
                );

                if (property_exists($result, "thumbnail")) {
                    $newResult->image = $result->thumbnail->src;
                }

                $this->results[] = $newResult;
            }

        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    public function getNext(\App\MetaGer $metager, $result)
    {
        try {
            $results = json_decode($result);

            if (sizeof($results->results) === 0) {
                return;
            }

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->offset += 1;

            $next = new BraveNews($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}