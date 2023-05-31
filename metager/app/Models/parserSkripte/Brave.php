<?php

namespace app\Models\parserSkripte;

use App\Localization;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;
use Request;

class Brave extends Searchengine
{
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
        $this->configuration->disabledByDefault = true;
    }

    public function applySettings()
    {
        parent::applySettings();

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

            # Check if the query got altered
            if (!empty($results->{"query"}) && !empty($results->{"query"}->{"altered"}) && $results->query->altered !== $results->query->original) {
                $this->alteredQuery = $results->{"query"}->{"altered"};
                $override = "";
                $original = trim($results->query->original);
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

            $web = $results->web;
            foreach ($web->results as $result) {
                $title = $result->title;
                $link = $result->url;
                $anzeigeLink = $result->meta_url->netloc . " " . $result->meta_url->path;
                $descr = $result->description;
                $this->counter++;
                $this->results[] = new \App\Models\Result(
                    $this->configuration->engineBoost,
                    $title,
                    $link,
                    $anzeigeLink,
                    $descr,
                    $this->configuration->infos->displayName,
                    $this->configuration->infos->homepage,
                    $this->counter,
                    []
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
            $results = json_decode($result);

            if (!$results->query->more_results_available) {
                return;
            }

            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->offset += 1;

            $next = new Brave($this->name, $newConfiguration);
            $this->next = $next;
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}