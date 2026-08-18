<?php

namespace App\Models\parserSkripte\Base;

use App\Localization;
use App\MetaGer;
use App\Models\Searchengine;
use LaravelLocalization;
use Log;

/**
 * Everything the Brave endpoints share, so a Brave fact is stated once.
 *
 * `brave`, `brave_images` and `brave_news` are three paths on one API with one
 * key, one region list and one set of interface languages. Held as three
 * independent classes they drifted, and the drift was invisible: the
 * country/`search_lang` split and `getNext()` were byte-identical copies, and
 * the `ui_lang` handling was too until the day Brave's language enum turned out
 * not to contain every locale MetaGer supports. That fix reached `Brave` and
 * not its two siblings, which would have gone on sending `ui_lang=ca-ES` — a
 * value Brave does not define — for every Catalan image and news search.
 *
 * ## This class must never declare CONFIG_OVERLOAD
 *
 * `SearchEngineRegistry::scanParserClasses()` reads `$fqcn::CONFIG_OVERLOAD`
 * off every class in `app/Models/parserSkripte`, and a class constant is
 * *inherited* when the child does not redeclare it. So a subclass that forgets
 * its own `CONFIG_OVERLOAD` does not fail — it silently re-registers its
 * parent's engine name under its own parser class, and because the scan walks
 * `scandir()` in alphabetical order the later file wins. `BraveImages` would
 * quietly take over the `brave` engine and parse web results as images.
 *
 * Two things keep that from happening. This file lives in a subdirectory, and
 * the scan is not recursive, so an abstract base is invisible to it. And
 * `EngineReachabilityTest::testEveryEngineIsParsedByTheClassThatDeclaresIt`
 * fails if any engine's `parser-class` is a class that inherited the constant
 * rather than declaring it.
 *
 * Subclasses supply their own `CONFIG_OVERLOAD` by spreading [SHARED_CONFIG]
 * and naming what differs — the path and the page size — plus a `loadResults()`
 * for their own response shape.
 */
abstract class BraveBase extends Searchengine
{
    /**
     * The values Brave accepts for `ui_lang`, from its API documentation.
     *
     * Not every MetaGer locale is in here: `ca-ES` is the first one that is
     * not. An unlisted locale means the parameter is left off entirely rather
     * than sent with a value Brave would reject — see [applySettings].
     */
    protected const SUPPORTED_UI_LANGUAGES = ["es-AR", "en-AU", "de-AT", "nl-BE", "fr-BE", "pt-BR", "en-CA", "fr-CA", "es-CL", "da-DK", "fi-FI", "fr-FR", "de-DE", "el-GR", "zh-HK", "en-IN", "en-ID", "it-IT", "ja-JP", "ko-KR", "en-MY", "es-MX", "nl-NL", "en-NZ", "no-NO", "zh-CN", "pl-PL", "en-PH", "ru-RU", "en-ZA", "es-ES", "sv-SE", "fr-CH", "de-CH", "zh-TW", "tr-TR", "en-GB", "en-US", "es-US"];

    /**
     * MetaGer market -> the value Brave's `country` parameter takes for it.
     *
     * Mostly an identity map; Portuguese is the exception, because Brave
     * distinguishes the two variants by language rather than by country and
     * [applySettings] splits this value on the underscore.
     *
     * A market absent from here disables the engine for that market rather than
     * searching the wrong one — `SearchengineConfiguration::applyLocale()`
     * records `INCOMPATIBLE_LOCALE`.
     */
    protected const REGIONS = [
        "ca_ES" => "ca_ES",
        "de_DE" => "de_DE",
        "de_AT" => "de_AT",
        "en_US" => "en_US",
        "en_GB" => "en_GB",
        "en_AU" => "en_AU",
        "es_ES" => "es_ES",
        "es_MX" => "es_MX",
        "da_DK" => "da_DK",
        "de_CH" => "de_CH",
        "fi_FI" => "fi_FI",
        "it_IT" => "it_IT",
        "nl_NL" => "nl_NL",
        "sv_SE" => "sv_SE",
        "fr_FR" => "fr_FR",
        "fr_CA" => "fr_CA",
        "pl_PL" => "pl_PL",
        "pt_PT" => "pt-pt_PT",
        "pt_BR" => "pt-br_BR",
    ];

    /** One index behind all three endpoints, so one description of it. */
    protected const INFOS = [
        "homepage" => "https://search.brave.com/",
        "index_name" => "Brave Search",
        "display_name" => "Brave",
        "founded" => "Juni 2021",
        "headquarter" => "San Francisco",
        "operator" => "Brave San Francisco",
        "index_size" => "einige Milliarden",
    ];

