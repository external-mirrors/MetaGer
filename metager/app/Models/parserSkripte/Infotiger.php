<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Infotiger extends Searchengine
{
    const RESULTS_PER_PAGE = 10;
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);
    }

    public function loadResults($resultstring)
    {
        $results_json = json_decode($resultstring);
        if (!$this->validateJsonResponse($results_json)) {
            return;
        }


        try {
            foreach ($results_json->response->docs as $result) {

                $title = $result->title;
                $link = $result->url;
                $anzeigeLink = $result->purl;
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
        $results_json = json_decode($result);

        if (!$this->validateJsonResponse($results_json)) {
            // Error parsing JSON response
            return;
        }
        $numFound = 0;
        if (!empty($results_json->response->numFound)) {
            $numFound = $results_json->response->numFound;
        }
        $current_page = intval($this->configuration->getParameter->page);

        // Currently only 20 pages are supported
        // No next page if we reached that
        if ($current_page >= 20) {
            return;
        }

        $current_max_result = (($current_page - 1) * self::RESULTS_PER_PAGE) + sizeof($results_json->response->docs);

        if ($numFound > $current_max_result) {
            // Erstellen des neuen Suchmaschinenobjekts und anpassen des GetStrings:
            /** @var SearchEngineConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->page = $current_page + 1;
            $this->next = new Infotiger($this->name, $newConfiguration);
        }
    }

    /**
     * Checks the returned object if it matches the expected format
     * 
     * @param Object $results_json
     * 
     * @return boolean Whether or not the object is valid
     */
    private function validateJsonResponse($results_json)
    {
        if (
            $results_json === null || // Error parsing JSON response (json_decode returned null)
            empty($results_json) ||
            !property_exists($results_json, 'response') || // Unexpected JSON format (no response object)
            !property_exists($results_json->response, 'docs') || // Unexpected JSON format (no docs object)
            !is_array($results_json->response->docs) // Unexpected JSON format (docs is not an array)
        ) {
            return false;
        } else {
            return true;
        }
    }
}