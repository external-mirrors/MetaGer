<?php

namespace App\Models\Configuration;

use App\Models\Authorization\Authorization;
use App\Models\DisabledReason;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\SearchSettings;
use Cookie;
use Log;

/**
 * Stores all available Searchengines for usage
 * Reads in sumas.json configuration file to do so
 */
class Searchengines
{
    /** @var SearchEngine[] */
    public $sumas = [];
    public function __construct()
    {
        $settings = app(SearchSettings::class);

        foreach ($settings->sumasJson->sumas as $name => $info) {
            $path = "App\\Models\\parserSkripte\\" . $info->{"parser-class"};
            // Check if parser exists
            try {
                $configuration = new SearchengineConfiguration($info);
                $this->sumas[$name] = new $path($name, $configuration);
            } catch (\ErrorException $e) {
                Log::error("Konnte " . $info->infos->display_name . " nicht abfragen. " . $e);
                continue;
            }
        }

        $settings = app(SearchSettings::class);
        $engines_in_fokus = $settings->sumasJson->foki->{$settings->fokus}->sumas;

        // Parse user configuration
        foreach ($this->sumas as $name => $suma) {
            $engine_user_setting = Cookie::get($settings->fokus . "_engine_" . $name, null);
            if ($engine_user_setting !== null) {
                if ($engine_user_setting === "off" && $suma->configuration->disabled === false) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::USER_CONFIGURATION;
                }
                if ($engine_user_setting === "on" && $suma->configuration->disabled === true) {
                    $suma->configuration->disabled = false;
                }
            }
        }

        foreach ($this->sumas as $suma) {
            // Disable all searchengines not supported by this fokus
            if (!in_array($suma->name, $engines_in_fokus)) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FOKUS;
                continue;
            }
            // Disable all searchengines not supporting the current locale
            $suma->configuration->applyLocale();

            if (!app(Authorization::class)->canDoAuthenticatedSearch() && $suma->configuration->cost > 0) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::PAYMENT_REQUIRED;
                continue;
            }
            // Disable searchengine if it serves ads and this request is authorized
            if ($suma->configuration->ads && app(Authorization::class)->canDoAuthenticatedSearch()) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::SERVES_ADVERTISEMENTS;
                continue;
            }
            // Disable searchengine if it does not support a possibly defined query filter
            foreach ($settings->queryFilter as $filterName => $filter) {
                if (empty($settings->sumasJson->filter->{"query-filter"}->$filterName->sumas->{$suma->name})) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FILTER;
                    continue 2;
                }
            }
        }

        // Enable Yahoo Ads if query is unauthorized and yahoo is disabled
        if (!app(Authorization::class)->canDoAuthenticatedSearch() && $settings->fokus !== "bilder") {
            if ($this->sumas["yahoo"]->configuration->disabled === true) {
                $this->sumas["yahoo-ads"]->configuration->disabled = false;
            }
        }

        $settings->loadQueryFilter();
        $settings->loadParameterFilter($this);

        foreach ($this->sumas as $suma) {
            // Disable searchengine if it does not support a possibly defined parameter filter
            foreach ($settings->parameterFilter as $filterName => $filter) {
                // We need to check if the searchengine supports the parameter value, too
                if ($filter->value !== null && (empty($filter->sumas->{$suma->name}) || empty($filter->sumas->{$suma->name}->values->{$filter->value}))) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FILTER;
                    continue 2;
                }
            }
        }
    }

    public function getSearchEnginesForFokus()
    {
        $settings = app(SearchSettings::class);
        $engines_in_fokus = $settings->sumasJson->foki->{$settings->fokus}->sumas;
        $sumas = [];
        foreach ($this->sumas as $name => $suma) {
            if (in_array($name, $engines_in_fokus)) {
                $sumas[$name] = $suma;
            }
        }
        return $sumas;
    }
}