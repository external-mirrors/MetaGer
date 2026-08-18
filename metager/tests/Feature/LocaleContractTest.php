<?php

namespace Tests\Feature;

use App\Localization\LocaleContext;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The shared locale contract, executed.
 *
 * `tests/Fixtures/locale-cases.json` is copied verbatim into app-en,
 * metager-keymanager and SafeBrowse, and each of the four runs it against its
 * own resolver. That is the whole conformance mechanism: four languages, one
 * list of decisions, and a diff whenever a copy drifts.
 *
 * This file therefore asserts nothing of its own. Everything it checks is in
 * the fixture, and `docs/locale-contract.md` is the prose form of the same
 * thing. A case that needs changing is changed there first.
 *
 * `LocaleResolutionTest` remains the place for rules only MetaGer has — legacy
 * two-letter segments, which paths skip locale work, what is left of the URL
 * afterwards. The fixture is deliberately narrower than that: it holds only
 * what all four projects can be expected to agree on.
 */
class LocaleContractTest extends TestCase
{
    /** @return array<string, mixed> */
    private static function fixture(): array
    {
        $raw = file_get_contents(__DIR__ . "/../Fixtures/locale-cases.json");

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function resolutionCases(): array
    {
        $cases = [];
        foreach (self::fixture()["resolution"] as $case) {
            $cases[$case["name"]] = [$case];
        }

        return $cases;
    }

    /** @param array<string, mixed> $case */
    #[DataProvider("resolutionCases")]
    public function testTheContractHolds(array $case): void
    {
        $url = "http://" . $case["host"] . $case["path"];
        if (!empty($case["query"])) {
            $url .= "?" . http_build_query($case["query"]);
        }

        $request = Request::create($url);

        /**
         * `Request::create()` invents `Accept-Language: en-us,en;q=0.5` when
         * the caller supplies none, so a fixture case that deliberately sends
         * no header cannot be built without removing it again. Left in, the
         * host-fallback cases would silently be testing an English browser.
         */
        $request->headers->remove("Accept-Language");

        foreach ($case["headers"] as $name => $value) {
            $request->headers->set($name, $value);
        }
        foreach ($case["cookies"] as $name => $value) {
            $request->cookies->set($name, $value);
        }

        $this->assertSame(
            $case["expect"],
            LocaleContext::resolve($request)->locale,
            $case["name"] . " — " . $case["why"],
        );
    }

    public function testTheHomeRegionsAreTheOnesInTheFixture(): void
    {
        $expected = self::fixture()["home_regions"];
        unset($expected['$comment']);

        $this->assertSame(
            $expected,
            LocaleContext::HOME_REGION,
            "The region a bare language stands for is shared with three other codebases. "
                . "Change tests/Fixtures/locale-cases.json and copy it, rather than only this table.",
        );
    }

    /**
     * The app is the only client that has to name its market outright, and the
     * fixture is where it says which one. MetaGer's side of that agreement is
     * narrow but load-bearing: every market the app might send has to be a
     * market this install actually offers, or the filter is silently dropped
     * and the engines answer from wherever they default to.
     */
    public function testEveryMarketTheFixturePromisesIsOffered(): void
    {
        $filters = json_decode(
            file_get_contents(base_path("config/filters.json")),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $offered = array_keys($filters["parameter-filter"]["language"]["values"]);

        foreach (self::fixture()["device_markets"] as $device => $market) {
            if ($device === '$comment' || $market === null) {
                continue;
            }

            $this->assertContains(
                $market,
                $offered,
                "The fixture promises a $device device the $market market, which config/filters.json "
                    . "does not offer. An unoffered market is not an error anywhere — it is dropped, "
                    . "and the search runs with no market filter at all.",
            );
        }
    }
}
