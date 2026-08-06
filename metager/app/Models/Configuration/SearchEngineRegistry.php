<?php

namespace App\Models\Configuration;

use App\Models\Searchengine;

/**
 * Assembles the full, non-secret+secret-merged search engine configuration.
 *
 * Non-secret per-engine config (host/path/infos/cost/...) lives on each
 * parser class's CONFIG_OVERLOAD constant, keyed by engine instance name
 * (a parser class can serve more than one engine, e.g. Mg_produkt2 parses
 * mg_produkt/mg_produkt2/mg_produkt2_ads). config/sumas.json only ever
 * contributes secrets (API keys/tokens, either as request-header or
 * get-parameter entries, and http-auth-credentials), merged on top.
 *
 * config/foki.json (fokus -> engine membership) and config/filters.json
 * (query-filter/parameter-filter definitions incl. per-engine value
 * translations) are cross-cutting, non-secret, and loaded as-is.
 */
class SearchEngineRegistry
{
    /** @var object engine name => merged config object */
    public $sumas;
    /** @var object fokus => {display-name, sumas, main} */
    public $foki;
    /** @var object {query-filter: ..., parameter-filter: ...} */
    public $filter;

    public function __construct()
    {
        $secrets = json_decode(file_get_contents(config_path("sumas.json")));
        if ($secrets === null) {
            throw new \Exception("Cannot load sumas.json file");
        }

        $foki = json_decode(file_get_contents(config_path("foki.json")));
        if ($foki === null) {
            throw new \Exception("Cannot load foki.json file");
        }
        $this->foki = $foki;

        $filter = json_decode(file_get_contents(config_path("filters.json")));
        if ($filter === null) {
            throw new \Exception("Cannot load filters.json file");
        }
        $this->filter = $filter;

        $this->sumas = new \stdClass;
        foreach ($this->scanParserClasses() as $engineName => $entry) {
            [$className, $config] = $entry;
            // Recursively cast the PHP array to stdClass, matching the
            // object shape the rest of the codebase expects from
            // json_decode(). JSON_FORCE_OBJECT keeps empty maps ({}) from
            // collapsing into empty lists ([]).
            $config = json_decode(json_encode($config, JSON_FORCE_OBJECT));
            $config->{"parser-class"} = $className;

            if (property_exists($secrets, $engineName)) {
                $this->mergeSecrets($config, $secrets->{$engineName});
            }

            $this->sumas->{$engineName} = $config;
        }
    }

    private function mergeSecrets(object $config, object $secret): void
    {
        if (property_exists($secret, "request-header")) {
            $base = property_exists($config, "request-header") ? (array) $config->{"request-header"} : [];
            $config->{"request-header"} = (object) array_merge($base, (array) $secret->{"request-header"});
        }
        if (property_exists($secret, "get-parameter")) {
            $base = property_exists($config, "get-parameter") ? (array) $config->{"get-parameter"} : [];
            $config->{"get-parameter"} = (object) array_merge($base, (array) $secret->{"get-parameter"});
        }
        if (property_exists($secret, "http-auth-credentials")) {
            $config->{"http-auth-credentials"} = $secret->{"http-auth-credentials"};
        }
    }

    /**
     * Scans App\Models\parserSkripte\*.php for classes extending
     * Searchengine and collects their CONFIG_OVERLOAD entries.
     *
     * @return array<string, array{0: string, 1: array}> engine name => [class name, config]
     */
    private function scanParserClasses(): array
    {
        $result = [];
        $dir = app_path("Models/parserSkripte");
        foreach (scandir($dir) as $file) {
            if (!str_ends_with($file, ".php")) {
                continue;
            }
            $className = substr($file, 0, -4);
            $fqcn = "App\\Models\\parserSkripte\\" . $className;
            if (!class_exists($fqcn) || !is_subclass_of($fqcn, Searchengine::class)) {
                continue;
            }
            foreach ($fqcn::CONFIG_OVERLOAD as $engineName => $config) {
                $result[$engineName] = [$className, $config];
            }
        }
        return $result;
    }
}
