<?php

namespace App\Models\Configuration;

use App\Models\Authorization\Authorization;
use App\Models\DisabledReason;
use App\Models\Searchengine;
use App\Models\SearchengineConfiguration;
use App\SearchSettings;
use Cookie;
use Log;
use Request;

/**
 * Stores all available Searchengines for usage
 * Reads in sumas.json configuration file to do so
 */
class Searchengines
{
    /** @var SearchEngine[] */
    public $sumas = [];

    /** @var DisabledReason[] */
    public $disabledReasons = [];

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

        $engines_in_fokus = $settings->sumasJson->foki->{$settings->fokus}->sumas;

        foreach ($this->sumas as $name => $suma) {
            // Parse user configuration
            // Default mode for this searchengine. Can be overriden by the user configuration
            if ($suma->configuration->disabledByDefault) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::USER_CONFIGURATION;
                $this->disabledReasons[] = DisabledReason::USER_CONFIGURATION;
            }
            // User setting defined via permanent cookie in browser
            $engine_user_setting = Cookie::get($settings->fokus . "_engine_" . $name, null);
            // Temporary User settings defined as URL parameter
            if (Request::has($settings->fokus . "_engine_" . $name) && in_array(Request::input($settings->fokus . "_engine_" . $name), ["on", "off"])) {
                $engine_user_setting = Request::input($settings->fokus . "_engine_" . $name);
            }
            if ($engine_user_setting !== null) {
                if ($engine_user_setting === "off" && $suma->configuration->disabled === false) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::USER_CONFIGURATION;
                    $this->disabledReasons[] = DisabledReason::USER_CONFIGURATION;
                }
                if ($engine_user_setting === "on" && $suma->configuration->disabled === true) {
                    $suma->configuration->disabled = false;
                }
            }
        }

        foreach ($this->sumas as $suma) {
            if (!app(Authorization::class)->canDoAuthenticatedSearch() && $suma->configuration->cost > 0) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::PAYMENT_REQUIRED;
                $this->disabledReasons[] = DisabledReason::PAYMENT_REQUIRED;
                continue;
            }
            // Disable searchengine if it serves ads and this request is authorized
            if ($suma->configuration->ads && app(Authorization::class)->canDoAuthenticatedSearch()) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::SERVES_ADVERTISEMENTS;
                $this->disabledReasons[] = DisabledReason::SERVES_ADVERTISEMENTS;
                continue;
            }
            // Disable all searchengines not supported by this fokus
            if (!in_array($suma->name, $engines_in_fokus)) {
                $suma->configuration->disabled = true;
                $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FOKUS;
                $this->disabledReasons[] = DisabledReason::INCOMPATIBLE_FOKUS;
                continue;
            }
            // Disable all searchengines not supporting the current locale
            $suma->configuration->applyLocale();
        }

        // Enable Yahoo Ads if query is unauthorized and yahoo is disabled
        if (!app(Authorization::class)->canDoAuthenticatedSearch() && $settings->fokus !== "bilder") {
            if ($this->sumas["yahoo"]->configuration->disabled === true) {
                $this->sumas["yahoo-ads"]->configuration->disabled = false;
            }
        }

        $settings->loadQueryFilter();
        $settings->loadParameterFilter($this);
        $authorization = app(Authorization::class);

        $authorization->cost = 0; // Update cost with actual cost that are correct for current engine configuration
        foreach ($this->sumas as $suma) {
            if ($suma->configuration->disabled) {
                continue;
            }
            // Disable searchengine if it does not support a possibly defined query filter
            foreach ($settings->queryFilter as $filterName => $filter) {
                if (empty($settings->sumasJson->filter->{"query-filter"}->$filterName->sumas->{$suma->name})) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FILTER;
                    $this->disabledReasons[] = DisabledReason::INCOMPATIBLE_FILTER;
                    continue 2;
                }
            }
            // Disable searchengine if it does not support a possibly defined parameter filter
            foreach ($settings->parameterFilter as $filterName => $filter) {
                // If a searchengine does not support safesearch and safesearch is set to off
                // it will not get disabled as it is probably the same
                if ($filterName === "safesearch" && $filter->value === "o") {
                    continue;
                }
                // We need to check if the searchengine supports the parameter value, too
                if ($filter->value !== null && (empty($filter->sumas->{$suma->name}) || empty($filter->sumas->{$suma->name}->values->{$filter->value}))) {
                    $suma->configuration->disabled = true;
                    $suma->configuration->disabledReason = DisabledReason::INCOMPATIBLE_FILTER;
                    $this->disabledReasons[] = DisabledReason::INCOMPATIBLE_FILTER;
                    continue 2;
                }
            }
            // Apply final settings to Sumas
            $suma->applySettings();
            if (!$suma->configuration->disabled && $suma->configuration->cost > 0) {
                $authorization->cost += $suma->configuration->cost;
            }
        }
        $authorization->cost = max($authorization->cost, 3);

        uasort($this->sumas, function ($a, $b) {
            if ($a->configuration->engineBoost === $b->configuration->engineBoost) {
                return 0;
            }
            return ($a->configuration->engineBoost > $b->configuration->engineBoost) ? -1 : 1;
        });
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

    public function getEnabledSearchengines()
    {
        $sumas = [];
        foreach ($this->sumas as $suma) {
            if ($suma->configuration->disabled === false) {
                $sumas[] = $suma;
            }
        }
        return $sumas;
    }

    public function getEnabledSearchengine(string $name): Searchengine|null
    {
        if (array_key_exists($name, $this->sumas) && $this->sumas[$name]->configuration->disabled === false) {
            return $this->sumas[$name];
        } else {
            return null;
        }
    }

    /**
     * Is there a disabled searchengine with given reason
     *
     * @return bool
     */
    public function hasDisabledSearchenginesWithReason(DisabledReason $reason)
    {
        foreach ($this->sumas as $suma) {
            if ($suma->configuration->disabled && $suma->configuration->disabledReason == $reason) {
                return true;
            }
        }
        return false;
    }

    public function getSearchCost()
    {
        $cost = 0;
        foreach ($this->sumas as $suma) {
            if (!$suma->configuration->disabled && $suma->configuration->cost > 0) {
                $cost += $suma->configuration->cost;
            }
        }
        return $cost;
    }

    public function checkPagination()
    {
        if (!\Request::has("next") || !\Cache::has(\Request::input("next"))) {
            return;
        }
        $next = unserialize(\Cache::get(\Request::input("next")));
        // Pagination call detected. Disable all Searchengines and replace the searchengines with the cached ones
        foreach ($this->sumas as $suma) {
            $suma->configuration->disabled = true;
            $suma->configuration->disabledReason = DisabledReason::SUMAS_CONFIGURATION;
        }
        foreach ($next["engines"] as $engine) {
            foreach ($this->sumas as $name => $suma) {
                if ($engine instanceof $suma) {
                    $this->sumas[$name] = $engine;
                }
            }
        }
        $settings = app(SearchSettings::class);
        $settings->page = $next["page"];
    }
}