    /**
     * The part of a Brave engine's config that does not depend on the endpoint.
     *
     * Spread into each subclass's `CONFIG_OVERLOAD`, which then adds `path` and
     * its own `get-parameter` page size. `SearchengineConfiguration` reads all
     * of this — including `lang`, `infos`, `engine-boost` and `cost` — which is
     * why the parser classes no longer have constructors restating it.
     */
    protected const SHARED_CONFIG = [
        "lang" => [
            "parameter" => "country",
            "languages" => [],
            "regions" => self::REGIONS,
        ],
        "host" => "api.search.brave.com",
        "port" => 443,
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
        "cost" => 0.8,
        "infos" => self::INFOS,
    ];

    public function applySettings()
    {
        parent::applySettings();

        $this->applyInterfaceLanguage();
        $this->splitMarketIntoLanguageAndCountry();
    }

    /**
     * Tell Brave which language to answer *in*, when it knows the one we want.
     *
     * A locale Brave does not list falls back to any variant of the same
     * language — `en-CH` would take `en-AU`, the first `en` entry — and a
     * language it does not list at all leaves `ui_lang` unset, which is what
     * `ca-ES` does today. Unset is the safe outcome: Brave then picks for
     * itself, where an unrecognised value would be an error.
     */
    private function applyInterfaceLanguage(): void
    {
        $locale = LaravelLocalization::getCurrentLocale();

        if (in_array($locale, static::SUPPORTED_UI_LANGUAGES, true)) {
            $this->configuration->getParameter->ui_lang = $locale;
            return;
        }

        $language = explode("-", $locale)[0];
        foreach (static::SUPPORTED_UI_LANGUAGES as $supported) {
            if (str_starts_with($supported, $language)) {
                $this->configuration->getParameter->ui_lang = $supported;
                return;
            }
        }
    }

    /**
     * Brave keeps the search language and the search country in two parameters;
     * MetaGer configures one market, spelled `de_DE`. Split it.
     *
     * Without a configured market — the engine reached before a locale was
     * applied, or a market that is not in [REGIONS] — both halves come from the
     * request locale instead.
     */
    private function splitMarketIntoLanguageAndCountry(): void
    {
        $parameters = $this->configuration->getParameter;

        if (property_exists($parameters, "country") && preg_match("/^[^_]+_[^_]+$/", $parameters->country)) {
            [$language, $country] = explode("_", $parameters->country);
            $parameters->search_lang = $language;
            $parameters->country = $country;
            return;
        }

        $parameters->search_lang = Localization::getLanguage();
        $parameters->country = Localization::getRegion();
    }

    /**
     * Brave answers a misspelled query with what it searched for instead, and
     * MetaGer offers the user their original back — every word forced with a
     * leading `+`, except inside a quoted phrase, where the quotes already do it.
     */
    protected function captureAlteredQuery($results): void
    {
        if (empty($results->{"query"}) || empty($results->{"query"}->{"altered"}) || $results->query->altered === $results->query->original) {
            return;
        }

        $this->alteredQuery = $results->{"query"}->{"altered"};

        $override = "";
        $original = trim($results->query->original);
        $wordstart = true;
        $inphrase = false;
        for ($i = 0; $i < strlen($original); $i++) {
            $char = $original[$i];
            if ($wordstart && !$inphrase) {
                $override .= "+";
            }
            $override .= $char;
            if (strlen(trim($char)) === 0) {
                $wordstart = true;
            }
            if (strlen(trim($char)) > 0) {
                $wordstart = false;
            }
            if ($char === "\"") {
                $inphrase = !$inphrase;
            }
        }

        $this->alterationOverrideQuery = $override;
    }

    /**
     * The next page is the same engine one offset further on.
     *
     * `new static` rather than a named class: this used to be `new Brave(...)`
     * copied into each subclass, which is exactly the line an inherited
     * implementation would get wrong.
     */
    public function getNext(MetaGer $metager, $result)
    {
        try {
            $results = json_decode($result);

            if (!$this->hasMoreResults($results)) {
                return;
            }

            /** @var \App\Models\SearchengineConfiguration $newConfiguration */
            $newConfiguration = unserialize(serialize($this->configuration));
            $newConfiguration->getParameter->offset += 1;

            $this->next = new static($this->name, $newConfiguration);
        } catch (\Exception $e) {
            Log::error("A problem occurred parsing results from $this->name:");
            Log::error($e->getMessage());
            return;
        }
    }

    /** Whether Brave says there is a further page. Overridden where it does not say. */
    protected function hasMoreResults($results): bool
    {
        return !empty($results->query) && !empty($results->query->more_results_available);
    }
}
