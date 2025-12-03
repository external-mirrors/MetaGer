<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\DeepResults\Button;
use App\Models\Result;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\Models\SearchEngineInfos;
use LaravelLocalization;
use Log;

class SerperNews extends Searchengine
{
    const CONFIG_OVERLOAD = [
        "lang" => [
            "parameter" => "gl",
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

        $this->configuration->engineBoost = 1.2;
        $this->configuration->disabledByDefault = false;
        $this->configuration->method = "post_json";
        //$this->configuration->cost = 1;

        $this->configuration->infos = new SearchEngineInfos("https://metager.de/search-engine", "Google", "Serper", null, null, "Serper", "~500,000,000,000");
    }

    public function applySettings()
    {
        parent::applySettings();

        // Setup UI Lang to match users language
        $locale = LaravelLocalization::getCurrentLocale();
        $this->configuration->getParameter->hl = $locale;
        // Brave has divided country search setting and language search setting
        // MetaGer will configure something like de_DE
        // We need to seperate both parameters and put them into their respective get parameters
        if (property_exists($this->configuration->getParameter, "gl") && preg_match("/^[^_]+_[^_]+$/", $this->configuration->getParameter->gl)) {
            $values = explode("_", $this->configuration->getParameter->gl);
            $this->configuration->getParameter->hl = $values[0];
            $this->configuration->getParameter->gl = strtolower($values[1]);
        } else {
            $this->configuration->getParameter->hl = Localization::getLanguage();
            $this->configuration->getParameter->gl = strtolower(Localization::getRegion());
        }
    }

    public function loadResults($result)
    {
        try {
            $results = json_decode($result);


            $news = $results->news;
            foreach ($news as $result) {
                $title = $result->title;
                $link = $result->link;
                $anzeigeLink = $link;
                $descr = $result->snippet;
                $this->counter++;

                $additionalInformation = [];
                if (property_exists($result, "date")) {
                    $additionalInformation["date_string"] = $result->date;
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

                if (property_exists($result, "imageUrl")) {
                    $newResult->image = $result->imageUrl;
                }

                if (property_exists($result, "sitelinks")) {
                    foreach ($result->sitelinks as $index => $clusterMember) {
                        $newResult->deepResults["buttons"][] = new Button($clusterMember->title, $clusterMember->link);
                    }
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
            if ($results !== null) {
                $web = $results->news;
                $num = 10;
                if (property_exists($this->configuration->getParameter, "num")) {
                    $num = $this->configuration->getParameter->num;
                }
                if (sizeof($web) < $num)
                    return;
            }

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $page = 1;
            if (property_exists($newConfiguration->getParameter, "page")) {
                $page = $newConfiguration->getParameter->page;
            }
            $newConfiguration->getParameter->page = $page + 1;

            $next = new Brave($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}