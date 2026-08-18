<?php

namespace Tests\Feature\Search;

use App\Models\Configuration\SearchEngineRegistry;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * What decides whether a search engine can be reached at all.
 *
 * config/foki.json maps each fokus to the engines it may use, and that mapping
 * is the only route to an engine: the public engine list iterates the foki
 * (SearchEngineList), and so does a search. But the *registry* is built by
 * scanning app/Models/parserSkripte for CONFIG_OVERLOAD constants, so a parser
 * file that exists is loaded, instantiated and configured whether or not any
 * fokus names it.
 *
 * These two facts together are the premise of removing the retired parsers:
 * anything absent from foki.json is unreachable, so deleting it cannot change
 * what a user sees. The premise is worth asserting rather than assuming — it is
 * the whole argument for the removal.
 *
 * testTheRegistryContainsNothingUnreachable is the invariant the removal
 * establishes, and the reason it stays afterwards: it fails the moment a parser
 * is added without being put into a fokus, which is how the twenty-seven
 * unreachable engines accumulated in the first place.
 */
class EngineReachabilityTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        $this->forgetSearchUserClaims();
        parent::tearDown();
    }

    /**
     * @return array<int, string> every engine named by any fokus
     */
    private function enginesNamedInFoki(): array
    {
        $names = [];
        foreach ((array) app(SearchEngineRegistry::class)->foki as $fokus) {
            foreach ($fokus->sumas as $name) {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @return array<int, string> every engine the parser scan produced
     */
    private function registeredEngines(): array
    {
        return array_keys((array) app(SearchEngineRegistry::class)->sumas);
    }

    public function testTheRegistryContainsNothingUnreachable(): void
    {
        $unreachable = array_values(array_diff($this->registeredEngines(), $this->enginesNamedInFoki()));

        $this->assertSame(
            [],
            $unreachable,
            "These engines are configured and instantiated on every search but named by no fokus, so nothing can reach them: "
            . implode(", ", $unreachable)
        );
    }

    /**
     * The converse: a fokus may not name an engine that has no parser. This one
     * already held, and it is what makes foki.json trustworthy as the list.
     */
    public function testEveryEngineNamedByAFokusExists(): void
    {
        $missing = array_values(array_diff($this->enginesNamedInFoki(), $this->registeredEngines()));

        $this->assertSame([], $missing, "config/foki.json names engines with no parser: " . implode(", ", $missing));
    }

    /**
     * A search asks only engines belonging to its own fokus — the mechanism
     * that makes an unnamed engine unreachable in practice rather than only on
     * paper.
     */
    public function testASearchOnlyQueriesEnginesOfItsFokus(): void
    {
        $this->actingAsSearchUser();
        $fetcher = $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $webEngines = (array) app(SearchEngineRegistry::class)->foki->web->sumas;
        // Quicktips shares the fetch queue without being a search engine.
        $queued = array_diff($fetcher->queuedEngines(), ["Quicktips"]);

        foreach ($queued as $name) {
            $this->assertContains($name, $webEngines, "A web search queried `$name`, which is not in the web fokus.");
        }
    }

    /**
     * A web search has something to search with.
     *
     * This is the assertion that was missing when the whole search suite went
     * red at once. Every test above reaches an engine through a rendered page,
     * so when no engine is enabled they all fail together with "expected 200,
     * got 302" — eighty times, naming neither the engines nor the reason,
     * because MetaGerSearch answers "no enabled engines" with a redirect to the
     * settings page rather than an error.
     *
     * The way it happened is worth keeping in view, even though the cause has
     * since been removed: `php artisan optimize` caches routes, this app used
     * to resolve its locale while *registering* routes
     * (RouteServiceProvider::mapWebRoutes passed Localization::setLocale() as
     * the group prefix), so a warm route cache meant registration never ran and
     * `app.locale` stayed the literal 'default'. applyLocale() then disables
     * every engine whose language map has no entry for the resulting locale,
     * and the web engines declare `languages => []` with only exact regional
     * keys — so *every* engine was disabled. Nothing about that is a search
     * bug, and nothing about the eighty failures said so.
     *
     * The locale is middleware now and the route table is cacheable, so this
     * particular trap is gone. The assertion stays: "a web search has engines"
     * is worth one named failure whatever the next cause turns out to be.
     */
    public function testAWebSearchHasEnginesToQuery(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses(["brave" => $this->engineFixture("brave-web.json")]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web");

        $engines = app(\App\Models\Configuration\Searchengines::class);
        $enabled = $engines->getEnabledSearchengines() ?: [];

        $why = [];
        foreach ($engines->sumas as $name => $suma) {
            if ($suma->configuration->disabled) {
                $why[] = $name . ": " . implode("+", array_map(
                    fn($reason) => $reason->name ?? (string) $reason,
                    $suma->configuration->disabledReasons
                ));
            }
        }

        $this->assertNotEmpty(
            $enabled,
            sprintf(
                "No engine is enabled for a web search, so every test that renders a result page will "
                    . "fail with a redirect instead of a page.\n  locale: regional=%s language=%s app.locale=%s\n"
                    . "  disabled: %s",
                \LaravelLocalization::getCurrentLocaleRegional(),
                \App\Localization::getLanguage(),
                config("app.locale"),
                implode(", ", $why)
            )
        );
    }

    /**
     * No engine may be registered by a class that only *inherited* its config.
     *
     * The scan reads `$fqcn::CONFIG_OVERLOAD` off every class in
     * app/Models/parserSkripte, and a class constant is inherited when the
     * child does not redeclare it. Since parser classes started sharing a base
     * (BraveBase), a subclass that forgets its own CONFIG_OVERLOAD no longer
     * fails — it silently re-registers its parent's engine name against itself,
     * and `scanParserClasses()` walks scandir() in alphabetical order, so the
     * later file wins. `BraveImages` would take over the `brave` engine and
     * parse web results as images, on every web search, with no error anywhere.
     *
     * There is no way to notice that from the outside, which is why it is
     * asserted from the inside instead.
     */
    public function testEveryEngineIsParsedByTheClassThatDeclaresIt(): void
    {
        $inherited = [];

        foreach ((array) app(SearchEngineRegistry::class)->sumas as $engine => $config) {
            $fqcn = "App\\Models\\parserSkripte\\" . $config->{"parser-class"};
            $declaring = (new \ReflectionClassConstant($fqcn, "CONFIG_OVERLOAD"))->getDeclaringClass()->getName();

            // Namespaces are declared inconsistently cased across the parsers
            // and PHP resolves them case-insensitively, so compare that way.
            if (strcasecmp($declaring, $fqcn) !== 0) {
                $inherited[] = "$engine is registered to $fqcn, which inherits CONFIG_OVERLOAD from $declaring";
            }
        }

        $this->assertSame([], $inherited, implode("; ", $inherited));
    }

    /**
     * The public engine list is built from the foki, so a retired engine is not
     * on it. Checked against display names actually rendered on the page.
     */
    public function testThePublicEngineListShowsLiveEnginesAndNotRetiredOnes(): void
    {
        $response = $this->get("/search-engine");

        $response->assertOk();
        $response->assertSee("Brave", false);

        foreach (["Flickr", "Europeana", "Shopzilla", "Fairmondo"] as $retired) {
            $response->assertDontSee($retired, false);
        }
    }
}
