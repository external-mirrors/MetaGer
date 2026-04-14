<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Mojeek extends Searchengine
{
    const CONFIG_OVERLOAD = [
        "lang" => [
            "parameter" => "lb",
            "languages" => [],
            "regions" => [
                "de_DE" => "de",
                "de_AT" => "at",
                "en_US" => "us",
                "en_GB" => "gb",
                "en_AU" => "au",
                "es_ES" => "es",
                "es_MX" => "mx",
                "da_DK" => "dk",
                "at_AT" => "at",
                "de_CH" => "ch",
                "fi_FI" => "fi",
                "it_IT" => "it",
                "nl_NL" => "nl",
                "sv_SE" => "se",
                "fr_FR" => "fr",
                "fr_CA" => "ca",
                "pl_PL" => "pl",
                "pt_PT" => "pt-pt_PT",
                "pt_BR" => "pt-br_BR",
            ]
        ]
    ];
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);

        $lang = strtoupper(Localization::getLanguage());
        $this->configuration->getParameter->lb = $lang;
        $this->configuration->getParameter->lbb = 100;
        $region = Localization::getRegion();
        $this->configuration->getParameter->rb = $region;
        $this->configuration->getParameter->rbb = 10;

        $this->configuration->disabledByDefault = true;
    }

    public function loadResults($resultstring)
    {
        $results_json = json_decode($resultstring);
        if (!$results_json === null) {
            return;
        }


        try {
            $results_json = $results_json->response;
            if ($results_json->status !== "OK") {
                return;
            }
            if (property_exists($results_json->head, "results")) {
                $this->totalResults = $results_json->head->results;
            }
            $results = $results_json->results;
            foreach ($results as $result) {

                $title = $result->title;
                $link = $result->url;
                $anzeigeLink = $result->url;
                $descr = $result->desc;
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter
                );
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
            $results_json = json_decode($result)->response;

            $start = $results_json->head->start;
            $count = $results_json->head->{"return"};

            $total = $results_json->head->results;

            if ($start + $count < $total) {
                /** @var SearchEngineConfiguration */
                $newConfiguration = unserialize(serialize($this->configuration));
                $newConfiguration->getParameter->s = $start + $count;
                $this->next = new Mojeek($this->name, $newConfiguration);
            }

        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}