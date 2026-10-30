<?php

namespace Tests\Unit\Search;

use App\Search\GoogleRedirectResolver;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The edges of GoogleRedirectResolver that a result page would not show:
 * what counts as a redirect, and which `Location` answers are refused.
 * The end-to-end behaviour is in Tests\Feature\Search\SerperGoogleRedirectTest.
 */
class GoogleRedirectResolverTest extends TestCase
{
    public static function redirects(): array
    {
        return [
            "goto with token" => ["https://www.google.com/goto?url=CAESggEB6zsw", true],
            "url with q" => ["https://www.google.de/url?q=https://example.org/", true],
            "country tld" => ["https://google.co.uk/url?url=https://example.org/", true],
            "a google page that is not a redirect" => ["https://support.google.com/goto", false],
            "a search, not a redirect" => ["https://www.google.com/search?q=goto", false],
            "lookalike host" => ["https://www.google.com.evil.example/goto?url=x", false],
            "ordinary result" => ["https://example.org/goto?url=x", false],
            "not a url" => ["goto", false],
        ];
    }

    #[DataProvider("redirects")]
    public function testRecognisesGoogleRedirects(string $url, bool $expected): void
    {
        $this->assertSame($expected, GoogleRedirectResolver::isRedirect($url));
    }

    public static function refusedLocations(): array
    {
        return [
            "captcha page" => ["https://www.google.com/sorry/index?continue=x"],
            "another redirect" => ["https://www.google.com/url?q=x"],
            "javascript scheme" => ["javascript:alert(1)"],
            "relative" => ["/somewhere"],
        ];
    }

    #[DataProvider("refusedLocations")]
    public function testRefusesLocationsThatAreNotAResult(string $location): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("", 302, ["Location" => $location])]);

        $url = "https://www.google.com/goto?url=CAES";
        $this->assertSame([$url => null], (new GoogleRedirectResolver())->resolve([$url]));
    }

    public function testAPlainTextTargetThatIsNotHttpIsAskedForInstead(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("", 302, ["Location" => "https://example.org/real"])]);

        $url = "https://www.google.com/url?q=javascript:alert(1)";
        $this->assertSame([$url => "https://example.org/real"], (new GoogleRedirectResolver())->resolve([$url]));
    }

    public function testATimeoutDropsTheLinkRatherThanFailingTheSearch(): void
    {
        Http::fake(fn() => throw new \Illuminate\Http\Client\ConnectionException("timed out"));

        $url = "https://www.google.com/goto?url=CAES";
        $this->assertSame([$url => null], (new GoogleRedirectResolver())->resolve([$url]));
    }

    public function testEachDistinctRedirectIsAskedForOnce(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("", 302, ["Location" => "https://example.org/"])]);

        $url = "https://www.google.com/goto?url=CAES";
        (new GoogleRedirectResolver())->resolve([$url, $url, "https://example.org/other"]);

        Http::assertSentCount(1);
    }
}
