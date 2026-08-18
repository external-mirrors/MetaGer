<?php

namespace Tests\Unit\Search;

use App\Models\Configuration\SearchEngineRegistry;
use App\Models\parserSkripte\Onenewspage;
use App\Models\parserSkripte\Onenewspagegermany;
use App\Models\Result;
use App\Models\SearchengineConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Die beiden OneNewsPage-Parser sortieren ihre Treffer nach Datum.
 *
 * Der Feed liefert pro Zeile `Titel|Beschreibung|Link|Zeitstempel`, das vierte
 * Feld aber nicht immer. `Result::getDate()` ist deshalb `Carbon|null`, und die
 * Sortierung hat auf diesem null aufgerufen: eine einzige undatierte Zeile hat
 * den kompletten Nachrichten-Fokus mit HTTP 500 beantwortet — für jede
 * englische Nachrichtensuche, im Web genauso wie in der App, weil `onenewspage`
 * als einzige Engine `en` bedient und dort nicht wegkonfiguriert wird.
 *
 * Getestet wird deshalb nicht die Sortierreihenfolge als Feature, sondern
 * genau das, was still kaputtgeht: dass eine datenlose Zeile den Parser nicht
 * umbringt und die datierten Treffer trotzdem korrekt sortiert bleiben.
 */
class NewsFeedSortingTest extends TestCase
{
    private function engine(string $name): Onenewspage|Onenewspagegermany
    {
        $registry = app(SearchEngineRegistry::class);
        $configuration = new SearchengineConfiguration($registry->sumas->{$name});

        return $name === "onenewspage"
            ? new Onenewspage($name, $configuration)
            : new Onenewspagegermany($name, $configuration);
    }

    /** @return string[] die Titel in der Reihenfolge, in der der Parser sie abgelegt hat */
    private function titles(Onenewspage|Onenewspagegermany $engine): array
    {
        return array_values(array_map(fn(Result $result) => $result->titel, $engine->results));
    }

    public static function parserProvider(): array
    {
        return [
            "onenewspage" => ["onenewspage"],
            "onenewspagegermany" => ["onenewspagegermany"],
        ];
    }

    #[DataProvider("parserProvider")]
    public function testAFeedLineWithoutATimestampDoesNotKillTheSearch(string $name): void
    {
        $engine = $this->engine($name);

        $engine->loadResults(implode("\n", [
            "Mit Datum|Eine Beschreibung.|https://example.org/mit-datum|1700000000",
            "Ohne Datum|Eine Beschreibung.|https://example.org/ohne-datum",
        ]));

        $this->assertSame(
            ["Mit Datum", "Ohne Datum"],
            $this->titles($engine),
            "die undatierte Zeile gehört hinter jede datierte",
        );
    }

    #[DataProvider("parserProvider")]
    public function testDatedResultsStillSortNewestFirstAroundAnUndatedOne(string $name): void
    {
        $engine = $this->engine($name);

        $engine->loadResults(implode("\n", [
            "Alt|Eine Beschreibung.|https://example.org/alt|1600000000",
            "Ohne Datum|Eine Beschreibung.|https://example.org/ohne-datum",
            "Neu|Eine Beschreibung.|https://example.org/neu|1700000000",
        ]));

        $this->assertSame(["Neu", "Alt", "Ohne Datum"], $this->titles($engine));
    }

    #[DataProvider("parserProvider")]
    public function testAFeedWithNoTimestampsAtAllKeepsItsOrder(string $name): void
    {
        $engine = $this->engine($name);

        $engine->loadResults(implode("\n", [
            "Erste|Eine Beschreibung.|https://example.org/erste",
            "Zweite|Eine Beschreibung.|https://example.org/zweite",
        ]));

        $this->assertSame(["Erste", "Zweite"], $this->titles($engine));
    }
}
