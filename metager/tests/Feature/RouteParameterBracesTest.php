<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Laravel substitutes route parameters into the path and only then looks for
 * `{…}` left in it to report a missing parameter, so a value that itself
 * contains braces is mistaken for an unfilled placeholder and route() throws
 * UrlGenerationException. A template-injection scanner sent exactly that: a
 * `key` of `{26322*26322}` or `${defined(…)}`, which every page carries into
 * `suggest/{key?}` — the startpage's search bar, /plugin and the OpenSearch
 * description — and a voucher code of `${10000360+10000360}` in `c/{code}`
 * (GlitchTip METAGER-1AB, -1AC, -1AT, -1AU, -1AV, -1B0, -1B7; 11,593 events
 * for -1AB alone).
 *
 * App\Routing\CookieCarryingUrlGenerator percent-encodes the braces of a
 * value bound for the path before Laravel sees them.
 */
class RouteParameterBracesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        // What the keymanager says about a code that does not exist.
        Http::fake(["*" => Http::response(["error" => "invalid_code"], 404)]);
    }

    /** @return array<string, array{string}> */
    public static function reportedRequests(): array
    {
        return [
            "METAGER-1AB startpage" => ["/?key=%7B26322*26322%7D"],
            "METAGER-1AC startpage" => ['/?key=$%7Bdefined(MG)%7D'],
            "METAGER-1AT plugin page" => ["/plugin?key=%7B19456*19456%7D"],
            "METAGER-1AU plugin page" => ['/plugin?key=$%7Bdefined(MG)%7D'],
            "METAGER-1AV opensearch description" => ["/plugins/opensearch.xml?key=%7B98991*97996%7D"],
            "METAGER-1B0 opensearch description" => ['/plugins/opensearch.xml?key=$%7Bdefined(MG)%7D'],
            "METAGER-1B7 voucher teaser" => ['/c/$%7B10000360+10000360%7D'],
        ];
    }

    #[Test]
    #[DataProvider("reportedRequests")]
    public function a_parameter_with_braces_does_not_answer_500(string $uri): void
    {
        $status = $this->get($uri)->getStatusCode();

        $this->assertLessThan(500, $status, "$uri answered $status");
    }

    #[Test]
    public function braces_in_a_path_parameter_are_percent_encoded(): void
    {
        $this->assertStringEndsWith("/suggest/%7B26322*26322%7D", route("suggest", ["key" => "{26322*26322}"]));
        $this->assertStringEndsWith('/c/%24%7B1+1%7D', route("voucher.code", ['${1+1}']));
    }

    #[Test]
    public function braces_in_a_query_parameter_are_encoded_once(): void
    {
        // These never threw — the query string is encoded before the check —
        // and must not come out double-encoded now.
        $this->assertStringContainsString("eingabe=%7Bcats%7D", route("resultpage", ["eingabe" => "{cats}"]));
    }

    #[Test]
    public function an_ordinary_path_parameter_is_unchanged(): void
    {
        $this->assertStringEndsWith("/suggest/abc-123", route("suggest", ["key" => "abc-123"]));
    }
}
