<?php

namespace Tests\Unit\Search;

use App\Models\Result;
use App\Search\DisplayUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The address drawn under a result's title, built by MetaGer from the
 * result's link rather than taken from the engine. Engines disagreed: Brave
 * sent "host › path", Staan "host > path", everyone else the bare URL, so one
 * result page mixed three styles.
 */
class DisplayUrlTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function links(): array
    {
        return [
            "host only" => ["https://example.org", "example.org"],
            "host with a trailing slash" => ["https://example.org/", "example.org"],
            "www is dropped" => ["https://www.example.org/kaffee", "example.org › kaffee"],
            "only a leading www" => ["https://www2.example.org/kaffee", "www2.example.org › kaffee"],
            "the host is lower-cased" => ["https://Example.ORG/Kaffee", "example.org › Kaffee"],
            "segments are separated" => ["https://klexikon.zum.de/wiki/Berlin", "klexikon.zum.de › wiki › Berlin"],
            "empty segments are skipped" => ["https://example.org//a///b/", "example.org › a › b"],
            "query and fragment are dropped" => ["https://example.org/suche?q=kaffee&p=2#treffer", "example.org › suche"],
            "a non-default port stays" => ["http://example.org:8080/a", "example.org:8080 › a"],
            "percent-encoding is decoded" => ["https://de.wikipedia.org/wiki/K%C3%B6ln", "de.wikipedia.org › wiki › Köln"],
            "invalid UTF-8 stays encoded" => ["https://example.org/%FF%FE", "example.org › %FF%FE"],
            "three segments are shown whole" => ["https://example.org/a/b/c", "example.org › a › b › c"],
            "more than three keep the first and the last" => ["https://example.org/a/b/c/d/e", "example.org › a › … › e"],
            "a long segment is shortened" => [
                "https://www.euronews.com/my-europe/2026/08/23/thousands-rally-in-stockholm-over-climate-crisis-weeks-ahead-of-general-election",
                "euronews.com › my-europe › … › thousands-rally-in-stockholm-ov…",
            ],
        ];
    }

    #[DataProvider("links")]
    public function testBuildsTheDisplayUrlFromTheLink(string $link, string $expected): void
    {
        $this->assertSame($expected, DisplayUrl::fromLink($link));
    }

    /**
     * Decoding must not let a URL reorder or hide what is drawn: a
     * right-to-left override in a path would turn "gpj.exe" into "exe.jpg"
     * on screen.
     */
    public function testControlAndBidiCharactersAreRemovedAfterDecoding(): void
    {
        $this->assertSame("example.org › bildgpj.exe", DisplayUrl::fromLink("https://example.org/bild%E2%80%AEgpj.exe"));
        $this->assertSame("example.org › ab", DisplayUrl::fromLink("https://example.org/a%00%0Ab"));
    }

    /**
     * A punycode host stays punycode, as browsers keep it for hosts that mix
     * scripts: decoding it would let "аpple.com" in Cyrillic pass as Apple.
     */
    public function testAnInternationalisedHostIsNotDecoded(): void
    {
        $this->assertSame("xn--pple-43d.com", DisplayUrl::fromLink("https://xn--pple-43d.com/"));
    }

    public function testALinkWithoutAHostHasNoDisplayUrl(): void
    {
        $this->assertNull(DisplayUrl::fromLink("http://host:abc"));
        $this->assertNull(DisplayUrl::fromLink("kein link"));
        $this->assertNull(DisplayUrl::fromLink(""));
    }

    /** Whatever the engine sent as its display URL, the result shows the same one. */
    public function testEveryEngineDisplaysTheSameLinkTheSameWay(): void
    {
        $link = "https://klexikon.zum.de/wiki/Berlin";
        $displays = array_map(
            fn(string $display) => (new Result(1, "Titel", $link, $display, "Beschreibung", "Engine", "https://example.org", 1))->anzeigeLink,
            [$link, "klexikon.zum.de › wiki › Berlin", "klexikon.zum.de > wiki > Berlin", ""]
        );

        $this->assertSame(array_fill(0, 4, "klexikon.zum.de › wiki › Berlin"), $displays);
    }

    /** A result whose link has no host keeps what the engine sent, as before. */
    public function testAResultWithoutAHostFallsBackToTheEnginesDisplay(): void
    {
        $result = new Result(1, "Titel", "http://host:abc", "https://www.example.org/x", "Beschreibung", "Engine", "https://example.org", 1);

        $this->assertSame("example.org/x", $result->anzeigeLink);
    }
}
