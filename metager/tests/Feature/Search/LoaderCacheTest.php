<?php

namespace Tests\Feature\Search;

use App\Models\Configuration\Searchengines;
use App\SearchSettings;
use Illuminate\Support\Facades\Cache;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What the loader cache entry has to carry, and what it costs to carry it.
 *
 * A search answers before every engine has replied, so the server stores the
 * whole search state under "loader_<uid>" and rebuilds from it when the browser
 * polls for more. LoadMoreTest pins the *response* that comes back out. These
 * pin the state that goes in — which is the part a change to any of these
 * classes can silently break, because the first page still renders perfectly
 * when the cached graph is wrong.
 *
 * The entry is four object graphs serialized wholesale: the Authorization, the
 * Searchengines, the SearchSettings and the Quicktips. Two things in there are
 * load-bearing and easy to lose:
 *
 *   - the query, which lives inside each engine's configuration->getParameter
 *     after applyQuery() wrote it there. Lose it and load-more fetches page two
 *     of nothing.
 *   - the engine registry (settings->sumasJson), which Searchengine::getNext
 *     reads filters out of, and which Searchengines uses to resolve the fokus.
 *
 * There is also a size assertion here, which is not incidental. The entry lives
 * an hour in a 5 GB allkeys-lru cache, so its size sets how many concurrent
 * searches fit before the cache starts evicting — and when it evicts a loader
 * entry, load-more stops working for that search with no error anywhere.
 *
 * Note on measurement: the suite runs CACHE_STORE=array (phpunit.xml), so the
 * cache holds live objects rather than bytes. Serializing what we read back is
 * what a Redis store would have written, and is the honest number.
 */
class LoaderCacheTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * Run a search and hand back the loader entry it cached.
     */
    private function searchAndReadLoaderEntry(string $query = "eingabe=kaffee&focus=web"): array
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $page = $this->get("/meta/meta.ger3?" . $query);
        $page->assertOk();

        preg_match('/name="searchkey" content="([^"]+)"/', $page->getContent(), $match);
        $this->assertNotEmpty($match, "The result page carries no search key, so nothing was cached to load more from.");

        $entry = Cache::get("loader_" . $match[1]);
        $this->assertIsArray($entry, "The search did not cache a loader entry, so load-more cannot work at all.");

        return $entry;
    }

    /**
     * The query has to survive, or load-more asks the engines for more of
     * nothing. It is not stored as a field — it sits in the GET parameters that
     * applyQuery() wrote into each engine's configuration.
     */
    public function testTheAppliedQuerySurvivesTheRoundTrip(): void
    {
        $entry = $this->searchAndReadLoaderEntry();

        /** @var Searchengines $engines */
        $engines = unserialize(serialize($entry["metager"]["searchengines"]));

        $found = [];
        foreach ($engines->getEnabledSearchengines() as $name => $engine) {
            $parameters = (array) $engine->configuration->getParameter;
            $found[$name] = in_array("kaffee", array_map(strval(...), $parameters), true);
        }

        $this->assertNotEmpty($found, "No engine was enabled, so this asserts nothing.");
        foreach ($found as $name => $carriesQuery) {
            $this->assertTrue($carriesQuery, "Engine [$name] lost the query across the loader cache round trip.");
        }
    }

    /**
     * The registry has to come back, whether it travelled in the entry or was
     * rebuilt on the way out. Searchengine::getNext reads its filters and
     * Searchengines resolves the fokus through it, so a null here is a fatal on
     * the load-more request rather than a missing feature.
     */
    public function testTheEngineRegistrySurvivesTheRoundTrip(): void
    {
        $entry = $this->searchAndReadLoaderEntry();

        /** @var SearchSettings $original */
        $original = $entry["metager"]["settings"];
        /** @var SearchSettings $restored */
        $restored = unserialize(serialize($original));

        $this->assertNotNull($restored->sumasJson, "The engine registry did not come back; getNext() reads filters off it.");
        $this->assertEquals(
            array_keys((array) $original->sumasJson->sumas),
            array_keys((array) $restored->sumasJson->sumas),
            "The set of known engines changed across the round trip."
        );
        $this->assertEquals(
            $original->sumasJson->foki->{$original->fokus}->sumas,
            $restored->sumasJson->foki->{$restored->fokus}->sumas,
            "The engines belonging to this fokus changed across the round trip."
        );
        $this->assertObjectHasProperty("query-filter", $restored->sumasJson->filter);
        $this->assertObjectHasProperty("parameter-filter", $restored->sumasJson->filter);
    }

    /**
     * The user's own settings are what makes this entry per-search rather than
     * shared, so they are the part that genuinely has to travel in it.
     */
    public function testTheUsersSettingsSurviveTheRoundTrip(): void
    {
        $entry = $this->searchAndReadLoaderEntry("eingabe=kaffee&focus=web&page=2");

        /** @var SearchSettings $original */
        $original = $entry["metager"]["settings"];
        /** @var SearchSettings $restored */
        $restored = unserialize(serialize($original));

        foreach (["q", "fokus", "page", "quicktips", "theme", "available_foki", "blacklist", "blacklist_tld"] as $field) {
            $this->assertEquals(
                $original->{$field},
                $restored->{$field},
                "SearchSettings::\$$field did not survive the loader cache round trip."
            );
        }
    }

    /**
     * Which engines were switched off, and why, decides what load-more asks
     * for. Losing it would let a disabled engine be queried on page two.
     */
    public function testTheDisabledEnginesSurviveTheRoundTrip(): void
    {
        $entry = $this->searchAndReadLoaderEntry();

        /** @var Searchengines $original */
        $original = $entry["metager"]["searchengines"];
        /** @var Searchengines $restored */
        $restored = unserialize(serialize($original));

        $this->assertSame(
            array_keys($original->sumas),
            array_keys($restored->sumas),
            "The set of engines changed across the round trip."
        );

        foreach ($original->sumas as $name => $engine) {
            $this->assertSame(
                $engine->configuration->disabled,
                $restored->sumas[$name]->configuration->disabled,
                "Engine [$name] changed its disabled state across the loader cache round trip."
            );
        }
    }

    /**
     * How many bytes one in-flight search occupies.
     *
     * The number is asserted, not merely reported, because it is a capacity
     * limit in disguise: at 60 minutes TTL in a 5 GB allkeys-lru cache, this
     * size divides into how many searches can be in flight before the cache
     * evicts loader entries — and an evicted entry means load-more silently
     * stops working for a search that is still on screen.
     *
     * The budget is deliberately a ceiling rather than an exact figure, so
     * ordinary changes to a fixture do not fail it. If a change pushes past it,
     * the question to answer is what got added to the cached graph.
     */
    public function testTheLoaderEntryStaysWithinItsSizeBudget(): void
    {
        $entry = $this->searchAndReadLoaderEntry();

        $bytes = strlen(serialize($entry));

        $this->assertLessThan(
            95_000,
            $bytes,
            sprintf(
                "One in-flight search now costs %d bytes of cache. Breakdown: settings %d, searchengines %d, "
                    . "authorization %d, engines %d.",
                $bytes,
                strlen(serialize($entry["metager"]["settings"])),
                strlen(serialize($entry["metager"]["searchengines"])),
                strlen(serialize($entry["metager"]["authorization"])),
                strlen(serialize($entry["engines"]))
            )
        );
    }
}
