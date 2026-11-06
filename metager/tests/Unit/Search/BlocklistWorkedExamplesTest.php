<?php

namespace Tests\Unit\Search;

use App\Models\Result;
use App\Search\Blacklists;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesBlocklist;
use Tests\TestCase;

/**
 * The worked examples of the blocklist service's docs/matching.md
 * (open-source/blocklist), run through MetaGer's own path: Result cuts the
 * link, Blacklists looks it up. The service's tests run the same table against
 * its reference matcher, so a row that fails here is MetaGer and the service
 * disagreeing about what is hidden.
 *
 * Copied, not fetched: change it when the service's table changes, and say so
 * in the commit.
 */
class BlocklistWorkedExamplesTest extends TestCase
{
    use FakesBlocklist;

    /** @return array<string, array{0: string, 1: string, 2: ?string, 3: string, 4: string, 5: string}> */
    public static function examples(): array
    {
        return [
            "1 domain blocks its host" => ["domain", "example.org", null, "https://example.org/a", "anything", "blocked"],
            "2 domain is not a suffix" => ["domain", "example.org", null, "https://news.example.org/a", "anything", "shown"],
            "3 host case and www do not matter" => ["domain", "example.org", null, "https://WWW.Example.ORG/x", "anything", "blocked"],
            "4 url ignores query string and trailing slash" => ["url", "example.org/people/max", null, "https://www.example.org/people/max/?ref=1", "anything", "blocked"],
            "5 url does not cover pages below it" => ["url", "example.org/people/max", null, "https://example.org/people/max/photos", "anything", "shown"],
            "6 the path is case-sensitive" => ["url", "example.org/People/Max", null, "https://example.org/people/max", "anything", "shown"],
            "7 url_terms: every term, any order, among others" => ["url_terms", "example.org/people/max", "max mustermann", "https://example.org/people/max", "Mustermann Max Anwalt", "blocked"],
            "8 url_terms: a term missing" => ["url_terms", "example.org/people/max", "max mustermann", "https://example.org/people/max", "Max Meier", "shown"],
            "9 url_terms: the query is folded" => ["url_terms", "example.org/people/max", "max mustermann", "https://example.org/people/max", "MAX MUSTERMANN", "blocked"],
            "10 url_terms: umlaut and spelled-out form" => ["url_terms", "example.org/team", "mueller", "https://example.org/team", "Müller", "blocked"],
            "11 url_terms: another page, same query" => ["url_terms", "example.org/people/max", "max mustermann", "https://example.org/people/erika", "Max Mustermann", "shown"],
            "12 description: shown without description" => ["description", "example.org/a", null, "https://example.org/a", "anything", "description"],
            "13 no readable host is never blocked" => ["domain", "example.org", null, "not a url at all", "anything", "shown"],
        ];
    }

    #[DataProvider("examples")]
    public function testTheWorkedExample(string $kind, string $value, ?string $terms, string $url, string $query, string $verdict): void
    {
        $this->blocklistEntry($kind, $value, $terms);
        Blacklists::flush();

        $result = (new \ReflectionClass(Result::class))->newInstanceWithoutConstructor();
        $blacklists = new Blacklists();
        $host = $result->getStrippedHost($url);
        $link = $result->getStrippedLink($url);

        $actual = $blacklists->blocksResult($host, $link, $query)
            ? "blocked"
            : ($blacklists->blocksDescription($link) ? "description" : "shown");

        $this->assertSame($verdict, $actual, "host '$host', link '$link'");
    }
}
