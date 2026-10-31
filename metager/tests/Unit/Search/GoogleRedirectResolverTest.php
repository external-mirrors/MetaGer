<?php

namespace Tests\Unit\Search;

use App\Search\GoogleRedirectResolver;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The edges of GoogleRedirectResolver that a result page would not show:
 * what counts as a redirect, and which plain-text targets are refused.
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

    public static function refusedPlainTextTargets(): array
    {
        return [
            "captcha page" => ["https://www.google.com/url?q=https://www.google.com/sorry/index?continue=x"],
            "another redirect" => ["https://www.google.com/url?q=https://www.google.com/goto?url=CAES"],
            "javascript scheme" => ["https://www.google.com/url?q=javascript:alert(1)"],
            "relative" => ["https://www.google.com/url?q=/somewhere"],
        ];
    }

    #[DataProvider("refusedPlainTextTargets")]
    public function testRefusesPlainTextTargetsThatAreNotAResult(string $url): void
    {
        $this->assertSame([$url => null], (new GoogleRedirectResolver())->resolve([$url]));
    }

    public function testAGotoLinkIsDroppedWithoutAskingGoogle(): void
    {
        Http::preventStrayRequests();

        $url = "https://www.google.com/goto?url=CAES";
        $this->assertSame([$url => null], (new GoogleRedirectResolver())->resolve([$url]));

        Http::assertNothingSent();
    }

    public function testEntriesThatAreNotRedirectsComeBackUnchanged(): void
    {
        $url = "https://example.org/other";
        $this->assertSame([$url => $url], (new GoogleRedirectResolver())->resolve([$url, $url]));
    }
}
