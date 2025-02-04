<?php

namespace App\Models\Suggestions;

use App\Localization;
use App\Suggestions;

class Serper extends Suggestions
{
    public const NAME = "serper";

    public function __construct(string $query)
    {
        $this->query = $query;
        $this->api_method_post = true;
        $this->api_base = "https://google.serper.dev/autocomplete";
        $this->api_post_data = json_encode([
            "q" => $query,
            "gl" => Localization::getRegion(),
            "hl" => Localization::getLanguage()
        ]);
        $this->api_header[] = "X-API-KEY: " . config("metager.suggestions.serper.api_key");
        $this->api_header[] = "Content-Type: application/json";
    }

    public function fetch()
    {
        return parent::fetch();
    }

    protected function parseResponse(string $response): void
    {
        try {
            $suggestion_response = json_decode($response, true);
            $result = [];
            foreach ($suggestion_response["suggestions"] as $suggestion) {
                $this->suggestions[] = $suggestion["value"];
            }
        } catch (Exception $e) {
        }
    }
}