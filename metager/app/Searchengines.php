<?php

namespace App;

use LaravelLocalization;

class Searchengines
{
    private readonly string $suma_file_path;
    private $sumas;

    public readonly array $available_foki;

    public function __construct()
    {
        $this->suma_file_path = \config_path("sumas.json");
        $this->sumas = \json_decode(\file_get_contents($this->suma_file_path));

        $this->available_foki = $this->parse_available_foki();
    }

    private function parse_available_foki()
    {
        // Current Locale to decide which searchengines are available
        $current_locale = LaravelLocalization::getCurrentLocaleRegional();
        $current_lang = Localization::getLanguage();

        $foki = [];
        foreach ($this->sumas->foki as $fokus => $fokus_data) {
            foreach ($fokus_data->sumas as $fokus_engine) {
                $suma_data = $this->sumas->sumas->$fokus_engine;
                // Check if this engine supports the current locale
                // Skip if language support is not defined
                if (!\property_exists($suma_data, "lang") || !\property_exists($suma_data->lang, "languages") || !\property_exists($suma_data->lang, "regions")) {
                    continue;
                }
                // Skip if engine does not support current locale or region (locale i.e. en is enough to get enabled)
                if (
                    !\property_exists($suma_data->lang->languages, $current_lang) &&
                    !\property_exists($suma_data->lang->regions, $current_locale)
                ) {
                    continue;
                }
                $foki[] = $fokus;
                break;
            }
        }
        return $foki;
    }
}
