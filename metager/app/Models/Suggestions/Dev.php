<?php

namespace App\Models\Suggestions;

use App\Localization;
use App\Suggestions;
use Exception;

class Dev extends Suggestions
{
    public const NAME = "dev";
    public const DISABLED = true;
    public $cost = 0;

    public function __construct(string $query)
    {
        $this->query = $query;
        $this->api_useragent = "Mozilla/5.0 (X11; Linux x86_64; rv:136.0) Gecko/20100101 Firefox/136.0";
        $this->api_base = config("metager.suggestions.dev.api_base");
        $this->api_get_parameters = array_merge(config("metager.suggestions.dev.api_get_parameters"), [
            config("metager.suggestions.dev.api_lang_parameter_name") => Localization::getLanguage() . "-" . Localization::getRegion(),
            "q" => $query
        ]);
    }

    public function fetch()
    {
        return parent::fetch();
    }

    protected function parseResponse(string $response): void
    {
        try {
            $suggestion_response = json_decode($response, true);
            $this->suggestions = $suggestion_response[1];
        } catch (Exception $e) {
        }
    }

}