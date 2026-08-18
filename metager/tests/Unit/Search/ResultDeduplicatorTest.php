<?php

namespace Tests\Unit\Search;

use App\Models\DeepResults\Button;
use App\Models\Result;
use App\Search\ResultDeduplicator;
use Tests\TestCase;

/**
 * ResultDeduplicator in isolation: two results in, one result out, and what
 * survives the merge.
 *
 * The behaviour is also covered end-to-end in
 * tests/Feature/Search/ResultMergingTest, which is what proves it is still
 * wired into a search at all. These tests exist for the cases a fixture cannot
 * reach cleanly — an image search comparing thumbnails, deep-result buttons
 * arriving from two engines, the `new`/`changed` bookkeeping — and because
 * having them means a change to the merge rules fails in one line rather than
 * as a mismatched result count on a rendered page.
 *
 * Extends Tests\TestCase rather than PHPUnit's: Result's constructor signs a
 * proxy link with a config value.
 */
class ResultDeduplicatorTest extends TestCase
{
    private function hit(string $link, string $engine = "brave", array $additional = []): Result
    {
        return new Result(
            1,
            "Kaffee",
            $link,
            $link,
            "Eine Beschreibung, die lang genug ist um nicht gekürzt zu werden.",
            $engine,
            "https://" . $engine . ".example/",
            1,
            $additional
        );
    }

    /**
     * @param Result[] $results
     * @return Result[]
     */
    private function deduplicate(array $results, string $fokus = "web"): array
    {
        return (new ResultDeduplicator())->deduplicate($results, $fokus);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function sameUrlSpelledDifferently(): array
    {
        return [
            "http instead of https" => ["http://example.org/kaffee"],
            "with www." => ["https://www.example.org/kaffee"],
            "trailing slash" => ["https://example.org/kaffee/"],
            "all three at once" => ["http://www.example.org/kaffee/"],
            "percent-encoded path" => ["https://example.org/%6Baffee"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider("sameUrlSpelledDifferently")]
    public function testTheSamePageIsRecognisedThroughItsSpelling(string $otherSpelling): void
    {
        $results = $this->deduplicate([
            $this->hit("https://example.org/kaffee", "brave"),
            $this->hit($otherSpelling, "serper"),
        ]);

        $this->assertCount(1, $results, $otherSpelling . " was treated as a different page from https://example.org/kaffee.");
    }

    /**
     * Characterizing, not endorsing. Normalisation covers scheme, `www.` and a
     * trailing slash and stops there, so tracking parameters split one page
     * into two results. Whether to strip known tracking parameters before
     * comparing is a product decision; this records that today it does not.
     */
    public function testAQueryStringMakesItADifferentPage(): void
    {
        $results = $this->deduplicate([
            $this->hit("https://example.org/kaffee"),
            $this->hit("https://example.org/kaffee?utm_source=newsletter"),
        ]);

        $this->assertCount(2, $results, "Deduplication learned to ignore query parameters. That is an improvement — update this test to describe it.");
    }

    /**
     * The first result keeps its place; it does not move to where the duplicate
     * was, and the results after it do not shuffle.
     */
    public function testTheFirstOccurrenceIsTheOneThatSurvivesAndKeepsItsPosition(): void
    {
        $results = $this->deduplicate([
            $this->hit("https://example.org/kaffee", "brave"),
            $this->hit("https://example.org/tee", "brave"),
            $this->hit("https://www.example.org/kaffee/", "serper"),
            $this->hit("https://example.org/kakao", "serper"),
        ]);

        $this->assertSame(
            ["https://example.org/kaffee", "https://example.org/tee", "https://example.org/kakao"],
            array_map(fn(Result $r) => $r->link, $results)
        );
    }

    public function testAMergedResultNamesEveryEngineThatFoundIt(): void
    {
        $results = $this->deduplicate([
            $this->hit("https://example.org/kaffee", "brave"),
            $this->hit("https://www.example.org/kaffee", "serper"),
            $this->hit("http://example.org/kaffee/", "mojeek"),
        ]);

        $this->assertSame(["brave", "serper", "mojeek"], $results[0]->gefVon);
        $this->assertSame(
            ["https://brave.example/", "https://serper.example/", "https://mojeek.example/"],
            $results[0]->gefVonLink,
            "gefVon and gefVonLink are parallel arrays the view walks by index; they came apart."
        );
    }

    /**
     * Two engines can each know a different sub-page of the same site, so the
     * buttons are appended rather than replaced.
     */
    public function testDeepResultButtonsFromBothEnginesAreKept(): void
    {
        $first = $this->hit("https://example.org/kaffee", "brave");
        $first->deepResults["buttons"][] = new Button("Impressum", "https://example.org/impressum");

        $second = $this->hit("https://example.org/kaffee/", "serper");
        $second->deepResults["buttons"][] = new Button("Kontakt", "https://example.org/kontakt");

        $results = $this->deduplicate([$first, $second]);

        $this->assertCount(2, $results[0]->deepResults["buttons"], "A duplicate's deep-result buttons replaced the kept result's instead of adding to them.");
    }

    /**
     * A result found without a preview picture and again with one keeps the
     * picture.
     */
    public function testAnImageOnTheDuplicateIsAdoptedByTheKeptResult(): void
    {
        $results = $this->deduplicate([
            $this->hit("https://example.org/kaffee", "brave"),
            $this->hit("https://example.org/kaffee/", "serper", ["image" => "https://example.org/bohne.jpg"]),
        ]);

        $this->assertSame("https://example.org/bohne.jpg", $results[0]->image);
    }

    /**
     * On an image search two results are the same when they show the same
     * picture, which is not the same question as whether they link to the same
     * page — two pages can embed one image, and one page can carry many.
     */
    public function testAnImageSearchComparesThumbnailsRatherThanLinks(): void
    {
        $thumbnail = (object) ["thumbnail" => "https://cdn.example.org/thumb/bohne.jpg"];

        $results = $this->deduplicate([
            $this->hit("https://example.org/eine-seite", "brave", ["image" => $thumbnail]),
            $this->hit("https://example.org/eine-voellig-andere-seite", "serper", ["image" => $thumbnail]),
        ], "bilder");

        $this->assertCount(1, $results, "Two results showing the same picture stayed two results on an image search.");
    }

    /**
     * Characterization, and the reason ResultDeduplicator carries a comment
     * about it: `changed` is set from the *previously kept* result rather than
     * from the duplicate being merged.
     *
     * Here the duplicate is new and the result before it is not — so a rule that
     * read the duplicate would set `changed`, and the rule that is actually
     * implemented does not. Nothing reads the flag (no blade, no stylesheet, no
     * line of resources/js), it only travels as a field of the load-more JSON
     * payload, so it is preserved rather than quietly corrected. If this test
     * fails because someone fixed it on purpose, that is the right outcome —
     * delete the test with the field.
     */
    public function testChangedIsDecidedByThePrecedingResultNotTheDuplicate(): void
    {
        $preceding = $this->hit("https://example.org/tee", "brave");
        $preceding->new = false;

        $kept = $this->hit("https://example.org/kaffee", "brave");
        $kept->new = false;

        $duplicate = $this->hit("https://example.org/kaffee/", "serper");
        $duplicate->new = true;

        $results = $this->deduplicate([$preceding, $kept, $duplicate]);

        $this->assertFalse(
            $results[1]->changed,
            "`changed` now follows the duplicate, which is what the name suggests it should have done all along."
        );
    }

    public function testAnEmptyListIsNotAnError(): void
    {
        $this->assertSame([], $this->deduplicate([]));
    }
}
