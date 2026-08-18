<?php

namespace App\Models\parserSkripte\Base;

use App\Localization;
use App\MetaGer;
use App\Models\Searchengine;
use Log;

/**
 * Everything the Serper endpoints share, so a Serper fact is stated once.
 *
 * `serper_web`, `serper_images`, `serper_news` and `serper_shopping` are four
 * paths on Google's Serper API behind one key, one country map and one billing
 * arrangement. Held as four independent classes they stated all of that four
 * times — and, being copies, they carried each other's mistakes:
 *
 *   - `SerperNews::getNext()` built its second page as `new Brave(...)`, so
 *     page two of a news search was handed to Brave's *web* parser, which reads
 *     a `web` property the Serper news response does not have. No news past the
 *     first page, and an error in the log rather than anywhere a user could see.
 *   - `SerperImages::getNext()` and `SerperShopping::getNext()` said
 *     `new Serper(...)` — the same defect one step less obvious, since Serper's
 *     web parser reads `organic` and an images response has none.
 *
 * Only `Serper` itself paginated as itself. `new static` in one place is what
 * makes that class of mistake unavailable rather than merely absent, and
 * [resultsKey] is the one thing each endpoint has to say for itself.
 *
 * ## This class must never declare CONFIG_OVERLOAD
 *
 * See [BraveBase] — the registry scan reads that constant off every class in
 * `app/Models/parserSkripte` and a subclass inherits it silently. This file
 * lives in a subdirectory the (non-recursive) scan cannot see, and
 * `EngineReachabilityTest::testEveryEngineIsParsedByTheClassThatDeclaresIt`
 * fails if any engine is ever registered against a class that inherited it.
 */
abstract class SerperBase extends Searchengine
{
    /**
     * MetaGer market -> the value Google's `gl` takes for it.
     *
     * Two-letter country codes, except the two Portuguese entries, which carry
     * an underscore and so take the splitting branch in [applySettings].
     *
     * `at_AT` is not a MetaGer market and matches nothing; it is kept because
     * all four classes carried it and removing it is a behaviour question, not
     * a deduplication one.
     */
    protected const REGIONS = [
        "ca_ES" => "ca",
        "de_DE" => "de",
        "de_AT" => "at",
        "en_US" => "us",
        "en_GB" => "gb",
        "en_AU" => "au",
        "es_ES" => "es",
        "es_MX" => "mx",
        "da_DK" => "dk",
        "at_AT" => "at",
        "de_CH" => "ch",
        "fi_FI" => "fi",
        "it_IT" => "it",
        "nl_NL" => "nl",
        "sv_SE" => "se",
        "fr_FR" => "fr",
        "fr_CA" => "ca",
        "pl_PL" => "pl",
        "pt_PT" => "pt-pt_PT",
        "pt_BR" => "pt-br_BR",
    ];

    /**
     * One index behind all four endpoints, so one description of it.
     *
     * This has to be here rather than in a constructor: `SearchEngineRegistry`
     * reads the static config and nothing else, so an engine whose display name
     * exists only imperatively shows up in the app's settings schema as its raw
     * engine name ("serper_web"). That is what happened before these blocks
     * were added, and holding them in one place is what stops the static and
     * imperative copies drifting apart again — there is no imperative copy now.
     */
    protected const INFOS = [
        "homepage" => "https://metager.de/search-engine",
        "index_name" => "Google",
        "display_name" => "Serper",
        "founded" => null,
        "headquarter" => null,
        "operator" => "Serper",
        "index_size" => "~500,000,000,000",
    ];

    /**
     * The part of a Serper engine's config that does not depend on the endpoint.
     *
     * Spread into each subclass's `CONFIG_OVERLOAD`, which adds only its `path`.
     * `method` is in here rather than in a constructor because
     * `SearchengineConfiguration` reads it now — Serper is POST-with-a-JSON-body
     * and was the only reason any parser still needed a constructor at all.
     */
    protected const SHARED_CONFIG = [
        "lang" => [
            "parameter" => "gl",
            "languages" => [],
            "regions" => self::REGIONS,
        ],
        "host" => "google.serper.dev",
        "port" => 443,
        "method" => "post_json",
        "query-parameter" => "q",
        "input-encoding" => "utf8",
        "output-encoding" => "utf8",
        "request-header" => [
            "Accept" => "application/json",
        ],
        "engine-boost" => 1.2,
        "cache-duration" => -1,
        "disabled" => false,
        "filter-opt-in" => false,
        "cost" => 0.2,
        "infos" => self::INFOS,
    ];

    /** The property of a Serper response holding this endpoint's result list. */
    abstract protected function resultsKey(): string;

    /**
     * Google keeps the interface language and the search country in two
     * parameters, `hl` and `gl`; MetaGer configures one market, spelled `de_DE`.
     *
     * Most of [REGIONS] already answers with a bare country, which takes the
     * second branch and fills both halves from the request locale. The
     * underscored Portuguese values take the first.
     */
    public function applySettings()
    {
        parent::applySettings();

        $parameters = $this->configuration->getParameter;

        if (property_exists($parameters, "gl") && preg_match("/^[^_]+_[^_]+$/", $parameters->gl)) {
            [$language, $country] = explode("_", $parameters->gl);
            $parameters->hl = $language;
            $parameters->gl = strtolower($country);
            return;
        }

        $parameters->hl = Localization::getLanguage();
        $parameters->gl = strtolower(Localization::getRegion());
    }

    /**
     * The next page is the same engine one page further on — asked for only
     * when this page came back full, since Serper reports no total.
     */
    public function getNext(MetaGer $metager, $result)
    {
        try {
            $results = json_decode($result);

            if ($results !== null) {
                $page = is_object($results) ? ($results->{$this->resultsKey()} ?? []) : [];
                $pageSize = $this->configuration->getParameter->num ?? 10;
                if (sizeof($page) < $pageSize) {
                    return;
                }
            }

            /** @var \App\Models\SearchengineConfiguration $newConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->page = ($newConfiguration->getParameter->page ?? 1) + 1;

            $this->next = new static($this->name, $newConfiguration);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }
}
