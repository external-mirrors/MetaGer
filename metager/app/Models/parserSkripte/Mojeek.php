<?php

namespace app\Models\parserSkripte;

use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use Log;

class Mojeek extends Searchengine
{
    const CONFIG_OVERLOAD = [
        'mojeek' => [
            'host' => 'api.mojeek.com',
            'path' => '/search',
            'port' => 443,
            'query-parameter' => 'q',
            'input-encoding' => 'utf8',
            'output-encoding' => 'utf8',
            'get-parameter' => [
                'fmt' => 'json',
            ],
            // Mojeek takes the market as two preferences, `lb` (a language)
            // and `rb` (a region). The map names the whole market and
            // applySettings() splits it, so the market filter can move both.
            'lang' => [
                'parameter' => 'rb',
                'languages' => [],
                'regions' => [
                    'ca_ES' => 'ca_ES',
                    'de_CH' => 'de_CH',
                    'de_DE' => 'de_DE',
                    'de_AT' => 'de_AT',
                    'en_US' => 'en_US',
                    'en_GB' => 'en_GB',
                    'en_AU' => 'en_AU',
                    'en_IE' => 'en_IE',
                    'es_ES' => 'es_ES',
                    'es_MX' => 'es_MX',
                    'da_DK' => 'da_DK',
                    'fi_FI' => 'fi_FI',
                    'it_IT' => 'it_IT',
                    'nl_NL' => 'nl_NL',
                    'sv_SE' => 'sv_SE',
                    'fr_FR' => 'fr_FR',
                    'fr_CA' => 'fr_CA',
                    'pl_PL' => 'pl_PL',
                    'pt_PT' => 'pt_PT',
                    'pt_BR' => 'pt_BR',
                ],
            ],
            'engine-boost' => 1.2,
            'cache-duration' => 0,
            'disabled' => false,
            'filter-opt-in' => false,
            'cost' => 0.3,
            'infos' => [
                'homepage' => 'https://www.mojeek.com/',
                'index_name' => 'Mojeek',
                'display_name' => 'Mojeek',
                'founded' => '2009',
                'headquarter' => 'Sussex Innovation Centre, University of Sussex, Science Park Square, Falmer, Brighton, BN1 9SB',
                'operator' => 'Mojeek Limited',
                'index_size' => '> 5 Milliarden',
            ],
        ],
    ];
    public $results = [];

    public function __construct($name, SearchengineConfiguration $configuration)
    {
        parent::__construct($name, $configuration);

        $this->configuration->getParameter->lbb = 100;
        $this->configuration->getParameter->rbb = 10;

        $this->configuration->disabledByDefault = true;
    }

    /**
     * Split the configured market, `en_GB`, into Mojeek's language and region
     * preferences, `lb=EN` and `rb=GB`.
     *
     * Mojeek ignores a value it does not know rather than rejecting it, so a
     * country sent as a language does not fail, it just stops steering. That is
     * how every locale but de-DE came to send its country code as `lb`.
     */
    public function applySettings()
    {
        parent::applySettings();

        $parameters = $this->configuration->getParameter;
        if (property_exists($parameters, "rb") && preg_match("/^([^_]+)_([^_]+)$/", $parameters->rb, $market)) {
            $parameters->lb = strtoupper($market[1]);
            $parameters->rb = strtoupper($market[2]);
        }
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