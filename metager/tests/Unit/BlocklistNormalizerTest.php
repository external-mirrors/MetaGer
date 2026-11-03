<?php

namespace Tests\Unit;

use App\Models\Result;
use App\Search\BlocklistNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Blocklist values are compared with what Result derives from each link, so
 * the normaliser has to cut a URL exactly where Result::getUrlElements does —
 * and then make it comparable, which Result does not (host case, trailing
 * slash).
 */
class BlocklistNormalizerTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function urls(): array
    {
        return [
            "plain" => ["https://example.org/a/b"],
            "www" => ["https://www.example.org/a"],
            "no scheme" => ["example.org/a"],
            "port" => ["http://example.org:8080/a"],
            "query and fragment" => ["https://example.org/a?x=1#y"],
            "no path" => ["https://example.org"],
            "subdomain" => ["https://news.example.org/a"],
            "credentials" => ["https://u:p@example.org/a"],
        ];
    }

    #[DataProvider("urls")]
    public function testItCutsALinkWhereResultDoes(string $url): void
    {
        $result = (new \ReflectionClass(Result::class))->newInstanceWithoutConstructor();

        $this->assertSame(
            BlocklistNormalizer::strippedHost($result->getStrippedHost($url)),
            BlocklistNormalizer::host($url)
        );
        $this->assertSame(
            BlocklistNormalizer::strippedLink($result->getStrippedLink($url)),
            BlocklistNormalizer::link($url)
        );
    }

    public function testTheHostIsLowercasedAndThePathKeepsItsCase(): void
    {
        $this->assertSame("example.org/People/Max", BlocklistNormalizer::link("https://WWW.Example.ORG/People/Max/"));
        $this->assertSame("example.org/People/Max", BlocklistNormalizer::strippedLink("Example.ORG/People/Max/"));
    }

    public function testAHostOnlyLinkLosesItsSlash(): void
    {
        $this->assertSame("example.org", BlocklistNormalizer::link("https://example.org/"));
    }

    public function testSomethingWithoutAHostIsEmpty(): void
    {
        $this->assertSame("", BlocklistNormalizer::link(""));
        $this->assertSame("", BlocklistNormalizer::host("   "));
    }

    public function testTermsAreFoldedSplitDedupedAndSorted(): void
    {
        $this->assertSame(
            ["anwalt", "max", "muellerstrasse", "mustermann"],
            BlocklistNormalizer::terms("Max  MUSTERMANN, Anwalt – Müllerstraße max")
        );
    }

    public function testOtherDiacriticsAreDropped(): void
    {
        $this->assertSame(["cafe", "creme", "senor"], BlocklistNormalizer::terms("Café Crème Señor"));
    }

    public function testTermsAcceptAList(): void
    {
        $this->assertSame(["max", "mustermann"], BlocklistNormalizer::terms(["Mustermann", "max"]));
    }

    public function testPunctuationOnlyIsNoTerms(): void
    {
        $this->assertSame([], BlocklistNormalizer::terms(" ,.;- "));
    }
}
