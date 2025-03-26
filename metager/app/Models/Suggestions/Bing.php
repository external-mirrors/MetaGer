<?php

namespace App\Models\Suggestions;

use App\Localization;
use App\Suggestions;
use Exception;

class Bing extends Suggestions
{
    public const NAME = "bing";
    public const COST = 0.3;

    public function __construct(string $query)
    {
        $this->query = $query;
        $this->api_base = "https://api.bing.microsoft.com/v7.0/suggestions";
        $this->api_header["Ocp-Apim-Subscription-Key"] = config("metager.suggestions.bing.api_key");
        $this->api_header["Accept"] = "application/json";
        $this->api_get_parameters = [
            "mkt" => Localization::getLanguage() . "-" . Localization::getRegion(),
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
            foreach ($suggestion_response["suggestionGroups"] as $group) {
                if ($group["name"] !== "Web")
                    continue;
                foreach ($group["searchSuggestions"] as $suggestion) {
                    $this->suggestions[] = $suggestion["query"];
                }
            }
        } catch (Exception $e) {
        }
    }
}