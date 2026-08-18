<?php

namespace Tests\Unit\Search;

use App\Models\Configuration\Searchengines;
use App\Models\Result;
use App\Search\ResultRanker;
use Tests\TestCase;

/**
 * ResultRanker in isolation.
 *
 * Deliberately not a test of the ranking arithmetic. That lives on Result::rank
 * — source rank, a URL match boost, a search-word boost, times the engine boost
 * — and it is a tuning surface someone is entitled to change without breaking
 * anything. What is asserted here is what the rest of the search relies on and
 * would break silently: that the list comes back highest-first, that it is
 * indexed from zero afterwards, and that every engine is asked to score its own
 * results.
 *
 * The order guarantee matters more than it looks. Deduplication keeps the first
 * copy of a page it meets, so if the list were not ordered first, "the result
 * that survives" would mean "whichever engine happened to answer first".
 */
class ResultRankerTest extends TestCase
{
    private function ranked(float $rank, string $link = "https://example.org/"): Result
    {
        $result = new Result(1, "Kaffee", $link, $link, "Eine Beschreibung.", "brave", "https://brave.example/", 1);
        $result->rank = $rank;

        return $result;
    }

    public function testResultsComeBackHighestRankFirst(): void
    {
        $ordered = (new ResultRanker())->order([
            $this->ranked(0.2, "https://example.org/mittel"),
            $this->ranked(0.9, "https://example.org/hoch"),
            $this->ranked(0.05, "https://example.org/niedrig"),
        ]);

        $this->assertSame(
            ["https://example.org/hoch", "https://example.org/mittel", "https://example.org/niedrig"],
            array_map(fn(Result $r) => $r->link, $ordered)
        );
    }

    /**
     * Everything downstream indexes this list from zero — deduplication reads
     * the last kept entry by position, the view walks it, the JSON output is
     * json_encode'd as an array. A sort that preserved the original keys would
     * turn that last one into an object.
     */
    public function testTheOrderedListIsIndexedFromZero(): void
    {
        $ordered = (new ResultRanker())->order([
            7 => $this->ranked(0.1),
            3 => $this->ranked(0.9),
        ]);

        $this->assertSame([0, 1], array_keys($ordered));
    }

    /**
     * Equal ranks keep the order they came in. PHP's sort has been stable since
     * 8.0, so this is a property of the language rather than of the comparator —
     * but it is the property that makes an ordering of a mostly-flat rank
     * distribution reproducible, which is what makes the result page the same
     * page twice in a row.
     */
    public function testEqualRanksKeepTheirRelativeOrder(): void
    {
        $ordered = (new ResultRanker())->order([
            $this->ranked(0.5, "https://example.org/erst"),
            $this->ranked(0.5, "https://example.org/dann"),
            $this->ranked(0.5, "https://example.org/zuletzt"),
        ]);

        $this->assertSame(
            ["https://example.org/erst", "https://example.org/dann", "https://example.org/zuletzt"],
            array_map(fn(Result $r) => $r->link, $ordered)
        );
    }

    public function testAnEmptyListIsNotAnError(): void
    {
        $this->assertSame([], (new ResultRanker())->order([]));
    }

    /**
     * Every enabled engine is asked to score its results — and only the enabled
     * ones, because a disabled engine's results never reach the page and ranking
     * them is work nobody sees.
     */
    public function testEveryEnabledEngineIsAskedToRankItsOwnResults(): void
    {
        $asked = [];
        $engine = new class($asked) {
            public function __construct(private array &$asked) {}
            public function rank(): void
            {
                $this->asked[] = "rank";
            }
        };

        $engines = new class extends Searchengines {
            public array $enabled = [];
            public function __construct() {}
            public function getEnabledSearchengines()
            {
                return $this->enabled;
            }
        };
        $engines->enabled = ["brave" => $engine, "serper_web" => $engine];
        $this->app->instance(Searchengines::class, $engines);

        (new ResultRanker())->rankEngineResults();

        $this->assertSame(["rank", "rank"], $asked, "Not every enabled engine was asked to rank its results.");
    }
}
