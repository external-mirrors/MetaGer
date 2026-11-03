<?php

namespace Tests\Feature\Search;

use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * `tootnews` is one engine name serving two hosts: `toot.suma-lab.de` for
 * English, `troet.suma-lab.de` for German, same feed/API on both. Nothing
 * in `SearchengineConfiguration` picks a host per language, so
 * `Tootnews::__construct()` swaps `configuration->host` in for a German
 * locale after the base config (which points at the English host) is
 * applied. This pins that the request actually goes to the right host for
 * each language, since a mistake here degrades silently: both hosts answer
 * with a 200, just the wrong index.
 *
 * The hosts are compared exactly, not by substring: the German host once
 * shipped as `troetnews.suma-ev.de`, a name that never existed in DNS, so
 * every German news search lost the engine without an error anyone saw.
 */
class TootNewsLocaleTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    private function requestUrlFor(string $path): ?string
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([]);

        $this->get("$path/meta/meta.ger3?eingabe=cafe&focus=nachrichten");

        foreach ($fetcher->missions() as $mission) {
            if ($mission["name"] === "tootnews") {
                return $mission["url"];
            }
        }

        return null;
    }

    public function testAGermanNewsSearchQueriesTheGermanHost(): void
    {
        $url = $this->requestUrlFor("/de-DE");

        $this->assertNotNull($url, "tootnews queued no request for a German search");
        $this->assertSame("troet.suma-lab.de", parse_url($url, PHP_URL_HOST));
    }

    public function testAnEnglishNewsSearchQueriesTheEnglishHost(): void
    {
        // en-US is the default locale, hidden from the URL
        // (hideDefaultLocaleInURL in config/laravellocalization.php) -- a
        // "/en-US/..." prefix here would 302-redirect to the unprefixed
        // path instead of running the search.
        $url = $this->requestUrlFor("");

        $this->assertNotNull($url, "tootnews queued no request for an English search");
        $this->assertSame("toot.suma-lab.de", parse_url($url, PHP_URL_HOST));
    }
}
