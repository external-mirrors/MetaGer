<?php

namespace App\Models\parserSkripte\Base;

use App\Localization;
use App\MetaGer;
use App\Models\Searchengine;
use App\Search\GoogleRedirectResolver;
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
        "en_IE" => "ie",
        "en_MY" => "my",
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
     * How long after its missions are queued a goto link is still worth
     * waiting for. Serper's `/resolve` answers a single link in well under a
     * second for the first few links of a search, then makes one of them wait
     * about 25 seconds, after which the rest are instant; measured against the
     * live API, the last link of a search came back 39 s after the search.
     * Also the missions' curl timeout, so nothing outlives the window.
     */
    public const RESOLVE_WINDOW_SECONDS = 45;

    /** The engine name resolve missions carry, which is what the fetcher's metrics are labelled by. */
    public const RESOLVE_MISSION_NAME = "serper_resolve";

    /**
     * Everything late resolution needs, and null for an answer that has no
     * goto link to resolve — which keeps it out of the loader cache entry for
     * every other search (see LoaderCacheTest's size budget).
     *
     *   - `answer`: the body as Serper sent it, goto links and all, so it can be
     *     parsed again once some of them are resolved
     *   - `resolved`: goto link => the page it leads to, as /resolve reported it
     *   - `unresolvable`: goto link => true, for answers without a usable target
     *   - `since`: when the resolve missions were queued; null until they are
     *
     * @var array{answer: string, resolved: array<string, string>, unresolvable: array<string, true>, since: ?int}|null
     */
    private ?array $late = null;

    /**
     * Parse the answer once Google's click-tracking redirects are out of it.
     *
     * Done on the raw body rather than on the results, because a Result works
     * out its host, its deduplication key and its proxy link from the URL it is
     * constructed with — every field would have to be recomputed afterwards.
     * Rewriting the body first means every link field the subclasses read
     * (organic results, their sitelinks, top stories) is covered without any of
     * them having to know. See [GoogleRedirectResolver] for why an unresolvable
     * redirect drops its entry instead of being shown.
     *
     * The original answer is kept when it carries goto links, so that
     * [applyResolvedAnswers] can bring the dropped entries back.
     */
    public function loadResponse(MetaGer $metager, ?string $body): bool
    {
        if ($body !== null && !$this->loaded) {
            $this->late = ["answer" => $body, "resolved" => [], "unresolvable" => [], "since" => null];
            if (empty($this->lateLinks())) {
                $this->late = null;
            }
            $body = self::withoutGoogleRedirects($body);
        }

        return parent::loadResponse($metager, $body);
    }

    /**
     * The goto links in this engine's answer that only Serper's `/resolve`
     * can still turn into a result: neither resolved nor given up on yet.
     *
     * In the order they appear in the answer, which for organic results is
     * rank order — the order Google lets them be opened in.
     *
     * @return string[]
     */
    public function lateLinks(): array
    {
        if ($this->late === null) {
            return [];
        }

        $decoded = json_decode($this->late["answer"]);
        if (!is_object($decoded) && !is_array($decoded)) {
            return [];
        }

        $redirects = array_values(array_unique(self::collectRedirects($decoded)));
        $local = (new GoogleRedirectResolver())->resolve($redirects);

        return array_values(array_filter(
            $redirects,
            fn(string $link) => $local[$link] === null
                && !isset($this->late["resolved"][$link])
                && !isset($this->late["unresolvable"][$link])
        ));
    }

    /**
     * Whether a later load-more may still bring results back: there are late
     * links, and either their missions are not queued yet or the window for
     * them has not closed.
     */
    public function isResolving(): bool
    {
        if (empty($this->lateLinks())) {
            return false;
        }

        return $this->late["since"] === null
            || now()->getTimestamp() - $this->late["since"] < self::RESOLVE_WINDOW_SECONDS;
    }

    /**
     * One `/resolve` mission per late link, the first time this is asked and
     * never again.
     *
     * One link per mission because that is what `/resolve` answers quickly:
     * a request carrying several links waits the full ~25 s for all of them,
     * where single links come back one by one. Queued all at once rather than
     * chained, since Serper already serialises them — three sent in parallel
     * came back after 0.6, 4.9 and 6.5 seconds.
     *
     * @return list<array<string, mixed>>
     */
    public function resolveMissions(): array
    {
        $links = $this->lateLinks();
        if (empty($links) || $this->late["since"] !== null) {
            return [];
        }
        $this->late["since"] = now()->getTimestamp();

        $headers = (array) $this->configuration->requestHeader;
        $headers["Content-Type"] = "application/json";

        return array_map(fn(string $link) => [
            "resulthash" => $this->resolveHash($link),
            "url" => "https://" . $this->configuration->host . "/resolve",
            "useragent" => $this->useragent,
            "username" => null,
            "password" => null,
            "headers" => $headers,
            "cacheDuration" => 0,
            "name" => self::RESOLVE_MISSION_NAME,
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(["organic" => [["link" => $link]]], JSON_UNESCAPED_SLASHES),
                // The fetcher's defaults give up after ten seconds, and on a
                // transfer slower than 50 kB/s; this one is a tiny answer that
                // can legitimately take 25 seconds to start.
                CURLOPT_TIMEOUT => self::RESOLVE_WINDOW_SECONDS,
                CURLOPT_LOW_SPEED_LIMIT => 0,
            ],
        ], $links);
    }

    /** Where the fetcher puts the answer for $link's resolve mission. */
    public function resolveHash(string $link): string
    {
        return md5("serper-resolve:" . $link);
    }

    /**
     * Take in what `/resolve` answered and, if that brought any result back,
     * parse the original answer again.
     *
     * A link whose answer arrived but names no usable page is given up on, so
     * it stops counting as late; one whose answer has not arrived is left
     * alone.
     *
     * @param array<string, ?string> $answers late link => the response body, or null if none arrived yet
     * @return bool whether this engine's results changed
     */
    public function applyResolvedAnswers(array $answers): bool
    {
        if ($this->late === null) {
            return false;
        }

        $resolvedAny = false;
        foreach ($answers as $link => $body) {
            if ($body === null) {
                continue;
            }

            $decoded = json_decode($body);
            $target = GoogleRedirectResolver::acceptableTarget($decoded->organic[0]->link ?? null);
            if ($target === null) {
                $this->late["unresolvable"][$link] = true;
                continue;
            }

            $this->late["resolved"][$link] = $target;
            $resolvedAny = true;
        }

        if (!$resolvedAny) {
            return false;
        }

        $this->results = [];
        $this->news = [];
        $this->videos = [];
        $this->products = [];
        $this->counter = 0;
        $this->loadResults(self::withoutGoogleRedirects($this->late["answer"], $this->late["resolved"]));
        $this->markNew();

        return true;
    }

    /**
     * $body with every Google redirect replaced by its target, and every list
     * entry whose redirect could not be unwrapped removed from its list.
     *
     * $resolved supplies targets learned elsewhere — from Serper's `/resolve`
     * — for redirects that cannot be unwrapped locally.
     *
     * Untouched — byte for byte — when it contains no redirect at all, which
     * is the common case, so most answers pay one substring check.
     *
     * @param array<string, string> $resolved
     */
    public static function withoutGoogleRedirects(string $body, array $resolved = []): string
    {
        if (!str_contains($body, "google.")) {
            return $body;
        }

        $decoded = json_decode($body);
        if (!is_object($decoded) && !is_array($decoded)) {
            return $body;
        }

        $redirects = self::collectRedirects($decoded);
        if (empty($redirects)) {
            return $body;
        }

        $targets = (new GoogleRedirectResolver())->resolve($redirects);
        foreach ($targets as $link => $target) {
            $targets[$link] = $target ?? $resolved[$link] ?? null;
        }

        return json_encode(
            self::replaceRedirects($decoded, $targets),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /** @return string[] every string anywhere in $node that is a Google redirect */
    private static function collectRedirects(mixed $node): array
    {
        if (is_string($node)) {
            return GoogleRedirectResolver::isRedirect($node) ? [$node] : [];
        }
        if (is_object($node)) {
            $node = get_object_vars($node);
        }
        if (!is_array($node)) {
            return [];
        }

        return array_merge([], ...array_map(self::collectRedirects(...), array_values($node)));
    }

    /**
     * @param array<string, ?string> $resolved
     * @return mixed $node rewritten, or [DROP] if it is an object holding a
     *               redirect that could not be resolved
     */
    private static function replaceRedirects(mixed $node, array $resolved): mixed
    {
        if (is_string($node)) {
            return array_key_exists($node, $resolved) ? ($resolved[$node] ?? self::DROP) : $node;
        }

        if (is_array($node)) {
            $kept = [];
            foreach ($node as $key => $child) {
                $child = self::replaceRedirects($child, $resolved);
                if ($child !== self::DROP) {
                    $kept[$key] = $child;
                }
            }
            return array_is_list($node) ? array_values($kept) : $kept;
        }

        if (is_object($node)) {
            foreach (get_object_vars($node) as $property => $child) {
                $child = self::replaceRedirects($child, $resolved);
                if ($child === self::DROP) {
                    // A link that may not be shown makes the whole entry
                    // unshowable; the list holding it drops it.
                    if (is_string($node->$property)) {
                        return self::DROP;
                    }
                    unset($node->$property);
                    continue;
                }
                $node->$property = $child;
            }
        }

        return $node;
    }

    /** Sentinel for "remove this entry"; no Serper answer contains it. */
    private const DROP = "\0metager:drop-unresolved-google-redirect\0";

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
