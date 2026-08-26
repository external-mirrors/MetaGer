<?php

namespace Tests\Feature\Search;

use App\Models\Configuration\SearchEngineRegistry;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * `onenewspage` and `onenewspagegermany` are both One News Page Ltd. endpoints
 * and both broke on the vendor's side, in different ways:
 *
 *   - suche.newsdeutschland.com (onenewspagegermany) accepts the TCP connect
 *     and completes the TLS handshake but never answers the HTTP request, on
 *     any path, over IPv4 or IPv6 -- the Cloudflare edge is alive, the origin
 *     behind it is not. It used to be listed under `nachrichten`'s "main" in
 *     config/foki.json, so every German news search burned the full
 *     EngineOrchestrator::WAIT_SECONDS (6s) waiting on an answer that would
 *     never arrive in time.
 *   - search.onenewspage.com/search.php (onenewspage) no longer serves the
 *     plaintext "e=1" feed the parser expects; it 301s to the ordinary HTML
 *     search page instead, dropping the query string on the second hop.
 *     Onenewspage::loadResults() then misparses lines of that page's own
 *     markup and JavaScript into fake results.
 *
 * Both are disabled via CONFIG_OVERLOAD and dropped from `main` in
 * config/foki.json (2026-08-26) until the vendor restores the feed. This
 * pins that a default nachrichten search queues neither, for both locales
 * that reach one of them.
 */
class OnenewspageOutageTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function queuedEnginesFor(string $path): array
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([]);

        $this->get("$path/meta/meta.ger3?eingabe=cafe&focus=nachrichten");

        return $fetcher->queuedEngines();
    }

    public function testAGermanNewsSearchDoesNotQueueOnenewspagegermany(): void
    {
        $engines = $this->queuedEnginesFor("/de-DE");

        $this->assertNotContains(
            "onenewspagegermany",
            $engines,
            "onenewspagegermany was queued despite being disabled -- suche.newsdeutschland.com does not answer and this stalls the whole search."
        );
    }

    public function testAnEnglishNewsSearchDoesNotQueueOnenewspage(): void
    {
        // en-US is the default locale, hidden from the URL
        // (hideDefaultLocaleInURL in config/laravellocalization.php) -- a
        // "/en-US/..." prefix here would 302-redirect to the unprefixed
        // path instead of running the search.
        $engines = $this->queuedEnginesFor("");

        $this->assertNotContains(
            "onenewspage",
            $engines,
            "onenewspage was queued despite being disabled -- its feed endpoint now serves an HTML page that gets misparsed into fake results."
        );
    }

    /**
     * Belt and suspenders on top of the CONFIG_OVERLOAD `disabled` flag:
     * `EngineOrchestrator::engines()` only ever considers enabled engines, so
     * listing a disabled engine under `main` in config/foki.json is already
     * inert today. But `main` is what a fokus is "built around" per its own
     * docblock, and leaving a dead endpoint there is a trap for the day
     * someone flips `disabled` back to false without re-checking this list --
     * the 6s stall would come straight back.
     */
    public function testNeitherOnenewspageEngineIsListedAsMainForNachrichten(): void
    {
        $main = app(SearchEngineRegistry::class)->foki->nachrichten->main;

        $this->assertNotContains("onenewspage", $main);
        $this->assertNotContains("onenewspagegermany", $main);
    }
}
