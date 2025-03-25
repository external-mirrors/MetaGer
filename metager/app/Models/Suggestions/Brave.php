<?php

namespace App\Models\Suggestions;

use App\Localization;
use App\Suggestions;
use Exception;

class Brave extends Suggestions
{
    public const NAME = "brave";
    public const COST = 0.1;

    public function __construct(string $query)
    {
        $this->query = $query;
        $this->api_base = "https://api.search.brave.com/res/v1/suggest/search";
        $this->api_header["X-Subscription-Token"] = config("metager.suggestions.brave.api_key");
        $this->api_header["Accept"] = "application/json";
        $this->api_get_parameters = [
            "count" => "10",
            "country" => Localization::getRegion(),
            "lang" => Localization::getLanguage(),
            "q" => $query
        ];
    }

    public function fetch()
    {
        return parent::fetch();
    }

    protected function parseResponse(string $response): void
    {
        try {
            $suggestion_response = json_decode($response, true);
            foreach ($suggestion_response["results"] as $suggest_entry) {
                $this->suggestions[] = $suggest_entry["query"];
            }
        } catch (Exception $e) {
        }
    }
}