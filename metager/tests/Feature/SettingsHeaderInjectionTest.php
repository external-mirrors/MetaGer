<?php

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The settings forms put three of their fields into response headers: `url`
 * becomes the redirect's Location, `focus` its fragment and a cookie name,
 * and `key`/`cookieKey` the name of the cookie to forget. A scanner sent
 * them with CR, LF and NUL in them (GlitchTip METAGER-1AF, -1AH, -1AI, -1AL,
 * -1B1, -1B2, -1B8, -1B9). PHP's header() refuses such a line, so nothing
 * was injected, but the refusal is an ErrorException at send time.
 *
 * A test client never calls header(), so these assert on the headers the
 * response would have sent.
 */
class SettingsHeaderInjectionTest extends TestCase
{
    /** @return array<string, array{string, array<string, string>}> */
    public static function reportedRequests(): array
    {
        $cases = [];
        foreach (["LF" => "\n", "CRLF" => "\r\n", "NUL" => "\0"] as $label => $c) {
            $cases["$label in url, enable filter"] = ["/meta/settings/ef", ["focus" => "web", "s" => "s", "url" => "https://metager.org/{$c}X-Injected: 1"]];
            $cases["$label in focus, enable filter"] = ["/meta/settings/ef", ["focus" => "web{$c}X", "s" => "s"]];
            $cases["$label in url, new blacklist"] = ["/meta/settings/nb", ["focus" => "web", "blacklist" => "example.com", "url" => "https://metager.org/{$c}X-Injected: 1"]];
            $cases["$label in focus, new blacklist"] = ["/meta/settings/nb", ["focus" => "web{$c}X", "blacklist" => "example.com"]];
            $cases["$label in url, remove all"] = ["/meta/settings/all-settings/removeAll", ["url" => "https://metager.org/{$c}X-Injected: 1"]];
            $cases["$label in url, remove one"] = ["/meta/settings/all-settings/removeOne", ["key" => "dark_mode", "url" => "https://metager.org/{$c}X-Injected: 1"]];
            $cases["$label in key, remove one"] = ["/meta/settings/all-settings/removeOne", ["key" => "dark_mode{$c}X"]];
            $cases["$label in focus, delete settings"] = ["/meta/settings/ds", ["focus" => "web{$c}X"]];
            $cases["$label in cookieKey, delete blacklist"] = ["/meta/settings/db", ["focus" => "web", "cookieKey" => "web_blpage{$c}X"]];
        }
        return $cases;
    }

    #[Test]
    #[DataProvider("reportedRequests")]
    public function no_control_character_reaches_a_header(string $uri, array $data): void
    {
        $response = $this->post($uri, $data);

        $this->assertLessThan(500, $response->getStatusCode());
        foreach ($this->headerLines($response) as $line) {
            $this->assertDoesNotMatchRegularExpression('/[\x00-\x1F\x7F]/', $line, "header line: " . json_encode($line));
        }
    }

    #[Test]
    public function an_ordinary_back_url_is_still_followed(): void
    {
        $this->post("/meta/settings/all-settings/removeAll", ["url" => "https://metager.org/meta/meta.ger3?eingabe=x"])
            ->assertRedirect("https://metager.org/meta/meta.ger3?eingabe=x");
    }

    #[Test]
    public function an_ordinary_key_is_still_forgotten(): void
    {
        $response = $this->call("POST", "/meta/settings/all-settings/removeOne", ["key" => "dark_mode"], ["dark_mode" => "dark"]);

        $forgotten = array_filter($response->headers->getCookies(), fn ($c) => $c->getName() === "dark_mode" && $c->isCleared());
        $this->assertNotEmpty($forgotten);
    }

    /** @return list<string> every header line, cookies included */
    private function headerLines(TestResponse $response): array
    {
        $lines = [];
        foreach ($response->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            foreach ($values as $value) {
                $lines[] = "$name: $value";
            }
        }
        foreach ($response->headers->getCookies() as $cookie) {
            $lines[] = "Set-Cookie: " . $cookie;
        }
        return $lines;
    }
}
