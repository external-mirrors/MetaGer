<?php

namespace App;

use App\Models\SearchengineConfiguration;
use App\Services\ChatBackend;
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
            // Foki with no engines (e.g. "chat") aren't locale-filtered searches at all —
            // include them unconditionally instead of falling through the per-engine loop below,
            // which would otherwise never append them.
            if (empty($fokus_data->sumas)) {
                // ...except chat, which depends on an external service. Offering the tab while
                // metager-chat is unreachable produces a focus that cannot work — previously an
                // iframe rendering MetaGer's own 404 inside MetaGer's chrome. Hiding it is a
                // cached Redis read on the hot path; the actual health call happens at most once
                // per cache window per node, and fails closed.
                if ($fokus === "chat" && !app(ChatBackend::class)->isAvailable()) {
                    continue;
                }
                $foki[] = $fokus;
                continue;
            }
            foreach ($fokus_data->sumas as $fokus_engine) {
                $suma_data = $this->sumas->sumas->$fokus_engine;

                $engine_configuration = new SearchengineConfiguration($suma_data);

                // Check if this engine supports the current locale
                // Skip if language support is not defined
                if (!\property_exists($engine_configuration, "languages") || !\property_exists($engine_configuration->languages, "languages") || !\property_exists($engine_configuration->languages, "regions")) {
                    continue;
                }
                // Skip if engine does not support current locale or region (locale i.e. en is enough to get enabled)
                if (
                    !\property_exists($engine_configuration->languages->languages, $current_lang) &&
                    !\property_exists($engine_configuration->languages->regions, $current_locale)
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
