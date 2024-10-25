<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\DeepResults\Button;
use App\Models\DeepResults\Imagesearchdata;
use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\Models\SearchEngineInfos;
use App\Models\SearchEngineLanguages;
use LaravelLocalization;
use Log;
use Request;

class BraveImages extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        $this->configuration->disabledByDefault = false;

        $this->configuration->engineBoost = 1.2;
        $this->configuration->cost = 1;

        $this->configuration->addQueryParameters([
            "count" => 100,
            "offset" => 0
        ]);

        $this->configuration->setLanguages("country", [], [
            "de_DE" => "DE",
            "de_AT" => "AT",
            "en_US" => "US",
            "en_GB" => "GB",
            "es_ES" => "ES",
            "es_MX" => "MX",
            "da_DK" => "DK",
            "at_AT" => "AT",
            "de_CH" => "CH",
            "fi_FI" => "FI",
            "it_IT" => "IT",
            "nl_NL" => "NL",
            "sv_SE" => "SE",
            "fr_FR" => "FR",
            "fr_CA" => "CA",
            "pl_PL" => "PL"
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
                $descr = null;
                $this->counter++;
                $newResult = new Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    [
                        "image" => new Imagesearchdata($result->thumbnail->src, 0, 0, $result->properties->url, 0, 0),
                    ]
                );

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

            if (!$results->query->more_results_available) {
                return;
            }

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->offset += 1;

            $next = new BraveImages($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}