<?php

namespace Tests\Feature;

use App\Authentication\KeyUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PHP turns `name[]=x` in a query, a form body or a Cookie header into an
 * array, and nothing MetaGer generates ever sends one: every field it reads is
 * a string. One scanner (GlitchTip METAGER-1AE … METAGER-1BA, sixteen issues
 * in two days, all from one address) found that nearly every controller
 * assumes so, and answered 500 with TypeErrors and "Array to string
 * conversion" from `trim()`, `substr()`, `parse_url()`, `htmlspecialchars()`,
 * a cookie name and an array offset.
 *
 * Fixed once, in App\Http\Middleware\DropArrayInput, rather than at every
 * call site the way METAGER-18L and METAGER-19V were: an array-valued field
 * is treated as absent, which is what those two per-site fixes chose too.
 * Every case below is one of the reported requests.
 */
class ArrayInputTest extends TestCase
{
    private const KEY = "aaaaaaaa-bbbb-cccc-dddd-eeeeee123456";

    /** @return array<string, array{0: string, 1: string, 2: array<string, mixed>, 3?: array<string, string>}> */
    public static function reportedRequests(): array
    {
        return [
            "METAGER-1AE settings page, nested query" => ["GET", "/meta/settings?dark_mode[a][b]=dark", []],
            "METAGER-1AG enable filter, array value" => ["POST", "/meta/settings/ef", ["focus" => "web", "s" => ["s"]]],
            "METAGER-1AJ new blacklist, array list" => ["POST", "/meta/settings/nb", ["focus" => "web", "blacklist" => ["example.com"]]],
            "METAGER-1AK new blacklist, array focus" => ["POST", "/meta/settings/nb", ["focus" => ["web"], "blacklist" => "example.com"]],
            "METAGER-1AM language selector, array previous_url" => ["GET", "/lang?previous_url[]=https://metager.org/", []],
            "METAGER-1AN donation, array amount" => ["GET", "/spende?amount[]=5", []],
            "METAGER-1AP quote search, array q" => ["GET", "/zitat-suche?q[]=liebe", []],
            "METAGER-1AQ startpage, array eingabe" => ["GET", "/?eingabe[]=cats", []],
            "METAGER-1AR sitesearch, array site" => ["GET", "/sitesearch?site[]=metager.org", []],
            "METAGER-1AS delete settings, array focus" => ["POST", "/meta/settings/ds", ["focus" => ["web"]], ["web_engine_brave" => "off"]],
            "METAGER-1B5 disable engine, array suma" => ["POST", "/meta/settings/de", ["focus" => "web", "suma" => ["brave"]]],
            "METAGER-1B6 enable engine, array suma" => ["POST", "/meta/settings/ee", ["focus" => "web", "suma" => ["brave"]]],
            "METAGER-1BA remove one setting, array key" => ["POST", "/meta/settings/all-settings/removeOne", ["key" => ["dark_mode"]]],
        ];
    }

    #[Test]
    #[DataProvider("reportedRequests")]
    public function an_array_field_does_not_answer_500(string $method, string $uri, array $data, array $cookies = []): void
    {
        $status = $this->call($method, $uri, $data, $cookies)->getStatusCode();

        $this->assertLessThan(500, $status, "$method $uri answered $status");
    }

    /**
     * METAGER-1AO: only the signed-in startpage has the search bar, which
     * echoes `focus` into a hidden field.
     */
    #[Test]
    public function the_signed_in_startpage_takes_an_array_focus(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response("")]);
        Cache::put("keyserver:key:" . self::KEY, ["key" => self::KEY, "charge" => 142.0], now()->addMinutes(10));
        $this->be(new KeyUser(self::KEY), "key");

        $this->get("/?focus[]=web")
            ->assertOk()
            ->assertSee('searchbar startpage-searchbar', false);
    }

    #[Test]
    public function an_array_cookie_does_not_answer_500(): void
    {
        // The settings page reads every cookie as a possible setting, and a
        // `Cookie: dark_mode[a][b]=dark` header parses into a nested array.
        $status = $this->call("GET", "/meta/settings", [], ["dark_mode" => ["a" => ["b" => "dark"]]])->getStatusCode();

        $this->assertLessThan(500, $status);
    }

    #[Test]
    public function an_array_field_counts_as_absent(): void
    {
        // Same as no `eingabe` at all: an empty query, not a search.
        $this->get("/?eingabe[]=cats")->assertOk();

        $this->assertSame("", app(\App\SearchSettings::class)->q);
    }

    #[Test]
    public function string_fields_beside_it_are_untouched(): void
    {
        $this->get("/meta/settings?url[]=x&url2=y&focus=web")->assertOk();
        $this->get("/zitat-suche?q=liebe")->assertOk();
    }

    #[Test]
    public function a_json_body_keeps_its_arrays(): void
    {
        // JSON APIs take structured bodies on purpose; only the PHP
        // bracket syntax of query strings, forms and cookies is dropped.
        $request = \Illuminate\Http\Request::create("/", "POST", [], [], [], ["CONTENT_TYPE" => "application/json"], json_encode(["list" => ["a", "b"]]));
        $seen = null;
        (new \App\Http\Middleware\DropArrayInput())->handle($request, function ($r) use (&$seen) {
            $seen = $r->input("list");
            return response("");
        });

        $this->assertSame(["a", "b"], $seen);
    }
}
