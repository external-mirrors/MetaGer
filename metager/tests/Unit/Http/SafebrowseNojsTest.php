<?php

namespace Tests\Unit\Http;

use App\Http\SafebrowseNojs;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * How a results page chooses its SafeBrowse sid (App\Http\SafebrowseNojs::sid): the one it was
 * reached with from one of our own pages, otherwise a new random one.
 *
 * Plain PHPUnit: sid() reads nothing but the request it is given.
 */
class SafebrowseNojsTest extends TestCase
{
    private const SID = "aB3dE5gH7jK9mN1pQ3sT5vW7";

    private static function request(string $query = "", ?string $referer = null): Request
    {
        $server = ["HTTP_HOST" => "metager.de", "HTTPS" => "on"];
        if ($referer !== null) {
            $server["HTTP_REFERER"] = $referer;
        }
        return Request::create("https://metager.de/meta/meta.ger3?eingabe=kaffee" . $query, "GET", [], [], [], $server);
    }

    public function testANewPageGetsANewRandomSid(): void
    {
        $first = SafebrowseNojs::sid(self::request());
        $second = SafebrowseNojs::sid(self::request());

        $this->assertMatchesRegularExpression(SafebrowseNojs::SID_PATTERN, $first);
        $this->assertNotSame($first, $second);
    }

    public function testOnePageHasOneSid(): void
    {
        $request = self::request();

        $this->assertSame(SafebrowseNojs::sid($request), SafebrowseNojs::sid($request));
    }

    public function testASidFromOurOwnPageIsKept(): void
    {
        $this->assertSame(self::SID, SafebrowseNojs::sid(self::request("&sid=" . self::SID, "https://metager.de/meta/meta.ger3?eingabe=tee")));
    }

    /** @return array<string, array{0: ?string}> */
    public static function foreignReferers(): array
    {
        return [
            "no referer (pasted, bookmarked)" => [null],
            "another site" => ["https://example.com/?sid=" . self::SID],
            "a look-alike host" => ["https://metager.de.example.com/meta/meta.ger3"],
            "another port" => ["https://metager.de:8443/meta/meta.ger3"],
            "not a URL" => ["metager.de"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider("foreignReferers")]
    public function testASidFromAnywhereElseIsReplaced(?string $referer): void
    {
        $this->assertNotSame(self::SID, SafebrowseNojs::sid(self::request("&sid=" . self::SID, $referer)));
    }

    /** @return array<string, array{0: string}> */
    public static function malformedSids(): array
    {
        return [
            "too short" => ["abc"],
            "too long" => [str_repeat("a", 65)],
            "markup" => ["aaaaaaaaaaaaaaaa<script>"],
            "an array" => ["x"],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider("malformedSids")]
    public function testAMalformedSidIsReplaced(string $sid): void
    {
        $query = $sid === "x" ? "&sid[]=" . self::SID : "&sid=" . rawurlencode($sid);
        $kept = SafebrowseNojs::sid(self::request($query, "https://metager.de/"));

        $this->assertNotSame($sid, $kept);
        $this->assertMatchesRegularExpression(SafebrowseNojs::SID_PATTERN, $kept);
    }
}
