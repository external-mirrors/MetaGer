<?php

namespace Tests\Unit;

use App\Support\AppHosts;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * App\Support\AppHosts — the one list of every host this deployment answers.
 *
 * It backs the trusted-host check ({@see \App\Http\Middleware\TrustHosts}) and
 * the browser origin the checkout forwards to the keymanager as
 * `return_origin`. A host that belongs here but is missing is trusted by the
 * ingress and rejected by the app; a host that does *not* belong but slips in
 * is an open redirect that carries a payment provider's return trip.
 */
class AppHostsTest extends TestCase
{
    /** @return array<string, array{0: string, 1: bool}> */
    public static function hosts(): array
    {
        return [
            "metager.de" => ["metager.de", true],
            "metager.org" => ["metager.org", true],
            "metager3.de" => ["metager3.de", true],
            "v3 onion" => ["metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion", true],
            "v2 onion" => ["b7cxf4dkdsko6ah2.onion", true],
            "a review preview" => ["feat-thing.review.metager.de", true],
            "localhost" => ["localhost", true],
            "the compose service name" => ["nginx", true],
            "uppercased" => ["METAGER.DE", true],

            "a regional that 301s at the ingress" => ["metager.es", false],
            "www. of a canonical host" => ["www.metager.de", false],
            "a lookalike suffix" => ["metager.de.evil.example", false],
            "review suffix as a substring only" => ["review.metager.de.evil.example", false],
            "the bare review base" => ["review.metager.de", false],
            "an unrelated host" => ["evil.example", false],
            "empty" => ["", false],
        ];
    }

    #[DataProvider("hosts")]
    public function testIsOurs(string $host, bool $expected): void
    {
        $this->assertSame($expected, AppHosts::isOurs($host));
    }

    public function testIsOursRejectsNull(): void
    {
        $this->assertFalse(AppHosts::isOurs(null));
    }

    public function testIsOnionMatchesBothServicesAndNothingElse(): void
    {
        $this->assertTrue(AppHosts::isOnion("metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion"));
        $this->assertTrue(AppHosts::isOnion("b7cxf4dkdsko6ah2.onion"));
        $this->assertFalse(AppHosts::isOnion("metager.de"));
        $this->assertFalse(AppHosts::isOnion("something.onion"));
        $this->assertFalse(AppHosts::isOnion(null));
    }

    public function testCurrentOriginReturnsTheSchemeAndHostForOneOfOurs(): void
    {
        $request = Request::create("https://metager.org/de-DE/konto/aufladen/1000/vrpayment", "POST");

        $this->assertSame("https://metager.org", AppHosts::currentOrigin($request));
    }

    public function testCurrentOriginIsNullForAHostThatIsNotOurs(): void
    {
        // Cannot normally happen behind TrustHosts, but currentOrigin is the
        // last guard before the value becomes a payment provider's redirect
        // target, so it refuses rather than trusts.
        $request = Request::create("https://evil.example/de-DE/konto/aufladen/1000/vrpayment", "POST");

        $this->assertNull(AppHosts::currentOrigin($request));
    }

    /**
     * Every pattern is an anchored regex Symfony can hand to
     * `Request::setTrustedHosts()`. A stray unescaped metacharacter there
     * widens what the app answers to — the `.` in a hostname must match a
     * literal dot, not any character.
     */
    public function testTrustedPatternsAreAnchoredAndEscaped(): void
    {
        foreach (AppHosts::trustedPatterns() as $pattern) {
            $this->assertStringStartsWith("^", $pattern);
            $this->assertStringEndsWith("$", $pattern);
            $this->assertNotFalse(@preg_match("#$pattern#i", ""), "pattern is not valid: $pattern");
            // A literal dot, not the regex "any char": "metagerXde" must not match.
            $this->assertDoesNotMatchRegularExpression("#$pattern#i", "metagerXde");
        }
    }
}
